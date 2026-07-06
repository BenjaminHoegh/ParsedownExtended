<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Inline;

trait TypographerExtension
{
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
        if (!$this->configEnabled('typographer')) {
            return null;
        }

        if (empty($Excerpt['text'])) {
            return null;
        }

        static $substitutions = null;
        static $lastEllipsesKey = null;

        // Only update ellipses if config changes
        $ellipsesKey = $this->configEnabled('smartypants') && $this->configEnabled('smartypants.smart_ellipses')
            ? $this->configValue('smartypants.substitutions.ellipses')
            : '...';

        if ($substitutions === null || $ellipsesKey !== $lastEllipsesKey) {
            $lastEllipsesKey = $ellipsesKey;
            $ellipses = $ellipsesKey === '...' ? '...' : html_entity_decode($ellipsesKey);

            $substitutions = [
                '/^\(c\)/i'      => '©',
                '/^\(r\)/i'      => '®',
                '/^\(tm\)/i'     => '™',
                '/^\(p\)/i'      => '¶',
                '/^\+-/i'        => '±',
                '/^!\.{3,}/i'    => '!..',
                '/^\?\.{3,}/i'   => '?..',
                '/^\.{2,}/i'     => $ellipses,
            ];
        }

        foreach ($substitutions as $pattern => $replacement) {
            if (preg_match($pattern, $Excerpt['text'], $matches)) {
                $match = $matches[0];
                return [
                    'extent' => strlen($match),
                    'element' => [
                        'text' => $replacement,
                    ],
                ];
            }
        }

        return null;
    }
}
