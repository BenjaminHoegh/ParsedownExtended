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

        $text = $Excerpt['text'];
        $first = $text[0] ?? '';

        if (
            $first !== '`' &&
            $first !== '"' &&
            $first !== "'" &&
            $first !== '<' &&
            $first !== '-' &&
            $first !== '.'
        ) {
            return null;
        }

        $before = $Excerpt['before'] ?? '';
        $hasNonWhitespaceBefore = $before !== '' && trim($before) !== '';

        // ``like this''
        if ('`' === $first) {
            if (
                !$this->configEnabled('smartypants.smart_backticks') ||
                $hasNonWhitespaceBefore ||
                !isset($text[1]) ||
                $text[1] !== '`'
            ) {
                return null;
            }

            $substitutions = $this->getSmartypantsSubstitutions();
            if (preg_match('/^``(?!\s)([^"\'`]+)\'\'/i', $text, $matches)) {
                return [
                    'extent' => strlen($matches[0]),
                    'element' => [
                        'text' => $substitutions['left_double_quote'] . $matches[1] . $substitutions['right_double_quote'],
                    ],
                ];
            }
        }

        // "like this" or 'like this'
        if ('"' === $first || "'" === $first) {
            if (
                !$this->configEnabled('smartypants.smart_quotes') ||
                $hasNonWhitespaceBefore ||
                !isset($text[1]) ||
                ctype_space($text[1]) ||
                strpos($text, $first, 1) === false
            ) {
                return null;
            }

            $substitutions = $this->getSmartypantsSubstitutions();
            if (preg_match('/^(\")(?!\s)([^\"]+)\"|^(?<!\w)(\')(?!\s)([^\']+)\'/i', $text, $matches)) {
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
        if ('<' === $first) {
            if (
                !$this->configEnabled('smartypants.smart_angled_quotes') ||
                $hasNonWhitespaceBefore ||
                !isset($text[1]) ||
                $text[1] !== '<'
            ) {
                return null;
            }

            $substitutions = $this->getSmartypantsSubstitutions();
            if (preg_match('/^<{2}(?!\s)([^<>]+)>{2}/i', $text, $matches)) {
                return [
                    'extent' => strlen($matches[0]),
                    'element' => [
                        'text' => $substitutions['left_angle_quote'] . $matches[1] . $substitutions['right_angle_quote'],
                    ],
                ];
            }
        }

        // -- or ---
        if ('-' === $first) {
            if (!$this->configEnabled('smartypants.smart_dashes') || !isset($text[1]) || $text[1] !== '-') {
                return null;
            }

            $substitutions = $this->getSmartypantsSubstitutions();
            if (isset($text[2]) && $text[2] === '-' && (!isset($text[3]) || $text[3] !== '-')) {
                return [
                    'extent' => 3,
                    'element' => [
                        'text' => $substitutions['mdash'],
                    ],
                ];
            }

            if (!isset($text[2]) || $text[2] !== '-') {
                return [
                    'extent' => 2,
                    'element' => [
                        'text' => $substitutions['ndash'],
                    ],
                ];
            }
        }

        // ...
        if ('.' === $first) {
            if (
                $this->configEnabled('smartypants.smart_ellipses') &&
                isset($text[1], $text[2]) &&
                $text[1] === '.' &&
                $text[2] === '.' &&
                (!isset($text[3]) || $text[3] !== '.')
            ) {
                $substitutions = $this->getSmartypantsSubstitutions();

                return [
                    'extent' => 3,
                    'element' => [
                        'text' => $substitutions['ellipses'],
                    ],
                ];
            }
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    private function getSmartypantsSubstitutions(): array
    {
        $cacheKey = 'smartypants.substitutions';
        if ($this->hasRuntimeCacheValue($cacheKey)) {
            $substitutions = $this->runtimeCacheValue($cacheKey);

            return is_array($substitutions) ? $substitutions : [];
        }

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

        $substitutions = [];
        foreach ($substitutionValues as $name => $value) {
            $substitutions[$name] = html_entity_decode($value);
        }

        $this->storeRuntimeCacheValue($cacheKey, $substitutions);

        return $substitutions;
    }
}
