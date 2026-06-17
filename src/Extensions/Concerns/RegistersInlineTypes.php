<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Concerns;

use BenjaminHoegh\ParsedownExtended\Extensions\ExtensionDefinitions;

trait RegistersInlineTypes
{
    /**
     * Registers all custom inline parsers for the extended syntax.
     *
     * @return void
     */
    private function registerCustomInlineTypes(): void
    {
        foreach ($this->customInlineExtensionDefinitions() as $definition) {
            $this->registerInlineExtension(
                $definition->markers(),
                $definition->type(),
                $definition->configPaths(),
                $definition->priority()
            );
        }
    }

    /**
     * @return list<\BenjaminHoegh\ParsedownExtended\Extensions\InlineExtensionDefinition>
     */
    private function customInlineExtensionDefinitions(): array
    {
        return ExtensionDefinitions::customInline();
    }

    /**
     * Registers an inline type marker with a corresponding handler.
     *
     * Higher priority handlers run before lower priority handlers for the same marker.
     *
     * @param mixed $markers One or more single-character markers.
     * @param list<string> $configPaths Boolean config paths that must be enabled before the handler runs.
     * @return $this
     */
    public function registerInlineExtension($markers, string $type, array $configPaths = [], int $priority = 100): self
    {
        $this->assertExtensionHandlerExists('inline', $type);
        $markers = $this->normalizeExtensionMarkers($markers);
        $this->registerInlineExtensionMetadata($type, $configPaths);

        foreach ($markers as $marker) {
            if (!isset($this->InlineTypes[$marker])) {
                $this->InlineTypes[$marker] = [];
            }

            $this->seedExtensionTypeOrder($marker, $this->InlineTypes, $this->inlineTypePriorities, $this->inlineTypeOrder);

            if (!in_array($marker, $this->specialCharacters, true)) {
                $this->specialCharacters[] = $marker;
            }

            $handlerIndex = array_search($type, $this->InlineTypes[$marker], true);
            if ($handlerIndex !== false) {
                unset($this->InlineTypes[$marker][$handlerIndex]);
            }

            $this->InlineTypes[$marker][] = $type;
            $this->inlineTypePriorities[$marker][$type] = $priority;
            $this->inlineTypeOrder[$marker][$type] = $this->inlineTypeOrder[$marker][$type]
                ?? ++$this->extensionRegistrationOrder;

            $this->InlineTypes[$marker] = $this->sortExtensionTypes(
                array_values($this->InlineTypes[$marker]),
                $this->inlineTypePriorities[$marker],
                $this->inlineTypeOrder[$marker]
            );

            if (strpos($this->inlineMarkerList, $marker) === false) {
                $this->inlineMarkerList .= $marker;
            }
        }

        $this->configurationChanged();

        return $this;
    }

    /**
     * @param mixed $markers
     */
    protected function addInlineType($markers, string $funcName): void
    {
        $this->registerInlineExtension($markers, $funcName);
    }
}
