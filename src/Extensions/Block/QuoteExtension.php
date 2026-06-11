<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Block;

trait QuoteExtension
{
    /**
     * Handles the parsing of block quote elements.
     *
     * @since 0.1.0
     *
     * @param array $Line The current line to be processed as a block quote.
     * @return mixed The parsed block quote if enabled, otherwise nothing.
     */
    protected function blockQuote($Line)
    {
        // Check if block quotes are enabled
        if ($this->configEnabled('quotes')) {
            return parent::blockQuote($Line); // Delegate to parent class
        }

        return null;
    }
}
