<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Inline;

trait TypographerExtension
{
    /**
     * Quickly rejects ordinary punctuation before the inline parser allocates an excerpt.
     */
    protected function inlineTypographerMarkerMatches(string $text, int $position): bool
    {
        $first = $text[$position] ?? '';
        $next = $text[$position + 1] ?? '';

        if ($first === '(') {
            $candidate = strtolower(substr($text, $position, 4));

            return strncmp($candidate, '(c)', 3) === 0
                || strncmp($candidate, '(r)', 3) === 0
                || strncmp($candidate, '(p)', 3) === 0
                || $candidate === '(tm)';
        }

        if ($first === '+') {
            return $next === '-';
        }

        if ($first === '!' || $first === '?') {
            return $next === '.' && strspn($text, '.', $position + 1) >= 3;
        }

        return $first === '.' && $next === '.';
    }

    /**
     * Processes inline typographic substitutions.
     *
     * This function handles typographic improvements, such as replacing plain text with their typographic equivalents.
     * It processes symbols like (c) to ©, (r) to ®, and smart ellipses based on the user's configuration.
     * This is particularly useful for enhancing readability by applying typographer rules.
     *
     * @since 1.0.1
     *
     * @param array $Excerpt The portion of text being parsed for typographic substitutions.
     * @return array|null The parsed typographic substitutions or null if the typographer feature is disabled.
     */
    protected function inlineTypographer(array $Excerpt): ?array
    {
        if (
            !$this->configEnabled('typographer') ||
            empty($Excerpt['text'])
        ) {
            return null;
        }

        $text = $Excerpt['text'];
        $first = $text[0];

        if ($first === '(') {
            $lower = strtolower(substr($text, 0, 4));
            $replacement = null;
            $extent = 3;

            if (strncmp($lower, '(c)', 3) === 0) {
                $replacement = '©';
            } elseif (strncmp($lower, '(r)', 3) === 0) {
                $replacement = '®';
            } elseif (strncmp($lower, '(p)', 3) === 0) {
                $replacement = '¶';
            } elseif ($lower === '(tm)') {
                $replacement = '™';
                $extent = 4;
            }

            if ($replacement !== null) {
                return [
                    'extent' => $extent,
                    'element' => [
                        'text' => $replacement,
                    ],
                ];
            }
        } elseif ($first === '+') {
            if (isset($text[1]) && $text[1] === '-') {
                return [
                    'extent' => 2,
                    'element' => [
                        'text' => '±',
                    ],
                ];
            }
        } elseif ($first === '!' || $first === '?') {
            if (!isset($text[1]) || $text[1] !== '.') {
                return null;
            }

            $dots = strspn($text, '.', 1);
            if ($dots >= 3) {
                return [
                    'extent' => $dots + 1,
                    'element' => [
                        'text' => $first . '..',
                    ],
                ];
            }
        } elseif ($first === '.') {
            if (!isset($text[1]) || $text[1] !== '.') {
                return null;
            }

            $dots = strspn($text, '.');
            if ($dots >= 2) {
                static $lastEllipsesKey = null;
                static $ellipses = '...';

                // Only update ellipses if config changes.
                $ellipsesKey = $this->configEnabled('smartypants') && $this->configEnabled('smartypants.smart_ellipses')
                    ? $this->configValue('smartypants.substitutions.ellipses')
                    : '...';

                if ($ellipsesKey !== $lastEllipsesKey) {
                    $lastEllipsesKey = $ellipsesKey;
                    $ellipses = $ellipsesKey === '...' ? '...' : html_entity_decode($ellipsesKey);
                }

                return [
                    'extent' => $dots,
                    'element' => [
                        'text' => $ellipses,
                    ],
                ];
            }
        }

        return null;
    }
}
