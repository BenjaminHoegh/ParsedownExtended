<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Block;

trait TaskListExtension
{
    /**
     * Processes list items, including handling task list syntax for checkboxes.
     *
     * This function processes list items in Markdown and handles special task list syntax (e.g., `- [x]` or `- [ ]`).
     * It converts list items into Parsedown 1.8 elements and renders checkboxes when task lists are enabled.
     *
     * @since 0.1.0
     *
     * @param array $lines The lines that make up the list item being processed.
     * @return array The parsed list item as an array of elements.
     */
    protected function li($lines)
    {
        // Check if task lists are enabled in the configuration settings
        if (!$this->configEnabled('lists.tasks')) {
            return parent::li($lines); // Return the default list item if task lists are not enabled
        }

        $Elements = $this->linesElements($lines);
        $paragraphIndex = 0;

        // Extract the text of the first element to check for a task list checkbox
        $text = $Elements[0]['handler']['argument'] ?? null;
        $firstFourChars = is_string($text) ? substr($text, 0, 4) : '';

        // Check if the list item starts with a checkbox (e.g., `[x]` or `[ ]`)
        if (is_string($text) && preg_match('/^\[[x ]\]/i', $firstFourChars, $matches)) {
            // Remove the checkbox marker from the beginning of the text
            $Elements[0]['handler']['argument'] = substr_replace($text, '', 0, 4);

            // Prepare attributes for the checkbox element
            $inputAttributes = [
                'type'     => 'checkbox',
                'disabled' => 'disabled',
            ];

            if (strtolower($matches[0]) === '[x]') {
                $inputAttributes['checked'] = 'checked';
            }

            // Insert the checkbox element at the beginning of the list item
            array_unshift($Elements, [
                'name'       => 'input',
                'attributes' => $inputAttributes,
                'autobreak'  => false,
            ]);

            $paragraphIndex = 1;
        }

        // Remove unnecessary paragraph tags for the list item if not interrupted
        if (!in_array('', $lines, true) && isset($Elements[$paragraphIndex]['name']) && $Elements[$paragraphIndex]['name'] === 'p') {
            unset($Elements[$paragraphIndex]['name']); // Remove paragraph wrapper
        }

        return $Elements; // Return the final array of elements for the list item
    }
}
