<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Registry;

/**
 * Coordinates the parser's built-in inline and block extensions.
 */
trait ExtensionRegistrar
{
    use InlineExtensions;
    use BlockExtensions;

    /** @var array<string, array<string, list<string>>> */
    private array $extensionConfigPaths = ['inline' => [], 'block' => []];

    /** @var array<string, array<string, bool>> */
    private array $extensionTypeEnabledCache = ['inline' => [], 'block' => []];

    private function registerExtensions(): void
    {
        $this->registerExtensionGroup('inline', $this->InlineTypes, $this->inlineExtensionDefinitions());
        $this->registerExtensionGroup('block', $this->BlockTypes, $this->blockExtensionDefinitions());
    }

    /**
     * @param 'inline'|'block' $group
     * @param array<string, list<string>> $types
     * @param array<string, array{config: list<string>, markers?: list<string>}> $definitions
     */
    private function registerExtensionGroup(string $group, array &$types, array $definitions): void
    {
        foreach ($definitions as $type => $definition) {
            $this->extensionConfigPaths[$group][$type] = $definition['config'];

            foreach ($definition['markers'] ?? [] as $marker) {
                $types[$marker] ??= [];

                if (!in_array($type, $types[$marker], true)) {
                    array_unshift($types[$marker], $type);
                }

                if (!in_array($marker, $this->specialCharacters, true)) {
                    $this->specialCharacters[] = $marker;
                }

                if ($group === 'inline' && !str_contains($this->inlineMarkerList, $marker)) {
                    $this->inlineMarkerList .= $marker;
                }
            }
        }

        $this->moveSpecialCharacterHandlerToEnd($types);
    }

    private function inlineTypeEnabled(string $inlineType): bool
    {
        return $this->extensionTypeEnabled('inline', $inlineType);
    }

    private function blockTypeEnabled(string $blockType): bool
    {
        return $this->extensionTypeEnabled('block', $blockType);
    }

    /**
     * @param 'inline'|'block' $group
     */
    private function extensionTypeEnabled(string $group, string $type): bool
    {
        if (!array_key_exists($type, $this->extensionTypeEnabledCache[$group])) {
            $configPaths = $this->extensionConfigPaths[$group][$type] ?? [];
            $this->extensionTypeEnabledCache[$group][$type] = $this->extensionConfigEnabled($configPaths);
        }

        return $this->extensionTypeEnabledCache[$group][$type];
    }

    private function clearExtensionEnabledCache(): void
    {
        $this->extensionTypeEnabledCache = ['inline' => [], 'block' => []];
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
     * @param array<string, list<string>> $types
     */
    private function moveSpecialCharacterHandlerToEnd(array &$types): void
    {
        foreach ($types as &$list) {
            $key = array_search('SpecialCharacter', $list, true);
            if ($key === false) {
                continue;
            }

            unset($list[$key]);
            $list[] = 'SpecialCharacter';
        }
        unset($list);
    }
}
