<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Block;

trait AbbreviationExtension
{
    /** @var bool $predefinedAbbreviationsAdded Tracks whether predefined abbreviations have been merged */
    private bool $predefinedAbbreviationsAdded = false;

    /**
     * Processes abbreviation blocks.
     *
     * This function handles the parsing of abbreviation definitions. It checks if abbreviations are enabled
     * in the configuration and whether custom abbreviations are allowed. If custom abbreviations are allowed,
     * it delegates the parsing to the parent class method.
     *
     * @since 0.1.0
     *
     * @param array $Line The line being processed to determine if it defines an abbreviation.
     * @return array|null The parsed abbreviation block or null if abbreviations are disabled or custom abbreviations are not allowed.
     */
    protected function blockAbbreviation($Line)
    {
        $config = $this->config();

        // Check if abbreviation support is enabled in the configuration settings
        if ($config->get('abbreviations')) {

            // If custom abbreviations are allowed, delegate to the parent class to handle parsing
            if ($config->get('abbreviations.allow_custom')) {
                return parent::blockAbbreviation($Line); // Parse custom abbreviation using parent method
            }

            // If custom abbreviations are not allowed, return null to prevent processing
            return null;
        }

        // Return null if abbreviations are completely disabled in the configuration
        return null;
    }

    /**
     * Registers predefined abbreviations in Parsedown's definition data once per parse.
     *
     * @return void
     */
    private function initializePredefinedAbbreviations(): void
    {
        $config = $this->config();

        if ($this->predefinedAbbreviationsAdded || !$config->get('abbreviations')) {
            return;
        }

        foreach ($config->get('abbreviations.predefined') as $abbreviation => $description) {
            $this->DefinitionData['Abbreviation'][$abbreviation] = $description;
        }

        $this->predefinedAbbreviationsAdded = true;
    }

    /**
     * Processes unmarked text, ensuring predefined abbreviations are initialized before parsing.
     *
     * @param string $text The input text to be processed.
     * @return string The processed text.
     */
    protected function unmarkedText($text)
    {
        $this->initializePredefinedAbbreviations();
        return parent::unmarkedText($text);
    }
}
