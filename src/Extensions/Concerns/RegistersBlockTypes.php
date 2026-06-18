<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Concerns;

use BenjaminHoegh\ParsedownExtended\Extensions\Definition\ExtensionDefinitions;

trait RegistersBlockTypes
{
    /**
     * Registers all custom block parsers for the extended syntax.
     *
     * @return void
     */
    private function registerCustomBlockTypes(): void
    {
        foreach ($this->customBlockExtensionDefinitions() as $definition) {
            $this->registerBlockExtension(
                $definition->markers(),
                $definition->type(),
                $definition->configPaths(),
                $definition->priority()
            );
        }
    }

    /**
     * @return list<\BenjaminHoegh\ParsedownExtended\Extensions\Definition\BlockExtensionDefinition>
     */
    private function customBlockExtensionDefinitions(): array
    {
        return ExtensionDefinitions::customBlock();
    }

    /**
     * Registers a block type marker with a corresponding handler.
     *
     * Higher priority handlers run before lower priority handlers for the same marker.
     *
     * @param mixed $markers One or more single-character markers.
     * @param list<string> $configPaths Boolean config paths that must be enabled before the handler runs.
     * @return $this
     */
    public function registerBlockExtension($markers, string $type, array $configPaths = [], int $priority = 100): self
    {
        $this->assertExtensionHandlerExists('block', $type);
        $markers = $this->normalizeExtensionMarkers($markers);
        $this->registerBlockExtensionMetadata($type, $configPaths);

        foreach ($markers as $marker) {
            if (!isset($this->BlockTypes[$marker])) {
                $this->BlockTypes[$marker] = [];
            }

            $this->seedExtensionTypeOrder($marker, $this->BlockTypes, $this->blockTypePriorities, $this->blockTypeOrder);

            if (!in_array($marker, $this->specialCharacters, true)) {
                $this->specialCharacters[] = $marker;
            }

            $handlerIndex = array_search($type, $this->BlockTypes[$marker], true);
            if ($handlerIndex !== false) {
                unset($this->BlockTypes[$marker][$handlerIndex]);
            }

            $this->BlockTypes[$marker][] = $type;
            $this->blockTypePriorities[$marker][$type] = $priority;
            $this->blockTypeOrder[$marker][$type] = $this->blockTypeOrder[$marker][$type]
                ?? ++$this->extensionRegistrationOrder;

            $this->BlockTypes[$marker] = $this->sortExtensionTypes(
                array_values($this->BlockTypes[$marker]),
                $this->blockTypePriorities[$marker],
                $this->blockTypeOrder[$marker]
            );
        }

        $this->configurationChanged();

        return $this;
    }

}
