<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Block;

trait ReferenceExtension
{
    /**
     * Handles the parsing of reference blocks.
     *
     * @since 0.1.0
     *
     * @param array $Line The current line to be processed as a reference.
     * @return mixed The parsed reference block if enabled, otherwise nothing.
     */
    protected function blockReference($Line)
    {
        // Check if references are enabled
        if ($this->config()->get('references')) {
            return parent::blockReference($Line); // Delegate to parent class
        }

        return null;
    }
}
