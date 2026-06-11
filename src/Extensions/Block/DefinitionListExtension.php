<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Block;

trait DefinitionListExtension
{
    /**
     * Handles the parsing of definition list blocks.
     *
     * @since 0.1.0
     *
     * @param array $Line The current line to be processed.
     * @param array $Block The current block context.
     * @return mixed The parsed definition list block if enabled, otherwise nothing.
     */
    protected function blockDefinitionList($Line, $Block)
    {
        // Check if definition lists are enabled
        if ($this->configEnabled('definition_lists')) {
            return parent::blockDefinitionList($Line, $Block); // Delegate to parent class
        }

        return null;
    }
}
