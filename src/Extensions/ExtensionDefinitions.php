<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions;

final class ExtensionDefinitions
{
    /**
     * @return list<InlineExtensionDefinition>
     */
    public static function coreInline(): array
    {
        return [
            InlineExtensionDefinition::core('Code', ['code', 'code.inline']),
            InlineExtensionDefinition::core('Image', ['images']),
            InlineExtensionDefinition::core('Markup', ['allow_raw_html']),
            InlineExtensionDefinition::core('Link', ['links']),
            InlineExtensionDefinition::core('Url', ['links']),
            InlineExtensionDefinition::core('UrlTag', ['links']),
            InlineExtensionDefinition::core('EmailTag', ['links', 'links.email_links']),
            InlineExtensionDefinition::core('FootnoteMarker', ['footnotes']),
            InlineExtensionDefinition::core('Emphasis', ['emphasis']),
            InlineExtensionDefinition::core('Strikethrough', ['emphasis', 'emphasis.strikethroughs']),
        ];
    }

    /**
     * @return list<BlockExtensionDefinition>
     */
    public static function coreBlock(): array
    {
        return [
            BlockExtensionDefinition::core('Code', ['code', 'code.blocks']),
            BlockExtensionDefinition::core('FencedCode', ['code', 'code.blocks']),
            BlockExtensionDefinition::core('Header', ['headings']),
            BlockExtensionDefinition::core('SetextHeader', ['headings']),
            BlockExtensionDefinition::core('Rule', ['thematic_breaks']),
            BlockExtensionDefinition::core('List', ['lists']),
            BlockExtensionDefinition::core('Table', ['tables']),
            BlockExtensionDefinition::core('Comment', ['comments']),
            BlockExtensionDefinition::core('Markup', ['allow_raw_html']),
            BlockExtensionDefinition::core('Quote', ['quotes']),
            BlockExtensionDefinition::core('Reference', ['references']),
            BlockExtensionDefinition::core('DefinitionList', ['definition_lists']),
            BlockExtensionDefinition::core('Abbreviation', ['abbreviations']),
            BlockExtensionDefinition::core('Footnote', ['footnotes']),
        ];
    }

    /**
     * @return list<InlineExtensionDefinition>
     */
    public static function customInline(): array
    {
        return [
            InlineExtensionDefinition::custom('Marking', ['='], ['emphasis', 'emphasis.mark']),
            InlineExtensionDefinition::custom('Insertions', ['+'], ['emphasis', 'emphasis.insertions']),
            InlineExtensionDefinition::custom('Keystrokes', ['['], ['emphasis', 'emphasis.keystrokes']),
            InlineExtensionDefinition::custom('MathNotation', ['\\', '$'], ['math', 'math.inline']),
            InlineExtensionDefinition::custom('Superscript', ['^'], ['emphasis', 'emphasis.superscript']),
            InlineExtensionDefinition::custom('Subscript', ['~'], ['emphasis', 'emphasis.subscript']),
            InlineExtensionDefinition::custom('Emojis', [':'], ['emojis']),
            InlineExtensionDefinition::custom('Smartypants', ['<', '>', '-', '.', "'", '"', '`'], ['smartypants']),
            InlineExtensionDefinition::custom('Typographer', ['(', '.', '+', '!', '?'], ['typographer'], 110),
        ];
    }

    /**
     * @return list<BlockExtensionDefinition>
     */
    public static function customBlock(): array
    {
        return [
            BlockExtensionDefinition::custom('MathNotation', ['\\', '$'], ['math', 'math.block']),
            BlockExtensionDefinition::custom('Alert', ['>'], ['alerts'], 110),
        ];
    }
}
