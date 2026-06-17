<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Inline;

trait InlineCodeExtension
{
    /**
     * Processes inline code elements.
     *
     * Handles inline code if it is enabled in the configuration settings.
     *
     * @since 0.1.0
     *
     * @param array $Excerpt The portion of text being parsed
     * @return mixed|null The parsed element or null if not processed
     */
    protected function inlineCode($Excerpt)
    {
        $marker = $Excerpt['text'][0];
        if (!isset($Excerpt['text'][1]) || strpos($Excerpt['text'], $marker, 1) === false) {
            return null;
        }

        if ($this->configEnabled('code') && $this->configEnabled('code.inline')) {
            return parent::inlineCode($Excerpt);
        }

        return null;
    }
}
