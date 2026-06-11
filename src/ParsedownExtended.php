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
    use Extensions\ExtensionRegistrar;
    use Extensions\Concerns\RegistersInlineTypes;
    use Extensions\Concerns\RegistersBlockTypes;
    use Extensions\Concerns\MovesSpecialCharacterHandler;
    use Extensions\Inline\InlineCodeExtension;
    use Extensions\Inline\InlineImageExtension;
    use Extensions\Inline\InlineMarkupExtension;
    use Extensions\Inline\StrikethroughExtension;
    use Extensions\Inline\EmphasisExtension;
    use Extensions\Inline\EscapeSequenceExtension;
    use Extensions\Inline\MarkingExtension;
    use Extensions\Inline\InsertionsExtension;
    use Extensions\Inline\KeystrokesExtension;
    use Extensions\Inline\SuperscriptExtension;
    use Extensions\Inline\SubscriptExtension;
    use Extensions\Inline\InlineMathExtension;
    use Extensions\Inline\EmojiExtension;
    use Extensions\Inline\SmartypantsExtension;
    use Extensions\Inline\TypographerExtension;
    use Extensions\Inline\LinkExtension;
    use Extensions\Block\FootnoteExtension;
    use Extensions\Block\DefinitionListExtension;
    use Extensions\Block\BlockCodeExtension;
    use Extensions\Block\CommentExtension;
    use Extensions\Block\ListExtension;
    use Extensions\Block\QuoteExtension;
    use Extensions\Block\RuleExtension;
    use Extensions\Block\BlockMarkupExtension;
    use Extensions\Block\ReferenceExtension;
    use Extensions\Block\TableExtension;
    use Extensions\Block\AbbreviationExtension;
    use Extensions\Block\AlertExtension;
    use Extensions\Block\BlockMathExtension;
    use Extensions\Block\DiagramExtension;
    use Extensions\Block\TaskListExtension;
    use Extensions\Block\TableSpanExtension;
    use Extensions\Block\HeadingExtension;
    use Extensions\Toc\TableOfContentsExtension;
    use Extensions\Toc\AnchorExtension;
    use Extensions\Toc\TransliterationExtension;

    public const VERSION = '2.2.1';
    public const VERSION_PARSEDOWN_REQUIRED = '1.8.0';
    public const VERSION_PARSEDOWN_EXTRA_REQUIRED = '0.9.0';
    public const MIN_PHP_VERSION = '7.4';

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
            $this->configHandler = new Configuration(self::$BOOLEAN_PATHS, self::$FLAT_SCHEMA);
        }
        $this->configHandler->bind($this->features, $this->payload);
        return $this->configHandler;
    }

    /**
     * Recursively applies configuration overrides to the current configuration.
     *
     * This method traverses the provided associative array of overrides, optionally using a prefix
     * to build dot-notated paths for nested configuration keys. If a value is an array and does not
     * correspond to a flat schema entry, the method recurses into that array. Otherwise, it sets the
     * configuration value at the computed path.
     *
     * @param array $ovr    The associative array of configuration overrides.
     * @param string $prefix The prefix for nested configuration keys, used for dot notation (optional).
     *
     * @return void
     */
    private function applyOverrides(array $ovr, string $prefix = '', ?object $configHandler = null): void
    {
        $configHandler ??= $this->config();

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
        $this->initializePredefinedAbbreviations();

        // Standardize line breaks.
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        $Elements = [];
        $nonNestables = empty($nonNestables)
            ? []
            : array_fill_keys($nonNestables, true);

        while ($ExcerptStr = strpbrk($text, $this->inlineMarkerList)) {
            $marker = $ExcerptStr[0];
            $markerPosition = strlen($text) - strlen($ExcerptStr);

            $Excerpt = [
                'text' => $ExcerptStr,
                'context' => $text,
                'before' => $markerPosition > 0 ? $text[$markerPosition - 1] : '',
            ];

            foreach ($this->InlineTypes[$marker] as $inlineType) {
                if (isset($nonNestables[$inlineType])) {
                    continue;
                }

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
                    ? array_merge($Inline['element']['nonNestables'], $nonNestables)
                    : $nonNestables;

                // Compile text before the inline.
                $InlineText = $this->inlineText(substr($text, 0, $Inline['position']));
                $Elements[] = $InlineText['element'];

                // Compile inline element itself.
                $Elements[] = $this->extractElement($Inline);

                // Remove processed text and continue.
                $text = substr($text, $Inline['position'] + $Inline['extent']);
                continue 2;
            }

            // Marker does not belong to an inline.
            $InlineText = $this->inlineText(substr($text, 0, $markerPosition + 1));
            $Elements[] = $InlineText['element'];
            $text = substr($text, $markerPosition + 1);
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
}
