<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Registry;

use BenjaminHoegh\ParsedownExtended\Extensions\Definition\BlockExtensionDefinition;
use BenjaminHoegh\ParsedownExtended\Extensions\Definition\ExtensionDefinitions;
use BenjaminHoegh\ParsedownExtended\Extensions\Definition\InlineExtensionDefinition;

trait ExtensionRegistrar
{
    /** @var array<string, list<string>> */
    private array $inlineExtensionConfigPaths = [];

    /** @var array<string, list<string>> */
    private array $blockExtensionConfigPaths = [];

    /** @var array<string, array<string, int>> */
    private array $inlineTypePriorities = [];

    /** @var array<string, array<string, int>> */
    private array $blockTypePriorities = [];

    /** @var array<string, array<string, int>> */
    private array $inlineTypeOrder = [];

    /** @var array<string, array<string, int>> */
    private array $blockTypeOrder = [];

    /** @var array<string, bool> */
    private array $inlineTypeEnabledCache = [];

    /** @var array<string, bool> */
    private array $blockTypeEnabledCache = [];

    private int $extensionRegistrationOrder = 0;

    private function registerExtensions(): void
    {
        $this->registerCoreExtensionMetadata();
        $this->registerCustomInlineTypes();
        $this->registerCustomBlockTypes();
        $this->moveSpecialCharacterHandlerToEnd($this->InlineTypes);
        $this->moveSpecialCharacterHandlerToEnd($this->BlockTypes);
    }

    private function registerCoreExtensionMetadata(): void
    {
        foreach ($this->coreInlineExtensionDefinitions() as $definition) {
            $this->registerInlineExtensionMetadata($definition->type(), $definition->configPaths());
        }

        foreach ($this->coreBlockExtensionDefinitions() as $definition) {
            $this->registerBlockExtensionMetadata($definition->type(), $definition->configPaths());
        }
    }

    /**
     * @return list<InlineExtensionDefinition>
     */
    private function coreInlineExtensionDefinitions(): array
    {
        return ExtensionDefinitions::coreInline();
    }

    /**
     * @return list<BlockExtensionDefinition>
     */
    private function coreBlockExtensionDefinitions(): array
    {
        return ExtensionDefinitions::coreBlock();
    }

    /**
     * @param list<string> $configPaths
     */
    private function registerInlineExtensionMetadata(string $type, array $configPaths): void
    {
        $this->inlineExtensionConfigPaths[$type] = $configPaths;
        unset($this->inlineTypeEnabledCache[$type]);
    }

    /**
     * @param list<string> $configPaths
     */
    private function registerBlockExtensionMetadata(string $type, array $configPaths): void
    {
        $this->blockExtensionConfigPaths[$type] = $configPaths;
        unset($this->blockTypeEnabledCache[$type]);
    }

    private function inlineTypeEnabled(string $inlineType): bool
    {
        if (array_key_exists($inlineType, $this->inlineTypeEnabledCache)) {
            return $this->inlineTypeEnabledCache[$inlineType];
        }

        return $this->inlineTypeEnabledCache[$inlineType] = $this->extensionConfigEnabled($this->inlineExtensionConfigPaths[$inlineType] ?? []);
    }

    private function blockTypeEnabled(string $blockType): bool
    {
        if (array_key_exists($blockType, $this->blockTypeEnabledCache)) {
            return $this->blockTypeEnabledCache[$blockType];
        }

        return $this->blockTypeEnabledCache[$blockType] = $this->extensionConfigEnabled($this->blockExtensionConfigPaths[$blockType] ?? []);
    }

    private function clearExtensionEnabledCache(): void
    {
        $this->inlineTypeEnabledCache = [];
        $this->blockTypeEnabledCache = [];
    }

    /**
     * @param list<string> $configPaths
     */
    private function extensionConfigEnabled(array $configPaths): bool
    {
        foreach ($configPaths as $configPath) {
            if (!$this->configEnabled($configPath)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param mixed $markers
     * @return list<string>
     */
    private function normalizeExtensionMarkers($markers): array
    {
        $normalized = [];

        foreach ((array) $markers as $marker) {
            if (!is_string($marker) || strlen($marker) !== 1) {
                throw new \InvalidArgumentException('Extension markers must be single-character strings.');
            }

            if (!in_array($marker, $normalized, true)) {
                $normalized[] = $marker;
            }
        }

        if ($normalized === []) {
            throw new \InvalidArgumentException('At least one extension marker is required.');
        }

        return $normalized;
    }

    private function assertExtensionHandlerExists(string $kind, string $type): void
    {
        $method = $kind . $type;

        if (!method_exists($this, $method)) {
            throw new \InvalidArgumentException("Missing extension handler: {$method}");
        }
    }

    /**
     * @param array<string, list<string>> $typeMap
     * @param array<string, array<string, int>> $priorities
     * @param array<string, array<string, int>> $orders
     */
    private function seedExtensionTypeOrder(string $marker, array $typeMap, array &$priorities, array &$orders): void
    {
        if (!isset($typeMap[$marker])) {
            return;
        }

        foreach ($typeMap[$marker] as $type) {
            if (!isset($priorities[$marker][$type])) {
                $priorities[$marker][$type] = 0;
            }

            if (!isset($orders[$marker][$type])) {
                $orders[$marker][$type] = ++$this->extensionRegistrationOrder;
            }
        }
    }

    /**
     * @param list<string> $types
     * @param array<string, int> $priorities
     * @param array<string, int> $orders
     * @return list<string>
     */
    private function sortExtensionTypes(array $types, array $priorities, array $orders): array
    {
        usort($types, static function (string $left, string $right) use ($priorities, $orders): int {
            $leftPriority = $priorities[$left] ?? 0;
            $rightPriority = $priorities[$right] ?? 0;

            if ($leftPriority !== $rightPriority) {
                return $rightPriority <=> $leftPriority;
            }

            return ($orders[$left] ?? 0) <=> ($orders[$right] ?? 0);
        });

        return $types;
    }
}
