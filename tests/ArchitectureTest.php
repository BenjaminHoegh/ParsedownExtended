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

    public function testExtensionFilesRespectDeclaredRootOwnership(): void
    {
        $allowedRootsByFile = [
            'src/Extensions/Inline/EmphasisExtension.php' => ['emphasis'],
            'src/Extensions/Inline/EscapeSequenceExtension.php' => ['math'],
            'src/Extensions/Inline/InlineCodeExtension.php' => ['code'],
            'src/Extensions/Inline/InlineImageExtension.php' => ['images'],
            'src/Extensions/Inline/InlineMarkupExtension.php' => ['allow_raw_html'],
            'src/Extensions/Inline/InlineMathExtension.php' => ['math'],
            'src/Extensions/Inline/InsertionsExtension.php' => ['emphasis'],
            'src/Extensions/Inline/KeystrokesExtension.php' => ['emphasis'],
            'src/Extensions/Inline/LinkExtension.php' => ['links'],
            'src/Extensions/Inline/MarkingExtension.php' => ['emphasis'],
            'src/Extensions/Inline/EmojiExtension.php' => ['emojis'],
            'src/Extensions/Inline/SmartypantsExtension.php' => ['smartypants'],
            'src/Extensions/Inline/StrikethroughExtension.php' => ['emphasis'],
            'src/Extensions/Inline/SubscriptExtension.php' => ['emphasis'],
            'src/Extensions/Inline/SuperscriptExtension.php' => ['emphasis'],
            'src/Extensions/Inline/TypographerExtension.php' => ['typographer'],

            'src/Extensions/Block/AbbreviationExtension.php' => ['abbreviations'],
            'src/Extensions/Block/AlertExtension.php' => ['alerts'],
            'src/Extensions/Block/BlockCodeExtension.php' => ['code'],
            'src/Extensions/Block/BlockMarkupExtension.php' => ['allow_raw_html'],
            'src/Extensions/Block/BlockMathExtension.php' => ['math'],
            'src/Extensions/Block/CommentExtension.php' => ['comments'],
            'src/Extensions/Block/DefinitionListExtension.php' => ['definition_lists'],
            'src/Extensions/Block/DiagramExtension.php' => ['code', 'diagrams'],
            'src/Extensions/Block/FootnoteExtension.php' => ['footnotes'],
            'src/Extensions/Block/HeadingExtension.php' => ['headings', 'toc'],
            'src/Extensions/Block/ListExtension.php' => ['lists'],
            'src/Extensions/Block/QuoteExtension.php' => ['quotes'],
            'src/Extensions/Block/ReferenceExtension.php' => ['references'],
            'src/Extensions/Block/RuleExtension.php' => ['thematic_breaks'],
            'src/Extensions/Block/TableExtension.php' => ['tables'],
            'src/Extensions/Block/TableSpanExtension.php' => ['tables'],
            'src/Extensions/Block/TaskListExtension.php' => ['lists'],
        ];

        foreach ($allowedRootsByFile as $relativePath => $allowedRoots) {
            $filePath = __DIR__ . '/../' . $relativePath;
            $contents = file_get_contents($filePath);
            if ($contents === false) {
                continue;
            }

            preg_match_all("/configEnabled\('([^']+)'\)/", $contents, $matches);
            $actualRoots = [];
            foreach ($matches[1] as $configPath) {
                $actualRoots[] = explode('.', $configPath, 2)[0];
            }

            $actualRoots = array_values(array_unique($actualRoots));
            sort($actualRoots);

            $expectedRoots = array_values(array_unique($allowedRoots));
            sort($expectedRoots);

            $this->assertSame(
                $expectedRoots,
                $actualRoots,
                'Unexpected config root usage in ' . $relativePath
            );
        }
    }
}
