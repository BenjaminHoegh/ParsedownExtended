<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions;

trait InlineExtensions
{
    use Inline\InlineCodeExtension;
    use Inline\InlineImageExtension;
    use Inline\InlineMarkupExtension;
    use Inline\StrikethroughExtension;
    use Inline\EmphasisExtension;
    use Inline\EscapeSequenceExtension;
    use Inline\MarkingExtension;
    use Inline\InsertionsExtension;
    use Inline\KeystrokesExtension;
    use Inline\SuperscriptExtension;
    use Inline\SubscriptExtension;
    use Inline\InlineMathExtension;
    use Inline\EmojiExtension;
    use Inline\SmartypantsExtension;
    use Inline\TypographerExtension;
    use Inline\LinkExtension;

    /** @var array<int,array{markers:string|array<int,string>,type:string}> */
    private const INLINE_TYPE_DEFINITIONS = [
        ['markers' => '=', 'type' => 'Marking'],
        ['markers' => '+', 'type' => 'Insertions'],
        ['markers' => '[', 'type' => 'Keystrokes'],
        ['markers' => ['\\', '$'], 'type' => 'MathNotation'],
        ['markers' => '^', 'type' => 'Superscript'],
        ['markers' => '~', 'type' => 'Subscript'],
        ['markers' => ':', 'type' => 'Emojis'],
        ['markers' => ['<', '>', '-', '.', "'", '"', '`'], 'type' => 'Smartypants'],
        ['markers' => ['(', '.', '+', '!', '?'], 'type' => 'Typographer'],
    ];

    /**
     * Returns inline handlers that require marker registration.
     *
     * @return array<int,array{markers:string|array<int,string>,type:string}>
     */
    protected function getInlineExtensionDefinitions(): array
    {
        return self::INLINE_TYPE_DEFINITIONS;
    }
}
