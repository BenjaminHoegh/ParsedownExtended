<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Inline;

trait InsertionsExtension
{
    /**
     * Processes inline insertion elements.
     *
     * Handles inline insertions denoted by double plus signs (`++text++`). If enabled in the configuration,
     * this will convert the marked text into an HTML `<ins>` tag, which is commonly used to indicate additions.
     *
     * @since 1.2.0
     *
     * @param array $Excerpt The portion of text being parsed to identify insertions.
     * @return array|null The parsed insertion element or null if insertions are disabled or not applicable.
     */
    protected function inlineInsertions(array $Excerpt): ?array
    {
        $config = $this->config();

        // Check if insertions are enabled in the configuration settings
        if (!$config->get('emphasis.insertions') || !$config->get('emphasis')) {
            return null; // Return null if insertions or general emphasis is disabled
        }

        // Early return if the excerpt does not start with two '+' characters
        if (!isset($Excerpt['text'][1]) || $Excerpt['text'][1] !== '+') {
            return null;
        }

        // Match the double plus signs for insertions (`++text++`) using regex
        if (preg_match('/^\+\+((?:\\\\\+|[^\+]|\+[^\+]*\+)+?)\+\+(?!\+)/s', $Excerpt['text'], $matches)) {
            // Return the parsed insertion element
            return [
                'extent' => strlen($matches[0]), // The length of the matched insertion text
                'element' => [
                    'name' => 'ins', // The HTML tag used for insertions
                    'text' => $matches[1], // The content inside the insertion
                ],
            ];
        }

        return null; // If no match is found, return null
    }
}
