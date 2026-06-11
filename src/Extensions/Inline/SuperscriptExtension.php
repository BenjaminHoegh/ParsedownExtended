<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Inline;

trait SuperscriptExtension
{
    /**
     * Processes inline superscript elements.
     *
     * Handles inline superscript denoted by a caret symbol (`^text^`). If enabled in the configuration,
     * this will convert the marked text into an HTML `<sup>` tag, which is typically used for superscripts in text.
     *
     * @since 1.0.0
     *
     * @param array $Excerpt The portion of text being parsed to identify superscript.
     * @return array|null The parsed superscript element or null if superscript is disabled or not applicable.
     */
    protected function inlineSuperscript(array $Excerpt): ?array
    {
        $config = $this->config();

        // Check if superscript is enabled in the configuration settings
        if (!$config->get('emphasis.superscript') || !$config->get('emphasis')) {
            return null; // Return null if superscript or general emphasis is disabled
        }

        // Early return if no text follows the caret
        if (!isset($Excerpt['text'][1]) || '^' === $Excerpt['text'][1]) {
            return null;
        }

        // Match the caret symbols for superscript (`^text^`) using regex
        if (preg_match('/^\^((?:\\\\\\^|[^\^]|\^[^\^]+?\^\^)+?)\^(?!\^)/s', $Excerpt['text'], $matches)) {
            // Return the parsed superscript element
            return [
                'extent' => strlen($matches[0]), // The length of the matched superscript text
                'element' => [
                    'name' => 'sup', // The HTML tag used for superscript
                    'text' => $matches[1], // The content inside the superscript markers
                ],
            ];
        }

        return null; // If no match is found, return null
    }
}
