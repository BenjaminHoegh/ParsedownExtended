<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Block;

trait FootnoteExtension
{
    /**
     * Handles the parsing of footnote blocks.
     *
     * @since 0.1.0
     *
     * @param array $Line The line to be processed as a footnote.
     * @return mixed The parsed footnote block if enabled, otherwise nothing.
     */
    protected function blockFootnote($Line)
    {
        // Check if footnotes are enabled
        if ($this->config()->get('footnotes')) {
            return parent::blockFootnote($Line); // Delegate to parent class
        }

        return null;
    }
}
