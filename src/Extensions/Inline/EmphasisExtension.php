<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Inline;

trait EmphasisExtension
{
    /**
     * Processes inline emphasis elements.
     *
     * Handles inline emphasis (like bold or italics) if enabled in the configuration.
     *
     * @since 0.1.0
     *
     * @param array $Excerpt The portion of text being parsed
     * @return array|null The parsed emphasis or null if not processed
     */
    protected function inlineEmphasis($Excerpt)
    {
        $config = $this->config();

        if (!$config->get('emphasis') || !isset($Excerpt['text'][1])) {
            return null; // If emphasis is disabled or the excerpt is too short, return null
        }

        $marker = $Excerpt['text'][0]; // Extract the marker character ('*', '_', etc.)

        // Check if the text matches bold emphasis using the marker
        if ($config->get('emphasis.bold') && preg_match($this->StrongRegex[$marker], $Excerpt['text'], $matches)) {
            $emphasis = 'strong'; // Use 'strong' for bold text
        }
        // Check if the text matches italic emphasis using the marker
        elseif ($config->get('emphasis.italic') && preg_match($this->EmRegex[$marker], $Excerpt['text'], $matches)) {
            $emphasis = 'em'; // Use 'em' for italic text
        } else {
            return null; // No valid emphasis match found
        }

        // Return the parsed emphasis element
        return [
            'extent' => strlen($matches[0]), // Length of the matched emphasis text
            'element' => [
                'name' => $emphasis, // 'strong' for bold or 'em' for italics
                'handler' => 'line', // Handler for further inline processing
                'text' => $matches[1], // The emphasized content
            ],
        ];
    }
}
