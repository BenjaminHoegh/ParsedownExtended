<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Inline;

trait StrikethroughExtension
{
    /**
     * Processes inline strikethrough elements.
     *
     * Handles inline strikethrough text if the emphasis is enabled in the configuration.
     *
     * @since 0.1.0
     *
     * @param array $Excerpt The portion of text being parsed
     * @return mixed|null The parsed strikethrough or null if not processed
     */
    protected function inlineStrikethrough($Excerpt)
    {
        if ($this->configEnabled('emphasis.strikethroughs') && $this->configEnabled('emphasis')) {
            return parent::inlineStrikethrough($Excerpt);
        }

        return null;
    }
}
