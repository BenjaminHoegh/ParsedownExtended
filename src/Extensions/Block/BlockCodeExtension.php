<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Block;

trait BlockCodeExtension
{
    /**
     * Handles the parsing of code blocks.
     *
     * @since 0.1.0
     *
     * @param array $Line The current line to be processed.
     * @param array|null $Block The current block context.
     * @return mixed The parsed code block if enabled, otherwise nothing.
     */
    protected function blockCode($Line, $Block = null)
    {
        // Check if code blocks are enabled
        if ($this->config()->get('code') && $this->config()->get('code.blocks')) {
            return parent::blockCode($Line, $Block); // Delegate to parent class
        }

        return null;
    }
}
