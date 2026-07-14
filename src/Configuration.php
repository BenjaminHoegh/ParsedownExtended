<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended;

/**
 * Validated, flat configuration storage for ParsedownExtended.
 *
 * Paths are resolved through a precompiled alias map. This keeps reads to two
 * array lookups while retaining validation for public get and set operations.
 */
final class Configuration
{
    /** @var array<string, array{type: string, default: mixed}> */
    private array $schema;

    /** @var array<string, string> */
    private array $aliases;

    /** @var array<string, mixed> */
    private array $values;

    /**
     * @param array<string, array{type: string, default: mixed}> $schema
     * @param array<string, string>                              $aliases
     * @param array<string, mixed>                               $defaults
     */
    public function __construct(array $schema, array $aliases, array $defaults)
    {
        $this->schema = $schema;
        $this->aliases = $aliases;
        $this->values = $defaults;

        // Branch aliases reference their canonical slot, so every caller reads
        // and writes one source of truth without resolving paths on each read.
        foreach ($this->aliases as $alias => $canonicalPath) {
            if ($alias !== $canonicalPath) {
                $this->values[$alias] = &$this->values[$canonicalPath];
            }
        }
    }

    /**
     * Return a configuration value using either its canonical path or branch alias.
     *
     * @return mixed
     */
    public function get(string $path)
    {
        if (!isset($this->values[$path]) && !array_key_exists($path, $this->values)) {
            throw new \InvalidArgumentException("Invalid config path: {$path}");
        }

        return $this->values[$path];
    }

    /**
     * Set one configuration value, a map of values, or the children of a branch.
     *
     * @param string|array<string, mixed> $path
     * @param mixed                       $value
     */
    public function set($path, $value = null): self
    {
        if (is_array($path)) {
            foreach ($path as $key => $item) {
                $this->set($key, $item);
            }

            return $this;
        }

        // A branch alias (for example "toc") may also receive a child map.
        // Canonical array-valued settings remain assignable as array leaves.
        if (is_string($path) && is_array($value) && !isset($this->schema[$path])) {
            $prefix = $path . '.';
            $hasChild = false;

            foreach ($this->aliases as $candidate => $_resolvedPath) {
                if (strpos($candidate, $prefix) === 0) {
                    $hasChild = true;
                    break;
                }
            }

            if ($hasChild) {
                foreach ($value as $key => $item) {
                    $this->set($prefix . $key, $item);
                }

                return $this;
            }
        }

        if (!is_string($path)) {
            throw new \InvalidArgumentException('Configuration paths must be strings.');
        }

        $resolvedPath = $this->resolvePath($path);
        $this->validate($value, $this->schema[$resolvedPath]['type']);
        $this->values[$resolvedPath] = $value;

        return $this;
    }

    /**
     * Export all configuration values using their canonical flat paths.
     *
     * @return array<string, mixed>
     */
    public function export(): array
    {
        $export = [];
        foreach ($this->schema as $path => $_definition) {
            $export[$path] = $this->values[$path];
        }

        return $export;
    }

    private function resolvePath(string $path): string
    {
        if (!isset($this->aliases[$path])) {
            throw new \InvalidArgumentException("Invalid config path: {$path}");
        }

        return $this->aliases[$path];
    }

    /** @param mixed $value */
    private function validate($value, string $expected): void
    {
        $actual = gettype($value);
        if ($expected !== $actual) {
            throw new \InvalidArgumentException("Expected {$expected}, got {$actual}");
        }
    }
}
