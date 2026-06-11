<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Configuration;

final class Configuration
{
    private array $booleanPaths;
    private array $schema;
    private array $features;
    private array $payload;

    public function __construct(array $booleanPaths, array $schema)
    {
        $this->booleanPaths = $booleanPaths;
        $this->schema = $schema;
    }

    public function bind(array &$features, array &$payload): void
    {
        $this->features = &$features;
        $this->payload  = &$payload;
    }

    /* ---------------------------- GET ----------------------- */
    public function get(string $path)
    {
        $path = $this->normalisePath($path);
        if (!isset($this->schema[$path])) {
            throw new \InvalidArgumentException("Invalid config path: {$path}");
        }
        if (isset($this->booleanPaths[$path])) {
            return $this->features[$path] ?? false;
        }
        return $this->payload[$path] ?? null;
    }

    /* ---------------------------- SET ----------------------- */
    public function set($path, $value = null): self
    {
        if (is_array($path)) {
            foreach ($path as $k => $v) {
                $this->set($k, $v);
            }
            return $this;
        }

        if (is_string($path) && is_array($value) && !isset($this->schema[$path])) {
            $prefix = $path . '.';
            $hasChild = false;
            foreach ($this->schema as $key => $_) {
                if (strpos($key, $prefix) === 0) {
                    $hasChild = true;
                    break;
                }
            }
            if ($hasChild) {
                foreach ($value as $k => $v) {
                    $this->set($prefix . $k, $v);
                }
                return $this;
            }
        }

        $path = $this->normalisePath($path);

        if (!isset($this->schema[$path])) {
            throw new \InvalidArgumentException("Invalid config path: {$path}");
        }
        $this->validate($value, $this->schema[$path]['type']);

        if (isset($this->booleanPaths[$path])) {
            $this->features[$path] = $value;
        } else {
            $this->payload[$path] = $value;
        }
        return $this;
    }

    public function export(): array
    {
        $flat = $this->payload;
        foreach ($this->booleanPaths as $p => $_) {
            $flat[$p] = $this->features[$p] ?? false;
        }
        return $flat;
    }

    /* -------------------- helpers --------------------------- */
    private function normalisePath(string $path): string
    {
        if (!isset($this->schema[$path]) && isset($this->schema[$path . '.enabled'])) {
            return $path . '.enabled';
        }
        return $path;
    }

    private function validate($value, string $expected): void
    {
        $actual = gettype($value);
        if ($expected !== $actual) {
            throw new \InvalidArgumentException("Expected {$expected}, got {$actual}");
        }
    }
}
