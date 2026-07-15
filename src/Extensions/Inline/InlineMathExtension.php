<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Inline;

trait InlineMathExtension
{
    /**
     * Processes inline math notation elements.
     *
     * Handles inline math notation using specific delimiters (e.g., `$...$`, `\\(...\\)`). If enabled in the configuration,
     * this function matches math notation within the specified delimiters and processes it accordingly.
     *
     * @since 1.1.2
     *
     * @param array $Excerpt The portion of text being parsed to identify math notation.
     * @return array|null The parsed math notation element or null if math parsing is disabled or not applicable.
     */
    protected function inlineMathNotation($Excerpt)
    {
        // Check if parsing of math notation is enabled in the configuration settings
        if (!$this->configEnabled('math') || !$this->configEnabled('math.inline')) {
            return null; // Return null if math or inline math is disabled
        }

        // Check if the excerpt has enough characters to proceed
        if (!isset($Excerpt['text'][1])) {
            return null; // Return null if there is insufficient text for math notation
        }

        // Check if there is whitespace before the excerpt (ensures math is not in the middle of a word)
        if ($Excerpt['before'] !== '' && preg_match('/\s/', $Excerpt['before']) === 0) {
            return null; // Return null if the math notation is not preceded by whitespace
        }

        // Iterate through the inline math delimiters (e.g., `$...$`, `\\(...\\)`).
        $patterns = $this->getInlineMathPatterns($this->configValue('math.inline.delimiters'));
        foreach ($patterns as $regex) {
            if (preg_match($regex, $Excerpt['text'], $matches)) {
                // Return the parsed math element
                return [
                    'extent' => strlen($matches[0]), // The length of the matched math notation
                    'element' => [
                        'text' => $matches[0], // The matched math content
                    ],
                ];
            }
        }

        return null; // If no match is found, return null
    }

    /**
     * Builds and caches regex patterns for inline math delimiters.
     *
     * @param array $delimiters Inline math delimiters.
     * @return array<int, string> Regex pattern list.
     */
    private function getInlineMathPatterns(array $delimiters): array
    {
        $cacheKey = 'math.inline.patterns';
        if ($this->hasRuntimeCacheValue($cacheKey)) {
            $patterns = $this->runtimeCacheValue($cacheKey);

            return is_array($patterns) ? $patterns : [];
        }

        $patterns = [];
        foreach ($delimiters as $delimiter) {
            if (
                !is_array($delimiter) ||
                !isset($delimiter['left'], $delimiter['right']) ||
                !is_string($delimiter['left']) ||
                !is_string($delimiter['right']) ||
                $delimiter['left'] === '' ||
                $delimiter['right'] === ''
            ) {
                continue;
            }

            $leftMarker = preg_quote($delimiter['left'], '/');
            $rightMarker = preg_quote($delimiter['right'], '/');

            if ($delimiter['left'][0] === '\\' || strlen($delimiter['left']) > 1) {
                $patterns[] = '/^(?<!\S)' . $leftMarker . '(?![\r\n])((?:\\\\' . $rightMarker . '|\\\\' . $leftMarker . '|[^\r\n])+?)' . $rightMarker . '(?!\w)/s';
                continue;
            }

            $patterns[] = '/^(?<!\S)' . $leftMarker . '(?![\r\n])((?:\\\\' . $rightMarker . '|\\\\' . $leftMarker . '|[^' . $rightMarker . '\r\n])+?)' . $rightMarker . '(?!\w)/s';
        }

        $this->storeRuntimeCacheValue($cacheKey, $patterns);

        return $patterns;
    }
}
