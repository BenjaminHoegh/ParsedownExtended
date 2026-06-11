<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Concerns;

trait RegistersBlockTypes
{
    /**
     * Registers all custom block parsers for the extended syntax.
     *
     * @return void
     */
    private function registerCustomBlockTypes(): void
    {
        $this->addBlockType(['\\', '$'], 'MathNotation');
        $this->addBlockType('>', 'Alert');
    }

    /**
     * Registers a block type marker with a corresponding handler function.
     *
     * This function ensures that a given marker is registered for block parsing, associating it with
     * a handler function that will handle the block behavior for that marker.
     *
     * @since 1.1.2
     *
     * @param mixed $markers One or more markers to register (can be a string or an array).
     * @param string $funcName The name of the handler function associated with the marker(s).
     * @return void
     */
    private function addBlockType($markers, string $funcName): void
    {
        // Ensure $markers is always an array, even if a single marker is passed as a string
        $markers = (array) $markers;

        foreach ($markers as $marker) {
            // If the marker is not already registered, initialize it
            if (!isset($this->BlockTypes[$marker])) {
                $this->BlockTypes[$marker] = [];
            }

            // Add the marker to the special characters array if it's not already present
            if (!in_array($marker, $this->specialCharacters, true)) {
                $this->specialCharacters[] = $marker;
            }

            // Add the function to the front while keeping a single instance in the handler chain.
            $handlerIndex = array_search($funcName, $this->BlockTypes[$marker], true);
            if ($handlerIndex !== false) {
                unset($this->BlockTypes[$marker][$handlerIndex]);
            }
            array_unshift($this->BlockTypes[$marker], $funcName);
        }
    }
}
