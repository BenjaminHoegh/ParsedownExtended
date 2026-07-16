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
        // If math is enabled, check for any inline math delimiters that might need special handling.
        if ($this->configEnabled('math')) {
            foreach ($this->getInlineMathPatterns($this->configValue('math.inline.delimiters')) as $regex) {
                // If a math notation match is found, return null as it's not an escape sequence
                if (preg_match($regex, $Excerpt['text'])) {
                    return null;
                }
            }
        }

        $escapedCharacter = $Excerpt['text'][1] ?? null;

        // Check if the character following the backslash is enabled and escapable.
        if (is_string($escapedCharacter) && $this->isEscapableSpecialCharacter($escapedCharacter)) {
            // Return the escaped character
            return [
                'element' => $escapedCharacter === '<'
                    ? ['text' => $escapedCharacter]
                    : ['rawHtml' => $escapedCharacter],
                'extent' => 2, // The length of the escape sequence (backslash + character)
            ];
        }

        // If no valid escape sequence is found, return null
        return null;
    }

    /**
     * Determine whether a backslash may escape an active inline marker.
     *
     * Optional extensions register their markers up front, but disabling an
     * extension must not change Parsedown-compatible escaping behavior.
     */
    private function isEscapableSpecialCharacter(string $character): bool
    {
        if (isset(self::PARSEDOWN_ESCAPABLE_SPECIAL_CHARACTERS[$character])) {
            return true;
        }

        return match ($character) {
            '=' => $this->configEnabled('emphasis') && $this->configEnabled('emphasis.mark'),
            '$' => $this->configEnabled('math'),
            '^' => $this->configEnabled('emphasis') && $this->configEnabled('emphasis.superscript'),
            ':' => $this->configEnabled('emojis'),
            '"', "'", '<' => $this->configEnabled('smartypants'),
            '?' => $this->configEnabled('typographer'),
            default => false,
        };
    }
}
