<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Concerns;

trait RegistersInlineTypes
{
    /**
     * Registers all custom inline parsers for the extended syntax.
     *
     * @return void
     */
    private function registerCustomInlineTypes(): void
    {
        $this->addInlineType('=', 'Marking');
        $this->addInlineType('+', 'Insertions');
        $this->addInlineType('[', 'Keystrokes');
        $this->addInlineType(['\\', '$'], 'MathNotation');
        $this->addInlineType('^', 'Superscript');
        $this->addInlineType('~', 'Subscript');
        $this->addInlineType(':', 'Emojis');
        $this->addInlineType(['<', '>', '-', '.', "'", '"', '`'], 'Smartypants');
        $this->addInlineType(['(', '.', '+', '!', '?'], 'Typographer');
    }

    /**
     * Registers an inline type marker with a corresponding handler function.
     *
     * This function ensures that a given marker is registered for inline parsing, associating it with
     * a handler function that will handle the inline behavior for that marker.
     *
     * @since 1.1.2
     *
     * @param mixed $markers One or more markers to register (can be a string or an array).
     * @param string $funcName The name of the handler function associated with the marker(s).
     * @return void
     */
    private function addInlineType($markers, string $funcName): void
    {
        // Ensure $markers is always an array, even if a single marker is passed as a string
        $markers = (array) $markers;

        foreach ($markers as $marker) {
            // If the marker is not already registered, initialize it
            if (!isset($this->InlineTypes[$marker])) {
                $this->InlineTypes[$marker] = [];
            }

            // Add the marker to the special characters array if it's not already present
            if (!in_array($marker, $this->specialCharacters, true)) {
                $this->specialCharacters[] = $marker;
            }

            // Add the function to the front while keeping a single instance in the handler chain.
            $handlerIndex = array_search($funcName, $this->InlineTypes[$marker], true);
            if ($handlerIndex !== false) {
                unset($this->InlineTypes[$marker][$handlerIndex]);
            }
            array_unshift($this->InlineTypes[$marker], $funcName);

            // Keep a unique marker list for strpbrk scanning.
            if (strpos($this->inlineMarkerList, $marker) === false) {
                $this->inlineMarkerList .= $marker;
            }
        }
    }
}
