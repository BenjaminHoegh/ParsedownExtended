<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Block;

trait BlockMathExtension
{
    /**
     * Processes block-level math notation.
     *
     * This function identifies and processes blocks of text surrounded by specific math delimiters (e.g., `$$` or `\\[ ... \\]`)
     * to be formatted as math elements.
     *
     * @since 1.1.2
     *
     * @param array $Line The line being processed for a math block.
     * @return array|null The parsed math block if matched, otherwise null.
     */
    protected function blockMathNotation($Line)
    {
        // Check if math notation block-level parsing is enabled in the configuration settings
        if (!$this->configEnabled('math') || !$this->configEnabled('math.block')) {
            return null; // Return null if math block parsing is disabled
        }

        // Iterate over each configured math block delimiter (e.g., `$$`, `\\[`)
        $delimiters = $this->configValue('math.block.delimiters');
        foreach ($delimiters as $dConfig) {

            // Escape the math delimiters for regex usage
            $leftMarker = preg_quote($dConfig['left'], '/');
            $rightMarker = preg_quote($dConfig['right'], '/');

            // Build the regex pattern to match the opening delimiter, content, and optional closing delimiter
            $regex = '/^(?<!\\\\)('. $leftMarker . ')(.*?)(?:(' . $rightMarker . ')|$)/';

            // Check if the line matches the math block pattern
            if (preg_match($regex, $Line['text'], $matches)) {
                $Block = [
                    'element' => [
                        'text' => $matches[2], // Extract and store the math content between the delimiters
                    ],
                    'start' => $dConfig['left'], // Store the start marker (e.g., `$$`)
                    'end' => $dConfig['right'], // Store the end marker (e.g., `$$`)
                ];

                if (isset($matches[3]) && $matches[3] !== '') {
                    $Block['complete'] = true;
                    $Block['math'] = true;
                }

                return $Block;
            }
        }

        return null; // Return null if the line does not match any configured math block pattern
    }

    /**
     * Continues processing block-level math notation by adding subsequent lines.
     *
     * This function handles the continuation of a math block until the closing delimiter is found.
     *
     * @since 1.1.2
     *
     * @param array $Line The current line being processed.
     * @param array $Block The current math block being extended.
     * @return array|null The updated math block or null if the continuation is not applicable.
     */
    protected function blockMathNotationContinue($Line, $Block)
    {
        // If the math block is already complete, return null
        if (isset($Block['complete'])) {
            return null;
        }

        // Handle interrupted lines in the math block by adding newlines
        if (isset($Block['interrupted'])) {
            // Convert the 'interrupted' flag to an integer to determine the number of newlines
            $Block['interrupted'] = (int) $Block['interrupted'];

            // Append the appropriate number of newlines to maintain line breaks
            $Block['element']['text'] .= str_repeat("\n", $Block['interrupted']);
            unset($Block['interrupted']); // Reset the interrupted flag
        }

        // Double escape the right marker to properly build the regex pattern for closing delimiter
        $rightMarker = preg_quote($Block['end'], '/');
        $regex = '/^(?<!\\\\)(' . $rightMarker . ')(.*)/';

        // Check if the current line contains the closing delimiter
        if (preg_match($regex, $Line['text'], $matches)) {
            $Block['complete'] = true; // Mark the block as complete
            $Block['math'] = true; // Indicate this is a math block
            $Block['element']['text'] = $Block['start'] . $Block['element']['text'] . $Block['end'] . $matches[2];

            return $Block; // Return the completed block
        }

        // Append the current line's text to the math block
        $Block['element']['text'] .= "\n" . $Line['body'];

        return $Block; // Return the updated block
    }

    /**
     * Completes the block-level math notation.
     *
     * This function is called when a math block is finalized.
     *
     * @since 1.1.2
     *
     * @param array $Block The current block being finalized.
     * @return array The completed math block.
     */
    protected function blockMathNotationComplete($Block)
    {
        return $Block; // Finalize and return the completed math block
    }
}
