<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended;

use BenjaminHoegh\ParsedownExtended\Configuration\Configuration;

/**
 * Class ParsedownExtended
 *
 * Extended version of Parsedown for customized Markdown parsing.
 * Provides extended parsing capabilities, version checking, and custom configuration options.
 *
 */
// @psalm-suppress UndefinedClass
class ParsedownExtended extends \ParsedownExtra
{
    use Extensions\Registry\ExtensionRegistrar;
    use Extensions\Toc\TocExtensions;

    public const VERSION = '3.0.0';
    public const VERSION_PARSEDOWN_REQUIRED = '1.8.0';
    public const VERSION_PARSEDOWN_EXTRA_REQUIRED = '0.9.0';

    /** Parsedown-compatible punctuation that is always backslash-escapable. */
    private const PARSEDOWN_ESCAPABLE_SPECIAL_CHARACTERS = [
        '\\' => true,
        '`' => true,
        '*' => true,
        '_' => true,
        '{' => true,
        '}' => true,
        '[' => true,
        ']' => true,
        '(' => true,
        ')' => true,
        '>' => true,
        '#' => true,
        '+' => true,
        '-' => true,
        '.' => true,
        '!' => true,
        '|' => true,
        '~' => true,
    ];

    /** Cached configuration handler. */
    private ?Configuration $configHandler = null;

    /** @var array<string, mixed> */
    private array $configurationValues;

    /** @var array<string, int> */
    private array $anchorCounts = [];

    /** @var array<string, true> */
    private array $usedAnchorIds = [];

    /** @var list<array<string, mixed>> */
    private array $contentsList = [];

    private string $contentsListHtml = '';

    private bool $contentsListHtmlDirty = false;

    private int $firstContentsHeadingLevel = 0;

    private int $footnoteCount = 0;

    private bool $predefinedAbbreviationsAdded = false;

    /** @var array<string, list<string>>|null */
    private ?array $activeInlineTypes = null;

    private string $activeInlineMarkerList = '';

    /** @var array<string, list<string>>|null */
    private ?array $activeBlockTypes = null;

    /** @var list<string> */
    private array $activeUnmarkedBlockTypes = [];

    /** @var array<string, list<string>> */
    private array $activeBlockCandidateTypes = [];

    /** @var array<string, array<string, true>> */
    private array $configValueSetCache = [];

    /** @var array<string, mixed> */
    private array $runtimeValueCache = [];

    /**
     * Constructor for ParsedownExtended.
     *
     * Initializes the class and performs version checks for Parsedown dependencies.
     */
    public function __construct(array $overrides = [])
    {
        // Check if the installed Parsedown version meets the minimum requirement
        $this->checkVersion('Parsedown', \Parsedown::version, self::VERSION_PARSEDOWN_REQUIRED);

        // Ensure ParsedownExtra meets the version requirement
        $this->checkVersion('ParsedownExtra', \ParsedownExtra::version, self::VERSION_PARSEDOWN_EXTRA_REQUIRED);
        parent::__construct();

        $this->configurationValues = Configuration::defaults();

        // Apply overrides if provided
        if ($overrides !== []) {
            $this->applyOverrides($overrides);
        }

        $this->registerExtensions();
    }

    /**
     * Check version compatibility for a specific component.
     *
     * Verifies that an installed Parsedown dependency meets the required version.
     * Throws an exception if the version is not sufficient.
     *
     * @since 1.3.0
     *
     * @param string $component The name of the dependency being checked
     * @param string $currentVersion The current version of the component installed
     * @param string $requiredVersion The minimum required version of the component
     *
     * @throws \Exception If the current version is lower than the required version
     */
    private function checkVersion(string $component, string $currentVersion, string $requiredVersion): void
    {
        // Compare the current version with the required version
        if (version_compare($currentVersion, $requiredVersion) < 0) {
            // Prepare an error message indicating version incompatibility
            $msg_error  = 'Version Error.' . PHP_EOL;
            $msg_error .= "  ParsedownExtended requires a later version of $component." . PHP_EOL;
            $msg_error .= "  - Current version : $currentVersion" . PHP_EOL;
            $msg_error .= "  - Required version: $requiredVersion and later" . PHP_EOL;

            // Throw an exception with the version error message
            throw new \Exception($msg_error);
        }
    }

    // Configurations Handler
    // -------------------------------------------------------------------------

    /**
     * Retrieves the flat schema array.
     *
     * @return array The flat schema defined in the class.
     */
    public function getFlatSchema(): array
    {
        return Configuration::definitions();
    }

    /**
     * Returns the configuration handler bound to this parser instance.
     */
    public function config(): Configuration
    {
        if ($this->configHandler === null) {
            $this->configHandler = new Configuration(
                $this->configurationValues,
                $this->configurationChanged(...)
            );
        }
        return $this->configHandler;
    }

    /**
     * Reads an internal boolean config flag without creating or rebinding the public config handler.
     */
    protected function configEnabled(string $path): bool
    {
        $path = Configuration::resolve($path);

        return (bool) ($this->configurationValues[$path] ?? false);
    }

    protected function hasRuntimeCacheValue(string $key): bool
    {
        return array_key_exists($key, $this->runtimeValueCache);
    }

    protected function runtimeCacheValue(string $key): mixed
    {
        return $this->runtimeValueCache[$key] ?? null;
    }

    protected function storeRuntimeCacheValue(string $key, mixed $value): void
    {
        $this->runtimeValueCache[$key] = $value;
    }

    /**
     * Reads an internal payload config value without creating or rebinding the public config handler.
     */
    protected function configValue(string $path): mixed
    {
        return $this->configurationValues[$path] ?? null;
    }

    protected function configValueSetContains(string $path, string $value): bool
    {
        $set = $this->configValueSet($path);

        return isset($set[$value]);
    }

    private function configValueSet(string $path): array
    {
        if (array_key_exists($path, $this->configValueSetCache)) {
            return $this->configValueSetCache[$path];
        }

        $values = $this->configurationValues[$path] ?? [];
        if (!is_array($values)) {
            return $this->configValueSetCache[$path] = [];
        }

        $set = [];
        foreach ($values as $value) {
            if (is_scalar($value)) {
                $set[(string) $value] = true;
            }
        }

        return $this->configValueSetCache[$path] = $set;
    }

    /**
     * Invalidates derived parser state after public runtime configuration changes.
     */
    private function configurationChanged(): void
    {
        $this->activeInlineTypes = null;
        $this->activeInlineMarkerList = '';
        $this->activeBlockTypes = null;
        $this->activeUnmarkedBlockTypes = [];
        $this->activeBlockCandidateTypes = [];
        $this->configValueSetCache = [];
        $this->runtimeValueCache = [];
        $this->clearExtensionEnabledCache();
    }

    /**
     * Builds the inline handler map and marker list used by strpbrk from enabled handlers only.
     */
    private function getActiveInlineTypes(): array
    {
        if ($this->activeInlineTypes !== null) {
            return $this->activeInlineTypes;
        }

        $inlineTypes = $this->InlineTypes;
        $inlineMarkerList = $this->inlineMarkerList;

        foreach ($this->configuredDelimiterMarkers('math.inline.delimiters') as $marker) {
            $inlineTypes[$marker] ??= [];

            if (!in_array('MathNotation', $inlineTypes[$marker], true)) {
                array_unshift($inlineTypes[$marker], 'MathNotation');
            }

            if (!str_contains($inlineMarkerList, $marker)) {
                $inlineMarkerList .= $marker;
            }
        }

        $activeInlineTypes = [];
        $markerList = '';
        foreach (str_split($inlineMarkerList) as $marker) {
            if (!isset($inlineTypes[$marker])) {
                continue;
            }

            foreach ($inlineTypes[$marker] as $inlineType) {
                if ($this->inlineTypeEnabled($inlineType)) {
                    $activeInlineTypes[$marker][] = $inlineType;
                }
            }

            if (isset($activeInlineTypes[$marker])) {
                $markerList .= $marker;
            }
        }

        $this->activeInlineTypes = $activeInlineTypes;
        $this->activeInlineMarkerList = $markerList;

        return $this->activeInlineTypes;
    }

    /**
     * Builds block handler maps from currently enabled handlers only.
     */
    private function initializeActiveBlockTypes(): void
    {
        if ($this->activeBlockTypes !== null) {
            return;
        }

        $blockTypes = $this->BlockTypes;
        foreach ($this->configuredDelimiterMarkers('math.block.delimiters') as $marker) {
            $blockTypes[$marker] ??= [];

            if (!in_array('MathNotation', $blockTypes[$marker], true)) {
                array_unshift($blockTypes[$marker], 'MathNotation');
            }
        }

        $activeBlockTypes = [];
        foreach ($blockTypes as $marker => $markerBlockTypes) {
            foreach ($markerBlockTypes as $blockType) {
                if ($this->blockTypeEnabled($blockType)) {
                    $activeBlockTypes[$marker][] = $blockType;
                }
            }
        }

        $activeUnmarkedBlockTypes = [];
        foreach ($this->unmarkedBlockTypes as $blockType) {
            if ($this->blockTypeEnabled($blockType)) {
                $activeUnmarkedBlockTypes[] = $blockType;
            }
        }

        $activeBlockCandidateTypes = [];
        foreach ($activeBlockTypes as $marker => $blockTypes) {
            $activeBlockCandidateTypes[$marker] = array_merge($activeUnmarkedBlockTypes, $blockTypes);
        }

        $this->activeBlockTypes = $activeBlockTypes;
        $this->activeUnmarkedBlockTypes = $activeUnmarkedBlockTypes;
        $this->activeBlockCandidateTypes = $activeBlockCandidateTypes;
    }

    /**
     * Returns the first byte used to dispatch each configured delimiter pair.
     *
     * @return list<string>
     */
    private function configuredDelimiterMarkers(string $path): array
    {
        $delimiters = $this->configValue($path);
        if (!is_array($delimiters)) {
            return [];
        }

        $markers = [];
        foreach ($delimiters as $delimiter) {
            $left = is_array($delimiter) ? ($delimiter['left'] ?? null) : null;
            if (is_string($left) && $left !== '') {
                $markers[$left[0]] = true;
            }
        }

        return array_keys($markers);
    }

    /**
     * Applies constructor overrides through the same rules as the public handler.
     */
    private function applyOverrides(array $overrides): void
    {
        $this->config()->set($overrides);
    }

    // Overwriting core Parsedown functions
    // -------------------------------------------------------------------------

    /**
     * Resets all state that belongs to the document about to be parsed.
     */
    protected function beginDocument(): void
    {
        $this->anchorCounts = [];
        $this->usedAnchorIds = [];
        $this->contentsList = [];
        $this->contentsListHtml = '';
        $this->contentsListHtmlDirty = false;
        $this->firstContentsHeadingLevel = 0;
        $this->footnoteCount = 0;
        $this->predefinedAbbreviationsAdded = false;
        $this->DefinitionData = [];
        $this->initializePredefinedAbbreviations();
    }

    /**
     * Parses a complete Markdown document through the explicit lifecycle entry point.
     *
     * @param string $text Markdown source.
     * @return array Parsed element tree.
     */
    protected function textElements($text): array
    {
        $this->beginDocument();

        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = trim($text, "\n");

        return $this->linesElements(explode("\n", $text));
    }

    /**
     * Parses Markdown lines into block elements while honoring extension metadata.
     *
     * This mirrors Parsedown's block dispatch loop, with one extra guard: registered
     * block types are skipped when their configured feature flags are disabled.
     *
     * @param array $lines Markdown source lines.
     * @return array Parsed block elements.
     */
    protected function linesElements(array $lines): array
    {
        $Elements = [];
        $CurrentBlock = null;
        $this->initializeActiveBlockTypes();
        $activeBlockTypes = $this->activeBlockCandidateTypes;
        $activeUnmarkedBlockTypes = $this->activeUnmarkedBlockTypes;

        foreach ($lines as $line) {
            if (chop($line) === '') {
                if (isset($CurrentBlock)) {
                    $CurrentBlock['interrupted'] = isset($CurrentBlock['interrupted'])
                        ? $CurrentBlock['interrupted'] + 1
                        : 1;
                }

                continue;
            }

            while (($beforeTab = strstr($line, "\t", true)) !== false) {
                $shortage = 4 - mb_strlen($beforeTab, 'utf-8') % 4;

                $line = $beforeTab
                    . str_repeat(' ', $shortage)
                    . substr($line, strlen($beforeTab) + 1);
            }

            $indent = strspn($line, ' ');
            $text = $indent > 0 ? substr($line, $indent) : $line;
            $Line = ['body' => $line, 'indent' => $indent, 'text' => $text];

            if (isset($CurrentBlock['continuable'])) {
                $methodName = 'block' . $CurrentBlock['type'] . 'Continue';
                $Block = $this->$methodName($Line, $CurrentBlock);

                if (isset($Block)) {
                    $CurrentBlock = $Block;

                    continue;
                }

                if ($this->isBlockCompletable($CurrentBlock['type'])) {
                    $methodName = 'block' . $CurrentBlock['type'] . 'Complete';
                    $CurrentBlock = $this->$methodName($CurrentBlock);
                }
            }

            $marker = $text[0];
            $blockTypes = $activeBlockTypes[$marker] ?? $activeUnmarkedBlockTypes;

            $Block = null;
            foreach ($blockTypes as $blockType) {
                $Block = $this->{"block$blockType"}($Line, $CurrentBlock);

                if (isset($Block)) {
                    $Block['type'] = $blockType;

                    if (!isset($Block['identified'])) {
                        if (isset($CurrentBlock)) {
                            $Elements[] = $this->extractElement($CurrentBlock);
                        }

                        $Block['identified'] = true;
                    }

                    if ($this->isBlockContinuable($blockType)) {
                        $Block['continuable'] = true;
                    }

                    $CurrentBlock = $Block;

                    continue 2;
                }
            }

            if (isset($CurrentBlock) && $CurrentBlock['type'] === 'Paragraph') {
                $Block = $this->paragraphContinue($Line, $CurrentBlock);
            }

            if (isset($Block)) {
                $CurrentBlock = $Block;
            } else {
                if (isset($CurrentBlock)) {
                    $Elements[] = $this->extractElement($CurrentBlock);
                }

                $CurrentBlock = $this->paragraph($Line);
                $CurrentBlock['identified'] = true;
            }
        }

        if (isset($CurrentBlock['continuable']) && $this->isBlockCompletable($CurrentBlock['type'])) {
            $methodName = 'block' . $CurrentBlock['type'] . 'Complete';
            $CurrentBlock = $this->$methodName($CurrentBlock);
        }

        if (isset($CurrentBlock)) {
            $Elements[] = $this->extractElement($CurrentBlock);
        }

        return $Elements;
    }

    /**
     * Process a line of Markdown text and extract inline elements.
     *
     * This function processes a line of Markdown text by iteratively searching for
     * markers in the text, and applies the appropriate inline handlers for those markers.
     *
     * @since 0.1.0
     *
     * @param string $text The text to be parsed for inline elements.
     * @param array $nonNestables Array of inline types that should not be nested.
     * @return string The parsed HTML markup for the given line.
     */
    public function line($text, $nonNestables = []): string
    {
        $this->initializePredefinedAbbreviations();
        return $this->elements($this->lineElements($text, $nonNestables));
    }

    /**
     * Parses a line of text into inline elements.
     *
     * This function processes the given text, identifying markers and breaking it into inline elements.
     * Inline elements include things like bold, italic, links, etc. It recursively handles nesting and respects
     * non-nestable contexts.
     *
     * @since 0.1.0
     *
     * @param string $text The text to be parsed.
     * @param array $nonNestables An array of inline types that should not be nested within this context.
     *
     * @return array An array of parsed elements representing the structure of the given text.
     */
    protected function lineElements($text, $nonNestables = []): array
    {
        // Standardize line breaks.
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        $Elements = [];
        $nonNestables = $this->normalizeNonNestables($nonNestables);

        $activeInlineTypes = $this->getActiveInlineTypes();
        $inlineMarkerList = $this->activeInlineMarkerList;

        $markerSearchOffset = 0;
        $textLength = strlen($text);

        while ($inlineMarkerList !== '' && $markerSearchOffset < $textLength) {
            $markerPosition = $markerSearchOffset + strcspn($text, $inlineMarkerList, $markerSearchOffset);
            if ($markerPosition >= $textLength) {
                break;
            }

            $marker = $text[$markerPosition];
            $candidateInlineTypes = [];

            foreach ($activeInlineTypes[$marker] as $inlineType) {
                if (isset($nonNestables[$inlineType])) {
                    continue;
                }

                if (!$this->inlineTypeCanMatchAtPosition($inlineType, $text, $markerPosition)) {
                    continue;
                }

                $candidateInlineTypes[] = $inlineType;
            }

            if ($candidateInlineTypes === []) {
                $markerSearchOffset = $markerPosition + 1;
                continue;
            }

            $ExcerptStr = substr($text, $markerPosition);

            $Excerpt = [
                'text' => $ExcerptStr,
                'context' => $text,
                'before' => $markerPosition > 0 ? $text[$markerPosition - 1] : '',
            ];

            foreach ($candidateInlineTypes as $inlineType) {
                $Inline = $this->{"inline$inlineType"}($Excerpt);

                if (!isset($Inline)) {
                    continue;
                }

                // Make sure the inline belongs to this marker.
                if (isset($Inline['position']) && $Inline['position'] > $markerPosition) {
                    continue;
                }

                // Set a default inline position.
                if (!isset($Inline['position'])) {
                    $Inline['position'] = $markerPosition;
                }

                // Propagate non-nestable markers through nested elements.
                $Inline['element']['nonNestables'] = isset($Inline['element']['nonNestables'])
                    ? $this->normalizeNonNestables($Inline['element']['nonNestables']) + $nonNestables
                    : $nonNestables;

                // Compile text before the inline.
                $InlineText = $this->inlineText(substr($text, 0, $Inline['position']));
                $Elements[] = $InlineText['element'];

                // Compile inline element itself.
                $Elements[] = $this->extractElement($Inline);

                // Remove processed text and continue.
                $text = substr($text, $Inline['position'] + $Inline['extent']);
                $markerSearchOffset = 0;
                $textLength = strlen($text);
                continue 2;
            }

            // Keep unmatched markers in the pending text so they can be emitted
            // as one element instead of allocating an element per character.
            $markerSearchOffset = $markerPosition + 1;
        }

        $InlineText = $this->inlineText($text);
        $Elements[] = $InlineText['element'];

        foreach ($Elements as &$Element) {
            if (!isset($Element['autobreak'])) {
                $Element['autobreak'] = false;
            }
        }
        unset($Element);

        return $Elements;
    }

    /**
     * Runs an optional cheap marker check before allocating the excerpt suffix.
     */
    private function inlineTypeCanMatchAtPosition(string $inlineType, string $text, int $position): bool
    {
        $methodName = 'inline' . $inlineType . 'MarkerMatches';

        return !method_exists($this, $methodName) || $this->$methodName($text, $position);
    }

    /**
     * Normalize Parsedown's list and element-metadata forms into a lookup set.
     *
     * @param array<int|string, mixed> $nonNestables
     * @return array<string, bool>
     */
    private function normalizeNonNestables(array $nonNestables): array
    {
        $normalized = [];

        foreach ($nonNestables as $key => $value) {
            if (is_string($key)) {
                $normalized[$key] = true;
                continue;
            }

            if (is_string($value)) {
                $normalized[$value] = true;
            }
        }

        return $normalized;
    }
}
