<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Block;

trait AlertExtension
{
    /**
     * Processes alert blocks within the parsed Markdown text.
     *
     * This function identifies and processes blocks starting with a specific alert syntax, such as `> [!NOTE]`.
     * Alerts are styled based on their type (e.g., Note, Warning, etc.) and formatted as HTML div elements with appropriate classes.
     *
     * @since 1.3.0
     *
     * @param array $Line The line being processed for an alert block.
     * @return array|null The parsed alert block if matched, otherwise null.
     */
    protected function blockAlert($Line): ?array
    {
        // Check if alerts are enabled in the configuration settings
        if (!$this->configEnabled('alerts') || strncmp($Line['text'], '> [!', 4) !== 0) {
            return null; // Return null if alert blocks are disabled
        }

        // Build escaped alert type pattern from config values
        $alertTypesPattern = $this->buildAlertTypesPattern();
        if ($alertTypesPattern === null) {
            return null;
        }

        // Create the full regex pattern for matching alert block syntax
        $pattern = '/^> \[!(' . $alertTypesPattern . ')\]/';

        // Check if the line matches the alert pattern
        if (preg_match($pattern, $Line['text'], $matches)) {
            $type = strtolower($matches[1]); // Extract the alert type and convert to lowercase
            $title = ucfirst($type); // Capitalize the first letter for the alert title

            // Get class name for alerts from the configuration
            $class = $this->configValue('alerts.class');

            // Build the alert block with appropriate HTML attributes and content
            return [
                'element' => [
                    'name' => 'div',
                    'attributes' => [
                        'class' => "{$class} {$class}-{$type}", // Add alert type as a class (e.g., 'alert alert-note')
                    ],
                    'elements' => [
                        [
                            'name' => 'p',
                            'attributes' => [
                                'class' => "{$class}-title", // Assign title-specific class for the alert
                            ],
                            'text' => $title, // Set the alert title (e.g., "Note")
                        ],
                    ],
                ],
            ]; // Return the parsed alert block
        }

        return null; // Return null if the line does not match the alert pattern
    }

    /**
     * Continues processing alert blocks by adding subsequent lines to the current alert block.
     *
     * @since 1.3.0
     *
     * @param array $Line The current line being processed.
     * @param array $Block The current block being extended.
     * @return array|null The updated alert block or null if the continuation is not applicable.
     */
    protected function blockAlertContinue($Line, array $Block)
    {
        // Build escaped alert type pattern from config values
        $alertTypesPattern = $this->buildAlertTypesPattern();
        if ($alertTypesPattern === null) {
            return null;
        }

        // Create the full regex pattern for identifying new alert blocks
        $pattern = '/^> \[!(' . $alertTypesPattern . ')\]/';

        // If the line matches a new alert block, terminate the current one
        if (preg_match($pattern, $Line['text'])) {
            return null; // Return null to terminate the current alert block
        }

        // Treat nested quote lines inside an alert as a regular blockquote
        if (preg_match('/^> > ?(.*)/', $Line['text'], $nestedMatches)) {
            if (isset($Block['interrupted'])) {
                unset($Block['interrupted']);
            }

            $nestedText = $nestedMatches[1];

            $lastElementIndex = count($Block['element']['elements']) - 1;
            $hasPreviousBlockquote = $lastElementIndex >= 0
                && isset($Block['element']['elements'][$lastElementIndex]['name'])
                && $Block['element']['elements'][$lastElementIndex]['name'] === 'blockquote';

            if ($hasPreviousBlockquote) {
                $Block['element']['elements'][$lastElementIndex]['handler']['argument'][] = $nestedText;

                return $Block;
            }

            $Block['element']['elements'][] = [
                'name' => 'blockquote',
                'handler' => [
                    'function' => 'linesElements',
                    'argument' => [$nestedText],
                    'destination' => 'elements',
                ],
            ];

            return $Block;
        }

        // Check if the line continues the current alert block with '>' followed by content
        if (isset($Line['text'][0]) && $Line['text'][0] === '>' && preg_match('/^> ?(.*)/', $Line['text'], $matches)) {
            // Reset interruption state before appending new content
            if (isset($Block['interrupted'])) {
                unset($Block['interrupted']); // Reset the interrupted status
            }

            // Treat an empty quote marker (">" or "> ") as a paragraph separator
            if (trim($matches[1]) === '') {
                return $Block;
            }

            // Append the new line content to the current block
            $Block['element']['elements'][] = [
                'name' => 'p',
                'handler' => [
                    'function' => 'lineElements',
                    'argument' => $matches[1],
                    'destination' => 'elements',
                ],
            ];

            return $Block; // Return the updated block
        }

        // If the line does not start with '>' and the block is not interrupted, append it
        if (!isset($Block['interrupted'])) {
            $Block['element']['elements'][] = [
                'name' => 'p',
                'handler' => [
                    'function' => 'lineElements',
                    'argument' => $Line['text'],
                    'destination' => 'elements',
                ],
            ];

            return $Block; // Return the updated block
        }

        return null; // Return null if the continuation conditions are not met
    }

    /**
     * Completes the alert block.
     *
     * @since 1.3.0
     *
     * @param array $Block The current block being finalized.
     * @return array The completed alert block.
     */
    protected function blockAlertComplete($Block)
    {
        return $Block; // Finalize and return the alert block
    }

    /**
     * Builds a safe alternation pattern for configured alert types.
     *
     * @return string|null Regex-safe alternation pattern or null if no valid types exist.
     */
    private function buildAlertTypesPattern(): ?string
    {
        static $cacheKey = '';
        static $cachedPattern = null;

        $alertTypes = $this->configValue('alerts.types');
        if (!is_array($alertTypes) || $alertTypes === []) {
            return null;
        }

        $newCacheKey = implode("\0", $alertTypes);
        if ($cacheKey === $newCacheKey) {
            return $cachedPattern;
        }

        $escapedTypes = [];
        foreach ($alertTypes as $alertType) {
            if (!is_string($alertType) || $alertType === '') {
                continue;
            }

            $escapedTypes[] = preg_quote(strtoupper($alertType), '/');
        }

        if ($escapedTypes === []) {
            $cacheKey = $newCacheKey;
            $cachedPattern = null;
            return null;
        }

        $cacheKey = $newCacheKey;
        $cachedPattern = implode('|', $escapedTypes);

        return $cachedPattern;
    }
}
