<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Inline;

trait SubscriptExtension
{
    /**
     * Processes inline subscript elements.
     *
     * Handles inline subscript denoted by a tilde (`~text~`). If enabled in the configuration,
     * this will convert the marked text into an HTML `<sub>` tag, which is typically used for subscripts in text.
     *
     * @since 1.0.0
     *
     * @param array $Excerpt The portion of text being parsed to identify subscript.
     * @return array|null The parsed subscript element or null if subscript is disabled or not applicable.
     */
    protected function inlineSubscript(array $Excerpt): ?array
    {
        if (!$this->configEnabled('emphasis') || !$this->configEnabled('emphasis.subscript')) {
            return null;
        }

        // Early return if no text follows the tilde or the next character is a tilde
        if (!isset($Excerpt['text'][1]) || '~' === $Excerpt['text'][1]) {
            return null;
        }

        // Match the tilde symbols for subscript (`~text~`) using regex
        if (preg_match('/^~((?:\\\\~|[^~]|~~[^~]*~~)+?)~(?!~)/s', $Excerpt['text'], $matches)) {
            // Return the parsed subscript element
            return [
                'extent' => strlen($matches[0]), // The length of the matched subscript text
                'element' => [
                    'name' => 'sub', // The HTML tag used for subscript
                    'text' => $matches[1], // The content inside the subscript markers
                ],
            ];
        }

        return null; // If no match is found, return null
    }
}
