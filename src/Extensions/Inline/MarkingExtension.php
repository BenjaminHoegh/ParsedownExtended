<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Inline;

trait MarkingExtension
{
    /**
     * Processes inline marking elements.
     *
     * Handles inline marking by using double equal signs (`==text==`). This will convert the marked text
     * into an HTML `<mark>` tag if the feature is enabled in the configuration.
     *
     * @since 1.2.0
     *
     * @param array $Excerpt The portion of text being parsed to identify marking.
     * @return array|null The parsed marking element or null if marking is disabled or not applicable.
     */
    protected function inlineMarking(array $Excerpt): ?array
    {
        // Check if marking is enabled in the configuration settings
        if (!$this->configEnabled('emphasis.mark') || !$this->configEnabled('emphasis')) {
            return null; // Return null if marking or emphasis is disabled
        }

        // Early return if the excerpt does not start with two '=' characters
        if (!isset($Excerpt['text'][1]) || $Excerpt['text'][1] !== '=') {
            return null;
        }

        // Match the double equal signs for marking (`==text==`) using regex
        if (preg_match('/^==((?:\\\\\=|[^=]|=[^=]*=)+?)==(?!=)/s', $Excerpt['text'], $matches)) {
            // Return the parsed marking element
            return [
                'extent' => strlen($matches[0]), // The length of the matched marking text
                'element' => [
                    'name' => 'mark', // The HTML tag used for marking
                    'text' => $matches[1], // The content inside the marking
                ],
            ];
        }

        return null; // If no match is found, return null
    }
}
