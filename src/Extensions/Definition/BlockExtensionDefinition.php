<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Definition;

final class BlockExtensionDefinition
{
    private string $type;
    private array $markers;
    private array $configPaths;
    private int $priority;

    /**
     * @param list<string> $markers
     * @param list<string> $configPaths
     */
    public function __construct(string $type, array $markers = [], array $configPaths = [], int $priority = 100)
    {
        $this->type = $type;
        $this->markers = $markers;
        $this->configPaths = $configPaths;
        $this->priority = $priority;
    }

    /**
     * @param list<string> $configPaths
     */
    public static function core(string $type, array $configPaths = []): self
    {
        return new self($type, [], $configPaths);
    }

    /**
     * @param list<string> $markers
     * @param list<string> $configPaths
     */
    public static function custom(string $type, array $markers, array $configPaths = [], int $priority = 100): self
    {
        return new self($type, $markers, $configPaths, $priority);
    }

    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return list<string>
     */
    public function markers(): array
    {
        return $this->markers;
    }

    /**
     * @return list<string>
     */
    public function configPaths(): array
    {
        return $this->configPaths;
    }

    public function priority(): int
    {
        return $this->priority;
    }
}
