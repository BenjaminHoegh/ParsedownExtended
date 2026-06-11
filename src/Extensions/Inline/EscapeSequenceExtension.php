<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Inline;

trait EscapeSequenceExtension
{
    /**
     * Processes inline escape sequences.
     *
     * Handles escape sequences to allow special characters to be rendered as literals instead of being interpreted.
     * Specifically, if a character is preceded by a backslash, it is treated as an escaped character.
     * Additionally, it ensures that math delimiters are not mistakenly escaped.
     *
     * @since 0.1.0
     *
     * @param array $Excerpt The portion of text being parsed to identify escape sequences.
     * @return array|null The parsed escape sequence element or null if no valid escape sequence is found.
     */
    protected function inlineEscapeSequence($Excerpt)
    {
        $config = $this->config();

        // If math is enabled, check for any inline math delimiters that might need special handling.
        if ($config->get('math')) {
            foreach ($this->getInlineMathPatterns($config->get('math.inline.delimiters')) as $regex) {
                // If a math notation match is found, return null as it's not an escape sequence
                if (preg_match($regex, $Excerpt['text'])) {
                    return null;
                }
            }
        }

        // Check if the character following the backslash is a special character that should be escaped
        if (isset($Excerpt['text'][1]) && in_array($Excerpt['text'][1], $this->specialCharacters, true)) {
            // Return the escaped character
            return [
                'markup' => $Excerpt['text'][1], // The character to be escaped
                'extent' => 2, // The length of the escape sequence (backslash + character)
            ];
        }

        // If no valid escape sequence is found, return null
        return null;
    }
}
