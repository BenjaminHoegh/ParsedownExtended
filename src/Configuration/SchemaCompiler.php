<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Configuration;

final class SchemaCompiler
{
    public function compile(array $schema): array
    {
        $booleanPaths = [];
        $flatSchema = [];
        $defaultFeatures = [];
        $defaultPayload = [];

        $this->walk(
            $schema,
            '',
            $booleanPaths,
            $flatSchema,
            $defaultFeatures,
            $defaultPayload
        );

        return [
            'booleanPaths' => $booleanPaths,
            'flatSchema' => $flatSchema,
            'defaultFeatures' => $defaultFeatures,
            'defaultPayload' => $defaultPayload,
        ];
    }

    private function walk(
        array $node,
        string $prefix,
        array &$booleanPaths,
        array &$flatSchema,
        array &$defaultFeatures,
        array &$defaultPayload
    ): void {
        foreach ($node as $k => $v) {
            $path = $prefix === '' ? $k : $prefix . '.' . $k;

            // branch (associative => object)
            if (is_array($v) && $v !== [] && array_keys($v) !== range(0, count($v) - 1)) {
                // implicit enabled=true unless provided
                $enabledDefault = true;
                if (array_key_exists('enabled', $v)) {
                    $enabledDefault = (bool)$v['enabled'];
                }
                $this->registerBoolean("{$path}.enabled", $enabledDefault, $booleanPaths, $flatSchema, $defaultFeatures);
                if (array_key_exists('enabled', $v)) {
                    unset($v['enabled']); // don't recurse into it
                }
                $this->walk($v, $path, $booleanPaths, $flatSchema, $defaultFeatures, $defaultPayload);
                continue;
            }

            // leaf boolean
            if (is_bool($v)) {
                $this->registerBoolean($path, $v, $booleanPaths, $flatSchema, $defaultFeatures);
                continue;
            }

            // leaf non-boolean (string, int, array, ...)
            $this->registerPayload($path, $v, $flatSchema, $defaultPayload);
        }
    }

    private function registerBoolean(
        string $path,
        bool $default,
        array &$booleanPaths,
        array &$flatSchema,
        array &$defaultFeatures
    ): void {
        $booleanPaths[$path] = true;
        $flatSchema[$path] = ['type' => 'boolean', 'default' => $default];
        $defaultFeatures[$path] = $default;
    }

    private function registerPayload(
        string $path,
        $default,
        array &$flatSchema,
        array &$defaultPayload
    ): void {
        $flatSchema[$path] = ['type' => gettype($default), 'default' => $default];
        $defaultPayload[$path] = $default;
    }
}
