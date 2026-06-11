<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Block;

trait TableExtension
{
    /**
     * Handles the parsing of table blocks.
     *
     * @since 0.1.0
     *
     * @param array $Line The current line to be processed.
     * @param array|null $Block The current block context.
     * @return mixed The parsed table block if enabled, otherwise nothing.
     */
    protected function blockTable($Line, $Block = null)
    {
        // Check if tables are enabled
        if ($this->configEnabled('tables')) {
            return parent::blockTable($Line, $Block); // Delegate to parent class
        }

        return null;
    }
}
