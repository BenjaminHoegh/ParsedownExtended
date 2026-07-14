<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Block;

trait HeadingExtension
{
    /**
     * Parses attribute data for headings.
     *
     * Handles parsing of attribute data for headings if the feature is enabled.
     *
     * @since 0.1.0
     *
     * @param string $attributeString The attribute string to be parsed.
     * @return array The parsed attributes or an empty array if not applicable.
     */
    protected function parseAttributeData($attributeString)
    {
        // Check if special attributes for headings are enabled
        if ($this->configEnabled('headings.special_attributes')) {
            return parent::parseAttributeData($attributeString); // Delegate to parent class
        }

        return []; // Return an empty array if the feature is disabled
    }

    /**
     * Processes ATX-style headers (e.g., `# Header Text`).
     *
     * This function processes ATX-style headers, checks if the heading levels are allowed, generates an anchor ID for the
     * header, and adds it to the Table of Contents (TOC) if applicable.
     *
     * @since 0.1.0
     *
     * @param array $Line The line being processed to determine if it is a header.
     * @return array|null The parsed header block with added attributes or null if the header is not allowed.
     */
    protected function blockHeader($Line)
    {
        // Check if headings are enabled in the configuration settings
        if (!$this->configEnabled('headings')) {
            return null; // Return null if headings are disabled
        }

        // Use the parent class to parse the header block
        $Block = parent::blockHeader($Line);

        if (!empty($Block)) {
            return $this->finalizeHeadingBlock($Block);
        }

        return null; // Return null if the header block is empty
    }

    /**
     * Processes Setext-style headers (e.g., `Header Text` followed by `===` or `---`).
     *
     * This function processes Setext-style headers, checks if the heading levels are allowed, generates an anchor ID for the
     * header, and adds it to the Table of Contents (TOC) if applicable.
     *
     * @since 0.1.0
     *
     * @param array $Line The line being processed for a Setext header.
     * @param array|null $Block The existing block context (if any).
     * @return array|null The parsed Setext header block with added attributes or null if the header is not allowed.
     */
    protected function blockSetextHeader($Line, $Block = null)
    {
        // Check if headings are enabled in the configuration settings
        if (!$this->configEnabled('headings')) {
            return null; // Return null if headings are disabled
        }

        // Use the parent class to parse the Setext header block
        $Block = parent::blockSetextHeader($Line, $Block);

        if (!empty($Block)) {
            return $this->finalizeHeadingBlock($Block);
        }

        return null; // Return null if the Setext header block is empty
    }

    /**
     * Applies shared level, anchor, and TOC handling to a parsed heading block.
     */
    private function finalizeHeadingBlock(array $Block): ?array
    {
        $text = $Block['element']['text'] ?? $Block['element']['handler']['argument'] ?? '';
        $level = $Block['element']['name'];

        if (!$this->configValueSetContains('headings.allowed_levels', $level)) {
            return null;
        }

        if ($this->configEnabled('headings.auto_anchors')) {
            $anchorId = $Block['element']['attributes']['id'] ?? $text;
            $anchorId = $this->createAnchorID($anchorId);

            if (is_string($anchorId) && $anchorId !== '') {
                $Block['element']['attributes']['id'] = $anchorId;
            } elseif (
                isset($Block['element']['attributes']['id'])
                && $Block['element']['attributes']['id'] === ''
            ) {
                unset($Block['element']['attributes']['id']);
            }
        }

        if (!$this->configEnabled('toc') || !$this->configValueSetContains('toc.levels', $level)) {
            return $Block;
        }

        $id = $Block['element']['attributes']['id'] ?? null;
        if (!is_string($id) || $id === '') {
            return $Block;
        }

        $this->setContentsList(['text' => $text, 'id' => $id, 'level' => $level]);

        return $Block;
    }
}
