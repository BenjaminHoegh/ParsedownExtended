<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Inline;

trait SmartypantsExtension
{
    /**
     * Processes inline Smartypants substitutions.
     *
     * This function handles typographic improvements to the text, such as converting straight quotes to curly quotes,
     * converting double angle quotes, converting dashes into em or en dashes, and ellipses into the proper character.
     * These changes enhance readability and align text formatting with common typographic standards.
     *
     * @since 1.0.0
     *
     * @param array $Excerpt The portion of text being parsed for Smartypants substitutions.
     * @return array|null The parsed Smartypants substitution or null if Smartypants is disabled.
     */
    protected function inlineSmartypants($Excerpt)
    {
        // Check if Smartypants is enabled in the configuration settings
        if (!$this->configEnabled('smartypants')) {
            return null; // Return null if Smartypants is disabled
        }

        // Substitutions: Load the characters to use for the specific Smartypants transformations
        static $substitutions = null;
        static $substitutionsKey = '';

        $substitutionValues = [
            'left_double_quote' => $this->configValue('smartypants.substitutions.left_double_quote'),
            'right_double_quote' => $this->configValue('smartypants.substitutions.right_double_quote'),
            'left_single_quote' => $this->configValue('smartypants.substitutions.left_single_quote'),
            'right_single_quote' => $this->configValue('smartypants.substitutions.right_single_quote'),
            'left_angle_quote' => $this->configValue('smartypants.substitutions.left_angle_quote'),
            'right_angle_quote' => $this->configValue('smartypants.substitutions.right_angle_quote'),
            'mdash' => $this->configValue('smartypants.substitutions.mdash'),
            'ndash' => $this->configValue('smartypants.substitutions.ndash'),
            'ellipses' => $this->configValue('smartypants.substitutions.ellipses'),
        ];
        $cacheKey = implode("\0", $substitutionValues);

        if ($substitutions === null || $substitutionsKey !== $cacheKey) {
            $substitutionsKey = $cacheKey;
            $substitutions = [];

            foreach ($substitutionValues as $name => $value) {
                $substitutions[$name] = html_entity_decode($value);
            }
        }

        $text = $Excerpt['text'];
        $first = $text[0] ?? '';

        // ``like this''
        if ('`' === $first && $this->configEnabled('smartypants.smart_backticks')) {
            if (preg_match('/^``(?!\s)([^"\'`]+)\'\'/i', $text, $matches)) {
                if (strlen(trim($Excerpt['before'])) > 0) {
                    return null;
                }

                return [
                    'extent' => strlen($matches[0]),
                    'element' => [
                        'text' => $substitutions['left_double_quote'] . $matches[1] . $substitutions['right_double_quote'],
                    ],
                ];
            }
        }

        // "like this" or 'like this'
        if (('"' === $first || "'" === $first) && $this->configEnabled('smartypants.smart_quotes')) {
            if (preg_match('/^(\")(?!\s)([^\"]+)\"|^(?<!\w)(\')(?!\s)([^\']+)\'/i', $text, $matches)) {
                if (strlen(trim($Excerpt['before'])) > 0) {
                    return null;
                }

                if (isset($matches[3]) && $matches[3] === "'") {
                    return [
                        'extent' => strlen($matches[0]),
                        'element' => [
                            'text' => $substitutions['left_single_quote'] . $matches[4] . $substitutions['right_single_quote'],
                        ],
                    ];
                }

                return [
                    'extent' => strlen($matches[0]),
                    'element' => [
                        'text' => $substitutions['left_double_quote'] . $matches[2] . $substitutions['right_double_quote'],
                    ],
                ];
            }
        }

        // <<like this>>
        if ('<' === $first && $this->configEnabled('smartypants.smart_angled_quotes')) {
            if (preg_match('/^<{2}(?!\s)([^<>]+)>{2}/i', $text, $matches)) {
                if (strlen(trim($Excerpt['before'])) > 0) {
                    return null;
                }

                return [
                    'extent' => strlen($matches[0]),
                    'element' => [
                        'text' => $substitutions['left_angle_quote'] . $matches[1] . $substitutions['right_angle_quote'],
                    ],
                ];
            }
        }

        // -- or ---
        if ('-' === $first && $this->configEnabled('smartypants.smart_dashes')) {
            if (preg_match('/^(-{2,3})(?!-)/', $text, $matches)) {
                if ('---' === $matches[1]) {
                    return [
                        'extent' => strlen($matches[1]),
                        'element' => [
                            'text' => $substitutions['mdash'],
                        ],
                    ];
                }

                if ('--' === $matches[1]) {
                    return [
                        'extent' => 2,
                        'element' => [
                            'text' => $substitutions['ndash'],
                        ],
                    ];
                }
            }
        }

        // ...
        if ('.' === $first && $this->configEnabled('smartypants.smart_ellipses')) {
            if (preg_match('/^(?<!\.)(\.{3})(?!\.)/i', $text, $matches)) {
                return [
                    'extent' => strlen($matches[0]),
                    'element' => [
                        'text' => $substitutions['ellipses'],
                    ],
                ];
            }
        }

        return null;
    }
}
