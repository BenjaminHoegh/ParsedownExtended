<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Block;

trait CommentExtension
{
    /**
     * Handles the parsing of HTML comment blocks.
     *
     * @since 0.1.0
     *
     * @param array $Line The current line to be processed as a comment.
     * @return mixed The parsed comment block if enabled, otherwise nothing.
     */
    protected function blockComment($Line)
    {
        // Check if HTML comments are enabled
        if ($this->config()->get('comments')) {
            return parent::blockComment($Line); // Delegate to parent class
        }

        return null;
    }
}
