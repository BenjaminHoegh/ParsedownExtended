<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Inline;

trait InlineImageExtension
{
    /**
     * Processes inline images.
     *
     * Handles inline images if the feature is enabled in the configuration.
     *
     * @since 0.1.0
     *
     * @param array $Excerpt The portion of text being parsed
     * @return mixed|null The parsed image element or null if not processed
     */
    protected function inlineImage($Excerpt)
    {
        if ($this->configEnabled('images')) {
            return parent::inlineImage($Excerpt);
        }

        return null;
    }
}
