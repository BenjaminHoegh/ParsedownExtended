<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Configuration;

final class ConfigurationValidator
{
    private const HEADING_LEVELS = [
        'h1' => true,
        'h2' => true,
        'h3' => true,
        'h4' => true,
        'h5' => true,
        'h6' => true,
    ];

    /**
     * Validates both the schema type and path-specific value structure.
     *
     * @param mixed $value
     */
    public static function validate(string $path, $value, string $expectedType): void
    {
        $actualType = gettype($value);
        if ($expectedType !== $actualType) {
            throw new \InvalidArgumentException("Expected {$expectedType}, got {$actualType}");
        }

        switch ($path) {
            case 'abbreviations.predefined':
                self::validateAbbreviations($path, $value);
                break;

            case 'alerts.types':
            case 'headings.auto_anchors.blacklist':
            case 'links.external_links.internal_hosts':
                self::validateStringList($path, $value);
                break;

            case 'headings.allowed_levels':
            case 'toc.levels':
                self::validateHeadingLevels($path, $value);
                break;

            case 'headings.auto_anchors.replacements':
                self::validateRegexReplacements($path, $value);
                break;

            case 'math.inline.delimiters':
            case 'math.block.delimiters':
                self::validateMathDelimiters($path, $value);
                break;

            case 'headings.auto_anchors.delimiter':
            case 'toc.tag':
                if ($value === '') {
                    self::invalid($path, 'must not be empty');
                }
                break;
        }
    }

    private static function validateAbbreviations(string $path, array $abbreviations): void
    {
        foreach ($abbreviations as $abbreviation => $description) {
            if (!is_string($abbreviation) || $abbreviation === '' || !is_string($description)) {
                self::invalid($path, 'expected non-empty string keys and string descriptions');
            }
        }
    }

    private static function validateStringList(string $path, array $values): void
    {
        if (!self::isList($values)) {
            self::invalid($path, 'expected a list of non-empty strings');
        }

        foreach ($values as $value) {
            if (!is_string($value) || $value === '') {
                self::invalid($path, 'expected a list of non-empty strings');
            }
        }
    }

    private static function validateHeadingLevels(string $path, array $levels): void
    {
        if (!self::isList($levels)) {
            self::invalid($path, 'expected a list containing only h1 through h6');
        }

        foreach ($levels as $level) {
            if (!is_string($level) || !isset(self::HEADING_LEVELS[$level])) {
                self::invalid($path, 'expected a list containing only h1 through h6');
            }
        }
    }

    private static function validateRegexReplacements(string $path, array $replacements): void
    {
        foreach ($replacements as $pattern => $replacement) {
            if (
                !is_string($pattern)
                || $pattern === ''
                || !is_string($replacement)
                || @preg_match($pattern, '') === false
            ) {
                self::invalid($path, 'expected valid regular-expression keys and string replacements');
            }
        }
    }

    private static function validateMathDelimiters(string $path, array $delimiters): void
    {
        if (!self::isList($delimiters)) {
            self::invalid($path, 'expected a list of delimiter pairs');
        }

        foreach ($delimiters as $delimiter) {
            if (
                !is_array($delimiter)
                || !isset($delimiter['left'], $delimiter['right'])
                || !is_string($delimiter['left'])
                || !is_string($delimiter['right'])
                || $delimiter['left'] === ''
                || $delimiter['right'] === ''
            ) {
                self::invalid($path, 'expected non-empty string left/right delimiter pairs');
            }
        }
    }

    private static function isList(array $values): bool
    {
        return $values === [] || array_keys($values) === range(0, count($values) - 1);
    }

    private static function invalid(string $path, string $reason): void
    {
        throw new \InvalidArgumentException("Invalid config value for {$path}: {$reason}");
    }
}
