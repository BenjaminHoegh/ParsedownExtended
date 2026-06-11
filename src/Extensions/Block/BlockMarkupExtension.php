<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Block;

trait BlockMarkupExtension
{
    /**
     * Handles the parsing of raw HTML markup blocks.
     *
     * @since 0.1.0
     *
     * @param array $Line The current line to be processed as raw HTML.
     * @return mixed The parsed HTML block if allowed, otherwise nothing.
     */
    protected function blockMarkup($Line)
    {
        // Check if raw HTML is allowed
        if ($this->configEnabled('allow_raw_html')) {
            return parent::blockMarkup($Line); // Delegate to parent class
        }

        return null;
    }
}
