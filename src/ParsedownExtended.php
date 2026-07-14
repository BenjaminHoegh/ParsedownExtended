<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended;

use BenjaminHoegh\ParsedownExtended\Configuration\Configuration;
use BenjaminHoegh\ParsedownExtended\Configuration\ConfigurationSchema;
use BenjaminHoegh\ParsedownExtended\Configuration\SchemaCompiler;

class_alias(class_exists('ParsedownExtra') ? 'ParsedownExtra' : 'Parsedown', 'ParsedownExtendedParentAlias');

/**
 * Class ParsedownExtended
 *
 * Extended version of Parsedown for customized Markdown parsing.
 * Provides extended parsing capabilities, version checking, and custom configuration options.
 *
 */
// @psalm-suppress UndefinedClass
class ParsedownExtended extends \ParsedownExtendedParentAlias
{
    use Extensions\Registry\ExtensionRegistration;
    use Extensions\Registry\InlineExtensions;
    use Extensions\Registry\BlockExtensions;
    use Extensions\Toc\TocExtensions;

    public const VERSION = '3.0.0';
    public const VERSION_PARSEDOWN_REQUIRED = '1.8.0';
    public const VERSION_PARSEDOWN_EXTRA_REQUIRED = '0.9.0';
    public const MIN_PHP_VERSION = '7.4';

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

    /** @var object|null $configHandler Cached configuration handler */
    private ?object $configHandler = null;

    /** @var array $BOOLEAN_PATHS Stores a set of boolean config paths. */
    private static array $BOOLEAN_PATHS = [];

    /** @var array $FLAT_SCHEMA Stores a flat schema of configuration options for easy access. */
    private static array $FLAT_SCHEMA   = [];

    /** @var array $DEFAULT_FEATURES Stores default boolean settings keyed by path. */
    private static array $DEFAULT_FEATURES = [];

    /** @var array $DEFAULT_PAYLOAD Stores default non-boolean settings for the configuration. */
    private static array $DEFAULT_PAYLOAD = [];

    /** @var bool $COMPILED Indicates whether the schema has been compiled. */
    private static bool  $COMPILED = false;

    /** @var array $features Stores boolean feature flags for the instance. */
    private array $features;

    /** @var array $payload Stores non-boolean settings for the instance. */
    private array $payload;   // non‑boolean settings

    /** @var array<string, list<string>> Cached inline handlers for currently enabled features. */
    private array $activeInlineTypes = [];

    /** @var string $activeInlineMarkerList Cached marker list for currently enabled inline handlers. */
    private string $activeInlineMarkerList = '';

    /** @var bool $activeInlineTypesValid Whether the active inline handler cache reflects current config. */
    private bool $activeInlineTypesValid = false;

    /** @var array<string, list<string>> Cached block handlers for currently enabled features. */
    private array $activeBlockTypes = [];

    /** @var list<string> Cached unmarked block handlers for currently enabled features. */
    private array $activeUnmarkedBlockTypes = [];

    /** @var array<string, list<string>> Cached block dispatch candidates keyed by marker. */
    private array $activeBlockCandidateTypes = [];

    /** @var bool $activeBlockTypesValid Whether the active block handler cache reflects current config. */
    private bool $activeBlockTypesValid = false;

    /** @var array<string, array<string, true>> Cached lookup sets for list-like config payloads. */
    private array $configValueSetCache = [];

    /**
     * Constructor for ParsedownExtended.
     *
     * Initializes the class and performs version checks for PHP and Parsedown dependencies.
     */
    public function __construct(array $overrides = [])
    {
        // Check if the current PHP version meets the minimum requirement
        $this->checkVersion('PHP', PHP_VERSION, self::MIN_PHP_VERSION);

        // Check if the installed Parsedown version meets the minimum requirement
        $this->checkVersion('Parsedown', \Parsedown::version, self::VERSION_PARSEDOWN_REQUIRED);

        if (class_exists('ParsedownExtra')) {
            // Ensure ParsedownExtra meets the version requirement
            $this->checkVersion('ParsedownExtra', \ParsedownExtra::version, self::VERSION_PARSEDOWN_EXTRA_REQUIRED);
            parent::__construct();
        }

        // Initialize the configuration schema
        if (!self::$COMPILED) {
            $compiled = (new SchemaCompiler())->compile(ConfigurationSchema::DEFAULT);

            self::$BOOLEAN_PATHS = $compiled['booleanPaths'];
            self::$FLAT_SCHEMA = $compiled['flatSchema'];
            self::$DEFAULT_FEATURES = $compiled['defaultFeatures'];
            self::$DEFAULT_PAYLOAD = $compiled['defaultPayload'];

            self::$COMPILED = true;
        }

        // Initialize features and payload
        $this->features = self::$DEFAULT_FEATURES;
        $this->payload  = self::$DEFAULT_PAYLOAD;

        // Apply overrides if provided
        if ($overrides) {
            $this->applyOverrides($overrides);
        }

        $this->registerExtensions();
        $this->warmRuntimeCaches();
    }

    /**
     * Check version compatibility for a specific component.
     *
     * Verifies if the current version of a component (e.g., PHP or Parsedown) meets the required version.
     * Throws an exception if the version is not sufficient.
     *
     * @since 1.3.0
     *
     * @param string $component The name of the component being checked (e.g., 'PHP', 'Parsedown')
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
        return self::$FLAT_SCHEMA;
    }

    /**
     * Returns a singleton configuration handler object for managing boolean settings and payload values.
     *
     * The handler provides methods to get, set, and export configuration values, supporting both
     * boolean path-based settings and arbitrary payload data. The configuration schema and boolean-path
     * mapping are provided statically. The handler validates types and throws exceptions for invalid
     * paths or types.
     *
     * @return object Configuration handler with the following public methods:
     *                - get(string $path): mixed
     *                - set(string|array $path, mixed $value = null): self
     *                - export(): array
     *                - bind(array &$features, array &$payload): void
     *
     * @throws \InvalidArgumentException If an invalid config path or type is provided to set().
     */
    public function config(): object
    {
        if ($this->configHandler === null) {
            $this->configHandler = new Configuration(
                self::$BOOLEAN_PATHS,
                self::$FLAT_SCHEMA,
                function (): void {
                    $this->configurationChanged();
                }
            );
            $this->configHandler->bind($this->features, $this->payload);
        }
        return $this->configHandler;
    }

    /**
     * Reads an internal boolean config flag without creating or rebinding the public config handler.
     */
    protected function configEnabled(string $path): bool
    {
        if (isset($this->features[$path])) {
            return $this->features[$path];
        }

        $enabledPath = $path . '.enabled';
        return $this->features[$enabledPath] ?? false;
    }

    /**
     * Reads an internal payload config value without creating or rebinding the public config handler.
     */
    protected function configValue(string $path)
    {
        return $this->payload[$path] ?? null;
    }

    protected function configValueSetContains(string $path, string $value): bool
    {
        $set = $this->configValueSet($path);

        return isset($set[$value]);
    }

    private function configValueSet(string $path): array
    {
        if (isset($this->configValueSetCache[$path])) {
            return $this->configValueSetCache[$path];
        }

        $values = $this->payload[$path] ?? [];
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
        $this->activeInlineTypes = [];
        $this->activeInlineMarkerList = '';
        $this->activeInlineTypesValid = false;
        $this->activeBlockTypes = [];
        $this->activeUnmarkedBlockTypes = [];
        $this->activeBlockCandidateTypes = [];
        $this->activeBlockTypesValid = false;
        $this->configValueSetCache = [];
        $this->clearExtensionEnabledCache();
        $this->clearSmartypantsSubstitutionCache();
    }

    private function warmRuntimeCaches(): void
    {
        $this->getActiveInlineTypes();
        $this->getActiveBlockTypes();
        $this->configValueSet('headings.allowed_levels');
        $this->configValueSet('toc.levels');
    }

    /**
     * Builds the inline handler map and marker list used by strpbrk from enabled handlers only.
     */
    private function getActiveInlineTypes(): array
    {
        if ($this->activeInlineTypesValid) {
            return $this->activeInlineTypes;
        }

        $activeInlineTypes = [];
        $markerList = '';
        foreach (str_split($this->inlineMarkerList) as $marker) {
            if (!isset($this->InlineTypes[$marker])) {
                continue;
            }

            foreach ($this->InlineTypes[$marker] as $inlineType) {
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
        $this->activeInlineTypesValid = true;

        return $this->activeInlineTypes;
    }

    /**
     * Builds block handler maps from currently enabled handlers only.
     */
    private function getActiveBlockTypes(): array
    {
        if ($this->activeBlockTypesValid) {
            return $this->activeBlockTypes;
        }

        $activeBlockTypes = [];
        foreach ($this->BlockTypes as $marker => $blockTypes) {
            foreach ($blockTypes as $blockType) {
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
        $this->activeBlockTypesValid = true;

        return $this->activeBlockTypes;
    }

    /**
     * Recursively applies configuration overrides.
     *
     * Traverses the provided associative array of overrides, building dot-notated
     * paths for nested configuration keys. If a value is an array and the computed
     * path is not defined as a flat schema entry, the method recurses into that
     * array. Otherwise, it sets the value on the provided configuration handler.
     *
     * @param array $ovr The associative array of configuration overrides.
     * @param string $prefix The prefix for nested configuration keys.
     * @param object|null $configHandler The configuration handler to apply values to.
     *
     * @return void
     */
    private function applyOverrides(array $ovr, string $prefix = '', ?object $configHandler = null): void
    {
        if ($configHandler === null) {
            $configHandler = new Configuration(self::$BOOLEAN_PATHS, self::$FLAT_SCHEMA);
            $configHandler->bind($this->features, $this->payload);
        }

        foreach ($ovr as $k => $v) {
            $path = $prefix === '' ? $k : $prefix . '.' . $k;

            if (is_array($v) && !isset(self::$FLAT_SCHEMA[$path])) {
                $this->applyOverrides($v, $path, $configHandler);
                continue;
            }
            $configHandler->set($path, $v);
        }
    }

    // Overwriting core Parsedown functions
    // -------------------------------------------------------------------------

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
        $this->getActiveBlockTypes();
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
    public function line($text, $nonNestables = [])
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
