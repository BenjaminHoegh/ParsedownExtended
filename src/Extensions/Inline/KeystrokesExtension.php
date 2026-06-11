<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Inline;

trait KeystrokesExtension
{
    /**
     * Processes inline keystroke elements.
     *
     * Handles inline keystrokes denoted by double square brackets (`[[text]]`). If enabled in the configuration,
     * this will convert the enclosed text into an HTML `<kbd>` tag, which is typically used to represent user input or keystrokes.
     *
     * @since 1.0.0
     *
     * @param array $Excerpt The portion of text being parsed to identify keystrokes.
     * @return array|null The parsed keystroke element or null if keystrokes are disabled or not applicable.
     */
    protected function inlineKeystrokes(array $Excerpt): ?array
    {
        $config = $this->config();

        // Check if keystrokes are enabled in the configuration settings
        if (!$config->get('emphasis.keystrokes') || !$config->get('emphasis')) {
            return null; // Return null if keystrokes or general emphasis is disabled
        }

        // Early return if the excerpt does not start with two '[' characters
        if (!isset($Excerpt['text'][1]) || '[' !== $Excerpt['text'][1]) {
            return null;
        }

        // Match the double square brackets for keystrokes (`[[text]]`) using regex
        if (preg_match('/^(?<!\[)\[\[([^\[\]]*|[\[\]])\]\](?!\])/s', $Excerpt['text'], $matches)) {
            // Return the parsed keystroke element
            return [
                'extent' => strlen($matches[0]), // The length of the matched keystroke text
                'element' => [
                    'name' => 'kbd', // The HTML tag used for keystrokes
                    'text' => $matches[1], // The content inside the keystroke brackets
                ],
            ];
        }

        return null; // If no match is found, return null
    }
}
