<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions;

trait BlockExtensions
{
    use Block\FootnoteExtension;
    use Block\DefinitionListExtension;
    use Block\BlockCodeExtension;
    use Block\CommentExtension;
    use Block\ListExtension;
    use Block\QuoteExtension;
    use Block\RuleExtension;
    use Block\BlockMarkupExtension;
    use Block\ReferenceExtension;
    use Block\TableExtension;
    use Block\AbbreviationExtension;
    use Block\AlertExtension;
    use Block\BlockMathExtension;
    use Block\DiagramExtension;
    use Block\TaskListExtension;
    use Block\TableSpanExtension;
    use Block\HeadingExtension;

    /** @var array<int,array{markers:string|array<int,string>,type:string}> */
    private const BLOCK_TYPE_DEFINITIONS = [
        ['markers' => ['\\', '$'], 'type' => 'MathNotation'],
        ['markers' => '>', 'type' => 'Alert'],
    ];

    /**
     * Returns block handlers that require marker registration.
     *
     * @return array<int,array{markers:string|array<int,string>,type:string}>
     */
    protected function getBlockExtensionDefinitions(): array
    {
        return self::BLOCK_TYPE_DEFINITIONS;
    }
}
