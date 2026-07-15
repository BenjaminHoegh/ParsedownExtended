<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Registry;

use BenjaminHoegh\ParsedownExtended\Extensions\Inline\EmojiExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\EmphasisExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\EscapeSequenceExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\FootnoteMarkerExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\InlineMathExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\InsertionsExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\KeystrokesExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\LinkExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\MarkingExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\SmartypantsExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\SubscriptExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\SuperscriptExtension;
use BenjaminHoegh\ParsedownExtended\Extensions\Inline\TypographerExtension;

/**
 * Inline extension behavior and definitions.
 */
trait InlineExtensions
{
    use EmphasisExtension;
    use EscapeSequenceExtension;
    use FootnoteMarkerExtension;
    use MarkingExtension;
    use InsertionsExtension;
    use KeystrokesExtension;
    use SuperscriptExtension;
    use SubscriptExtension;
    use InlineMathExtension;
    use EmojiExtension;
    use SmartypantsExtension;
    use TypographerExtension;
    use LinkExtension;

    /**
     * @return array<string, array{config: list<string>, markers?: list<string>}>
     */
    private function inlineExtensionDefinitions(): array
    {
        return [
            'Code' => ['config' => ['code', 'code.inline']],
            'Image' => ['config' => ['images']],
            'Markup' => ['config' => ['allow_raw_html']],
            'Link' => ['config' => ['links']],
            'Url' => ['config' => ['links']],
            'UrlTag' => ['config' => ['links']],
            'EmailTag' => ['config' => ['links', 'links.email_links']],
            'FootnoteMarker' => ['config' => ['footnotes']],
            'Emphasis' => ['config' => ['emphasis']],
            'Strikethrough' => ['config' => ['emphasis', 'emphasis.strikethroughs']],
            'Marking' => ['config' => ['emphasis', 'emphasis.mark'], 'markers' => ['=']],
            'Insertions' => ['config' => ['emphasis', 'emphasis.insertions'], 'markers' => ['+']],
            'Keystrokes' => ['config' => ['emphasis', 'emphasis.keystrokes'], 'markers' => ['[']],
            'MathNotation' => ['config' => ['math', 'math.inline'], 'markers' => ['\\', '$']],
            'Superscript' => ['config' => ['emphasis', 'emphasis.superscript'], 'markers' => ['^']],
            'Subscript' => ['config' => ['emphasis', 'emphasis.subscript'], 'markers' => ['~']],
            'Emojis' => ['config' => ['emojis'], 'markers' => [':']],
            'Smartypants' => [
                'config' => ['smartypants'],
                'markers' => ['<', '>', '-', '.', "'", '"', '`'],
            ],
            'Typographer' => [
                'config' => ['typographer'],
                'markers' => ['(', '.', '+', '!', '?'],
            ],
        ];
    }
}
