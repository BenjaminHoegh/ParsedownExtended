<?php

declare(strict_types=1);

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

final class ArchitectureTest extends TestCase
{
    public function testExtensionConfigRootsExistInSchema(): void
    {
        $parser = new ParsedownExtended();

        $schemaRoots = [];
        foreach (array_keys($parser->getFlatSchema()) as $path) {
            $root = explode('.', $path, 2)[0];
            $schemaRoots[$root] = true;
        }

        $unknownRoots = [];
        $extensionFiles = array_merge(
            glob(__DIR__ . '/../src/Extensions/Inline/*Extension.php') ?: [],
            glob(__DIR__ . '/../src/Extensions/Block/*Extension.php') ?: []
        );

        foreach ($extensionFiles as $filePath) {
            $contents = file_get_contents($filePath);
            if ($contents === false) {
                continue;
            }

            if (!preg_match_all("/configEnabled\('([^']+)'\)/", $contents, $matches)) {
                continue;
            }

            foreach ($matches[1] as $configPath) {
                $root = explode('.', $configPath, 2)[0];
                if (!isset($schemaRoots[$root])) {
                    $unknownRoots[$root] = true;
                }
            }
        }

        $this->assertSame(
            [],
            array_keys($unknownRoots),
            'Found extension config roots used in handlers but missing from schema: ' . implode(', ', array_keys($unknownRoots))
        );
    }
}
