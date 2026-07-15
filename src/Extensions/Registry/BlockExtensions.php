<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Registry;

use BenjaminHoegh\ParsedownExtended\Extensions\Block\AbbreviationExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\AlertExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\BlockMathExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\DiagramExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\HeadingAnchorExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\HeadingExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\ReferenceExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\TableSpanExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Block\TaskListExtension;

/**
 * Block extension behavior and definitions.
 */
trait BlockExtensions
{
    use ReferenceExtension;
    use AbbreviationExtension;
    use AlertExtension;
    use BlockMathExtension;
    use DiagramExtension;
    use TaskListExtension;
    use TableSpanExtension;
    use HeadingExtension;
    use HeadingAnchorExtension;

    /**
     * @return array<string, array{config: list<string>, markers?: list<string>}>
     */
    private function blockExtensionDefinitions(): array
    {
        return [
            'Code' => ['config' => ['code', 'code.blocks']],
            'FencedCode' => ['config' => ['code', 'code.blocks']],
            'Header' => ['config' => ['headings']],
            'SetextHeader' => ['config' => ['headings']],
            'Rule' => ['config' => ['thematic_breaks']],
            'List' => ['config' => ['lists']],
            'Table' => ['config' => ['tables']],
            'Comment' => ['config' => ['comments']],
            'Markup' => ['config' => ['allow_raw_html']],
            'Quote' => ['config' => ['quotes']],
            'Reference' => ['config' => ['references']],
            'DefinitionList' => ['config' => ['definition_lists']],
            'Abbreviation' => ['config' => ['abbreviations']],
            'Footnote' => ['config' => ['footnotes']],
            'MathNotation' => ['config' => ['math', 'math.block'], 'markers' => ['\\', '$']],
            'Alert' => ['config' => ['alerts'], 'markers' => ['>']],
        ];
    }
}
