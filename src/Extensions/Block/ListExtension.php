<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Block;

trait ListExtension
{
    /**
     * Handles the parsing of list blocks.
     *
     * @since 0.1.0
     *
     * @param array $Line The current line to be processed.
     * @param array|null $CurrentBlock The current block context.
     * @return mixed The parsed list block if enabled, otherwise nothing.
     */
    protected function blockList($Line, ?array $CurrentBlock = null)
    {
        // Check if lists are enabled
        if ($this->config()->get('lists')) {
            return parent::blockList($Line, $CurrentBlock); // Delegate to parent class
        }

        return null;
    }
}
