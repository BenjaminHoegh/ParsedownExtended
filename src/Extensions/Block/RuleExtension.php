<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Block;

trait RuleExtension
{
    /**
     * Handles the parsing of horizontal rule blocks.
     *
     * @since 0.1.0
     *
     * @param array $Line The current line to be processed.
     * @return mixed The parsed horizontal rule if enabled, otherwise nothing.
     */
    protected function blockRule($Line)
    {
        // Check if thematic breaks (horizontal rules) are enabled
        if ($this->config()->get('thematic_breaks')) {
            return parent::blockRule($Line); // Delegate to parent class
        }

        return null;
    }
}
