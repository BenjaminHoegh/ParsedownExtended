<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Block;

trait HeadingAnchorExtension
{
    /** @var (\Closure(string, \BenjaminHoegh\ParsedownExtended\Configuration\Configuration): ?string)|null */
    private ?\Closure $createAnchorIDCallback = null;

    /**
     * Sets a callback function for creating anchor IDs for headers.
     *
     * This allows the user to provide custom logic for generating anchor IDs for
     * the headers found in the Markdown content.
     *
     * @since 1.2.0
     *
     * @param callable $callback The callback function to generate anchor IDs.
     * @return void
     */
    public function setCreateAnchorIDCallback(callable $callback): void
    {
        $this->createAnchorIDCallback = \Closure::fromCallable($callback);
    }

    /**
     * Creates an anchor ID for a given header text.
     *
     * This function generates a unique anchor ID for a header, allowing for custom
     * callbacks to be used for the generation logic. If no callback is provided,
     * default logic is used, including transliteration, normalization, and sanitization.
     *
     * @since 1.0.0
     *
     * @param string $text The header text for which an anchor ID is generated.
     * @return string|null The generated anchor ID or null if auto anchors are disabled.
     */
    protected function createAnchorID(string $text): ?string
    {
        // Check if automatic anchor generation is enabled in the settings
        if (!$this->configEnabled('headings.auto_anchors')) {
            return null; // Return null if auto anchors are disabled
        }

        // If a user-defined callback is provided, use it to generate the anchor ID
        if ($this->createAnchorIDCallback !== null) {
            $config = $this->config();
            return ($this->createAnchorIDCallback)($text, $config);
        }

        // Convert text to lowercase if configured to do so
        if ($this->configEnabled('headings.auto_anchors.lowercase')) {
            if (extension_loaded('mbstring')) {
                $text = mb_strtolower($text);
            } else {
                $text = strtolower($text);
            }
        }

        // Apply replacements to the text based on the configuration settings
        $replacements = $this->configValue('headings.auto_anchors.replacements');
        if (!empty($replacements)) {
            $text = preg_replace(array_keys($replacements), $replacements, $text);
        }

        // Normalize the text (ensure proper encoding)
        $text = $this->normalizeString($text);

        // Transliterate text if configured to do so
        if ($this->configEnabled('headings.auto_anchors.transliterate')) {
            $text = $this->transliterate($text);
        }

        // Sanitize the text to make it a valid anchor ID
        $text = $this->sanitizeAnchor($text);

        // Fall back to "heading" if sanitization produced an empty string
        if ($text === '') {
            $text = 'heading';
        }

        // Ensure the generated anchor ID is unique
        return $this->uniquifyAnchorID($text);
    }

    /**
     * Sanitizes a string to make it suitable for use as an HTML anchor ID.
     *
     * This function replaces non-alphanumeric characters in the string with a delimiter
     * (e.g., hyphen), ensuring the result is suitable as an HTML ID. Consecutive delimiters
     * are collapsed into a single delimiter, and leading/trailing delimiters are trimmed.
     *
     * @since 1.2.0
     *
     * @param string $text The input text to be sanitized.
     * @return string The sanitized string suitable for use as an anchor ID.
     */
    protected function sanitizeAnchor(string $text): string
    {
        // Get the delimiter used to replace non-alphanumeric characters (e.g., '-')
        $delimiter = $this->configValue('headings.auto_anchors.delimiter');

        // Replace any character that is not a letter or number with the delimiter
        $text = preg_replace_callback(
            '/[^\p{L}\p{Nd}]+/u',
            static fn(): string => $delimiter,
            $text
        );

        $cacheKey = 'headings.auto_anchors.collapse_pattern';
        if (!$this->hasRuntimeCacheValue($cacheKey)) {
            $this->storeRuntimeCacheValue($cacheKey, '/(' . preg_quote($delimiter, '/') . '){2,}/');
        }

        $collapseDelimiterPattern = $this->runtimeCacheValue($cacheKey);
        if (!is_string($collapseDelimiterPattern)) {
            $collapseDelimiterPattern = '/(' . preg_quote($delimiter, '/') . '){2,}/';
            $this->storeRuntimeCacheValue($cacheKey, $collapseDelimiterPattern);
        }

        // Collapse consecutive delimiters into a single delimiter
        $text = preg_replace($collapseDelimiterPattern, '$1', $text);

        // Trim any leading or trailing delimiters
        return trim($text, $delimiter);
    }

    /**
     * Ensures that the generated anchor ID is unique.
     *
     * This function keeps track of generated anchor IDs to avoid duplicates. If an anchor ID has already been used,
     * it appends a unique suffix to it. Blacklisted anchor IDs are also skipped to ensure the final anchor is valid.
     *
     * @since 1.2.0
     *
     * @param string $text The base anchor ID text.
     * @return string A unique anchor ID.
     */
    protected function uniquifyAnchorID(string $text): string
    {
        $count = $this->anchorCounts[$text] ?? 0;

        // Adjust the anchor ID to ensure it is unique and not in the blacklist
        while (true) {
            // Generate the potential anchor ID with the count as suffix (if needed)
            $potentialId = $text . ($count > 0 ? '-' . $count : '');

            if (
                !isset($this->usedAnchorIds[$potentialId])
                && !$this->configValueSetContains('headings.auto_anchors.blacklist', $potentialId)
            ) {
                $this->anchorCounts[$text] = $count + 1;
                $this->usedAnchorIds[$potentialId] = true;

                return $potentialId;
            }

            // Increment the counter to generate the next potential ID
            ++$count;
        }
    }
}
