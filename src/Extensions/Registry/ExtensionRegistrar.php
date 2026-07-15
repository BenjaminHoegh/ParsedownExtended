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
 * The parser's built-in extensions and their config switches.
 */
trait ExtensionRegistrar
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

    /** @var array<string, list<string>> */
    private array $inlineExtensionConfigPaths = [];

    /** @var array<string, list<string>> */
    private array $blockExtensionConfigPaths = [];

    /** @var array<string, bool> */
    private array $inlineTypeEnabledCache = [];

    /** @var array<string, bool> */
    private array $blockTypeEnabledCache = [];

    private function registerExtensions(): void
    {
        $this->inlineExtensionConfigPaths = $this->coreInlineExtensions();
        $this->blockExtensionConfigPaths = $this->coreBlockExtensions();

        foreach ($this->customInlineExtensions() as $extension) {
            $this->addInlineExtension($extension['markers'], $extension['type'], $extension['config']);
        }

        foreach ($this->customBlockExtensions() as $extension) {
            $this->addBlockExtension($extension['markers'], $extension['type'], $extension['config']);
        }

        $this->moveSpecialCharacterHandlerToEnd($this->InlineTypes);
        $this->moveSpecialCharacterHandlerToEnd($this->BlockTypes);
    }

    /**
     * @param list<string> $markers
     * @param list<string> $configPaths
     */
    private function addInlineExtension(array $markers, string $type, array $configPaths): void
    {
        $this->inlineExtensionConfigPaths[$type] = $configPaths;

        foreach ($markers as $marker) {
            $this->InlineTypes[$marker] ??= [];

            if (!in_array($type, $this->InlineTypes[$marker], true)) {
                array_unshift($this->InlineTypes[$marker], $type);
            }

            if (!in_array($marker, $this->specialCharacters, true)) {
                $this->specialCharacters[] = $marker;
            }

            if (strpos($this->inlineMarkerList, $marker) === false) {
                $this->inlineMarkerList .= $marker;
            }
        }
    }

    /**
     * @param list<string> $markers
     * @param list<string> $configPaths
     */
    private function addBlockExtension(array $markers, string $type, array $configPaths): void
    {
        $this->blockExtensionConfigPaths[$type] = $configPaths;

        foreach ($markers as $marker) {
            $this->BlockTypes[$marker] ??= [];

            if (!in_array($type, $this->BlockTypes[$marker], true)) {
                array_unshift($this->BlockTypes[$marker], $type);
            }

            if (!in_array($marker, $this->specialCharacters, true)) {
                $this->specialCharacters[] = $marker;
            }
        }
    }

    /**
     * @return array<string, list<string>>
     */
    private function coreInlineExtensions(): array
    {
        return [
            'Code' => ['code', 'code.inline'],
            'Image' => ['images'],
            'Markup' => ['allow_raw_html'],
            'Link' => ['links'],
            'Url' => ['links'],
            'UrlTag' => ['links'],
            'EmailTag' => ['links', 'links.email_links'],
            'FootnoteMarker' => ['footnotes'],
            'Emphasis' => ['emphasis'],
            'Strikethrough' => ['emphasis', 'emphasis.strikethroughs'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function coreBlockExtensions(): array
    {
        return [
            'Code' => ['code', 'code.blocks'],
            'FencedCode' => ['code', 'code.blocks'],
            'Header' => ['headings'],
            'SetextHeader' => ['headings'],
            'Rule' => ['thematic_breaks'],
            'List' => ['lists'],
            'Table' => ['tables'],
            'Comment' => ['comments'],
            'Markup' => ['allow_raw_html'],
            'Quote' => ['quotes'],
            'Reference' => ['references'],
            'DefinitionList' => ['definition_lists'],
            'Abbreviation' => ['abbreviations'],
            'Footnote' => ['footnotes'],
        ];
    }

    /**
     * @return list<array{type: string, markers: list<string>, config: list<string>}>
     */
    private function customInlineExtensions(): array
    {
        return [
            ['type' => 'Marking', 'markers' => ['='], 'config' => ['emphasis', 'emphasis.mark']],
            ['type' => 'Insertions', 'markers' => ['+'], 'config' => ['emphasis', 'emphasis.insertions']],
            ['type' => 'Keystrokes', 'markers' => ['['], 'config' => ['emphasis', 'emphasis.keystrokes']],
            ['type' => 'MathNotation', 'markers' => ['\\', '$'], 'config' => ['math', 'math.inline']],
            ['type' => 'Superscript', 'markers' => ['^'], 'config' => ['emphasis', 'emphasis.superscript']],
            ['type' => 'Subscript', 'markers' => ['~'], 'config' => ['emphasis', 'emphasis.subscript']],
            ['type' => 'Emojis', 'markers' => [':'], 'config' => ['emojis']],
            [
                'type' => 'Smartypants',
                'markers' => ['<', '>', '-', '.', "'", '"', '`'],
                'config' => ['smartypants'],
            ],
            [
                'type' => 'Typographer',
                'markers' => ['(', '.', '+', '!', '?'],
                'config' => ['typographer'],
            ],
        ];
    }

    /**
     * @return list<array{type: string, markers: list<string>, config: list<string>}>
     */
    private function customBlockExtensions(): array
    {
        return [
            ['type' => 'MathNotation', 'markers' => ['\\', '$'], 'config' => ['math', 'math.block']],
            ['type' => 'Alert', 'markers' => ['>'], 'config' => ['alerts']],
        ];
    }

    private function inlineTypeEnabled(string $inlineType): bool
    {
        if (!array_key_exists($inlineType, $this->inlineTypeEnabledCache)) {
            $this->inlineTypeEnabledCache[$inlineType]
                = $this->extensionConfigEnabled($this->inlineExtensionConfigPaths[$inlineType] ?? []);
        }

        return $this->inlineTypeEnabledCache[$inlineType];
    }

    private function blockTypeEnabled(string $blockType): bool
    {
        if (!array_key_exists($blockType, $this->blockTypeEnabledCache)) {
            $this->blockTypeEnabledCache[$blockType]
                = $this->extensionConfigEnabled($this->blockExtensionConfigPaths[$blockType] ?? []);
        }

        return $this->blockTypeEnabledCache[$blockType];
    }

    private function clearExtensionEnabledCache(): void
    {
        $this->inlineTypeEnabledCache = [];
        $this->blockTypeEnabledCache = [];
    }

    /**
     * @param list<string> $configPaths
     */
    private function extensionConfigEnabled(array $configPaths): bool
    {
        foreach ($configPaths as $configPath) {
            if (!$this->configEnabled($configPath)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, list<string>> $types
     */
    private function moveSpecialCharacterHandlerToEnd(array &$types): void
    {
        foreach ($types as &$list) {
            $key = array_search('SpecialCharacter', $list, true);
            if ($key === false) {
                continue;
            }

            unset($list[$key]);
            $list[] = 'SpecialCharacter';
        }
        unset($list);
    }
}
