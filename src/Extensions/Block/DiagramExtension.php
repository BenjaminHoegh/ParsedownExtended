<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Block;

trait DiagramExtension
{
    /**
     * Processes fenced code blocks with special handling for extensions like Mermaid and Chart.js.
     *
     * This function extends the standard fenced code block parsing to handle additional languages that may
     * require specific rendering, such as diagrams (e.g., Mermaid, Chart.js). The type of element rendered depends
     * on the specified language, and different HTML elements may be used based on the context.
     *
     * @since 0.1.0
     *
     * @param array $Line The line being processed for a fenced code block.
     * @return array|null The parsed code block or diagram block if applicable, otherwise null.
     */
    protected function blockFencedCode($Line)
    {
        // Check if code block parsing is enabled in the configuration settings
        if (!$this->configEnabled('code') || !$this->configEnabled('code.blocks')) {
            return null; // Return null if code block parsing is disabled
        }

        // Use the parent class to parse the fenced code block
        $Block = parent::blockFencedCode($Line);
        if (!$Block || !isset($Line['text'][0])) {
            return $Block;
        }

        // Check if diagram support is enabled in the configuration
        if (!$this->configEnabled('diagrams')) {
            return $Block; // Return the standard code block if diagrams are disabled
        }

        $marker = $Line['text'][0]; // Identify the marker character (e.g., backticks)
        $openerLength = strspn($Line['text'], $marker); // Determine the length of the opening markers

        // Extract the language identifier from the fenced code line
        $parts = explode(' ', trim(substr($Line['text'], $openerLength)), 2);
        $language = strtolower($parts[0] ?? ''); // Convert the language identifier to lowercase

        // Define custom handlers for specific code block extensions like Mermaid and Chart.js
        $extensions = [
            'mermaid' => ['div', 'mermaid', 'diagrams.mermaid'], // Mermaid diagrams rendered inside a <div> with class "mermaid"
            'chart' => ['canvas', 'chartjs', 'diagrams.chartjs'], // Chart.js diagrams rendered inside a <canvas> with class "chartjs"
            'chartjs' => ['canvas', 'chartjs', 'diagrams.chartjs'],
            // Additional languages can be added here as needed
        ];

        // If the specified language matches one of the configured extensions, customize the element
        if (isset($extensions[$language])) {
            [$elementName, $class, $diagramConfigPath] = $extensions[$language]; // Extract element details and the feature flag path

            if (!$this->configEnabled($diagramConfigPath)) {
                return $Block;
            }

            return [
                'char' => $marker, // Store the marker character
                'openerLength' => $openerLength, // Store the length of the opener
                'element' => [
                    'name' => $elementName, // Set the element name (e.g., 'div', 'canvas')
                    'element' => [
                        'text' => '', // Placeholder for content
                    ],
                    'attributes' => [
                        'class' => $class, // Add the class for styling (e.g., 'mermaid', 'chartjs')
                    ],
                ],
            ];
        }

        // Return the standard code block if no special handling is needed
        return $Block;
    }
}
