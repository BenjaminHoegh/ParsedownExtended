<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Block;

trait TableSpanExtension
{
    /**
     * Completes the processing of table blocks.
     *
     * This function processes table blocks after the initial parsing to handle special features such as column spans
     * and row spans. It processes each cell in the table, merging cells where indicated by specific characters
     * (e.g., '>' for colspan and '^' for rowspan).
     *
     * @since 1.0.1
     *
     * @param array $block The parsed table block to be processed further.
     * @return array The completed and modified table block.
     */
    protected function blockTableComplete(array $block): array
    {
        // Check if table spanning (colspan and rowspan) is enabled
        if (!$this->configEnabled('tables.tablespan')) {
            return $block; // Return the original block if spanning is not enabled
        }

        $headerElements = &$block['element']['elements'][0]['elements'][0]['elements'];

        // Process colspan in header elements
        for ($index = count($headerElements) - 1; $index >= 0; --$index) {
            $colspan = 1;
            $headerElement = &$headerElements[$index];

            while ($index && '>' === $headerElements[$index - 1]['handler']['argument']) {
                ++$colspan;
                $previousHeaderElement = &$headerElements[--$index];
                $previousHeaderElement['merged'] = true;
                if (isset($previousHeaderElement['attributes'])) {
                    $headerElement['attributes'] = $previousHeaderElement['attributes'];
                }
            }

            // Assign colspan attribute if colspan is greater than 1
            if ($colspan > 1) {
                if (!isset($headerElement['attributes'])) {
                    $headerElement['attributes'] = [];
                }
                $headerElement['attributes']['colspan'] = $colspan;
            }
        }

        // Remove merged header elements
        for ($index = count($headerElements) - 1; $index >= 0; --$index) {
            if (isset($headerElements[$index]['merged'])) {
                array_splice($headerElements, $index, 1);
            }
        }

        $rows = &$block['element']['elements'][1]['elements'];

        // Process colspan for rows
        foreach ($rows as &$row) {
            $elements = &$row['elements'];

            for ($index = count($elements) - 1; $index >= 0; --$index) {
                $colspan = 1;
                $element = &$elements[$index];

                while ($index && '>' === $elements[$index - 1]['handler']['argument']) {
                    ++$colspan;
                    $previousElement = &$elements[--$index];
                    $previousElement['merged'] = true;
                    if (isset($previousElement['attributes'])) {
                        $element['attributes'] = $previousElement['attributes'];
                    }
                }

                // Assign colspan attribute if colspan is greater than 1
                if ($colspan > 1) {
                    if (!isset($element['attributes'])) {
                        $element['attributes'] = [];
                    }
                    $element['attributes']['colspan'] = $colspan;
                }
            }
        }
        unset($row);

        // Process rowspan for rows
        foreach ($rows as $rowNo => &$row) {
            $elements = &$row['elements'];

            foreach ($elements as $index => &$element) {
                $rowspan = 1;

                if (isset($element['merged'])) {
                    continue; // Skip merged elements
                }

                while (
                    $rowNo + $rowspan < count($rows) &&
                    $index < count($rows[$rowNo + $rowspan]['elements']) &&
                    '^' === $rows[$rowNo + $rowspan]['elements'][$index]['handler']['argument'] &&
                    (($element['attributes']['colspan'] ?? null) === ($rows[$rowNo + $rowspan]['elements'][$index]['attributes']['colspan'] ?? null))
                ) {
                    $rows[$rowNo + $rowspan]['elements'][$index]['merged'] = true;
                    ++$rowspan;
                }

                // Assign rowspan attribute if rowspan is greater than 1
                if ($rowspan > 1) {
                    if (!isset($element['attributes'])) {
                        $element['attributes'] = [];
                    }
                    $element['attributes']['rowspan'] = $rowspan;
                }
            }
            unset($element);
        }
        unset($row);

        // Remove merged elements after processing row spans
        foreach ($rows as &$row) {
            $elements = &$row['elements'];

            for ($index = count($elements) - 1; $index >= 0; --$index) {
                if (isset($elements[$index]['merged'])) {
                    array_splice($elements, $index, 1); // Remove merged element
                }
            }
        }
        unset($row);

        return $block; // Return the completed and modified table block
    }
}
