<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Configuration;

/**
 * The parser's configuration values, definitions, and validation rules.
 *
 * Adding an option should require one entry in definitions() and its parser use.
 */
final class Configuration
{
    private const RULE_ABBREVIATION_MAP = 'abbreviation_map';
    private const RULE_DELIMITER_PAIRS = 'delimiter_pairs';
    private const RULE_HEADING_LEVELS = 'heading_levels';
    private const RULE_NON_EMPTY = 'non_empty';
    private const RULE_REGEX_MAP = 'regex_map';
    private const RULE_STRING_LIST = 'string_list';

    private const HEADING_LEVELS = [
        'h1' => true,
        'h2' => true,
        'h3' => true,
        'h4' => true,
        'h5' => true,
        'h6' => true,
    ];

    private array $values;

    private $onChange;

    public function __construct(array &$values, ?callable $onChange = null)
    {
        $this->values = &$values;
        $this->onChange = $onChange;
    }

    /**
     * The only list of supported configuration options.
     *
     * @return array<string, array{
     *     type: string,
     *     default: mixed,
     *     description: string,
     *     validationRule: string|null,
     *     alias: string|null
     * }>
     */
    public static function definitions(): array
    {
        static $definitions = null;

        if ($definitions !== null) {
            return $definitions;
        }

        $definitions = [
            'abbreviations.enabled' => self::feature(true, 'Enables abbreviation processing.', 'abbreviations'),
            'abbreviations.allow_custom' => self::option(true, 'Allows custom Markdown abbreviation definitions.'),
            'abbreviations.predefined' => self::option(
                [],
                'Abbreviations to load before every parse, keyed by abbreviation.',
                self::RULE_ABBREVIATION_MAP
            ),

            'alerts.enabled' => self::feature(true, 'Enables GitHub-style alert blocks.', 'alerts'),
            'alerts.class' => self::option('markdown-alert', 'Base CSS class used for generated alert wrappers.'),
            'alerts.types' => self::option(
                ['note', 'tip', 'important', 'warning', 'caution'],
                'Alert labels accepted after `> [!...]`.',
                self::RULE_STRING_LIST
            ),

            'allow_raw_html' => self::option(true, 'Allows raw inline and block HTML when safe mode is not escaping it.'),

            'code.enabled' => self::feature(true, 'Enables code parsing.', 'code'),
            'code.blocks' => self::option(true, 'Enables indented and fenced code blocks.'),
            'code.inline' => self::option(true, 'Enables inline backtick code.'),
            'comments' => self::option(true, 'Enables raw HTML comments when raw HTML is allowed.'),
            'definition_lists' => self::option(true, 'Enables Parsedown Extra definition lists.'),

            'diagrams.enabled' => self::feature(false, 'Enables diagram-aware fenced code handling.', 'diagrams'),
            'diagrams.chartjs' => self::option(true, 'Converts `chart` and `chartjs` fences to Chart.js canvas elements.'),
            'diagrams.mermaid' => self::option(true, 'Converts `mermaid` fences to Mermaid containers.'),
            'emojis' => self::option(true, 'Enables emoji shortcode replacement.'),

            'emphasis.enabled' => self::feature(true, 'Enables emphasis extensions.', 'emphasis'),
            'emphasis.bold' => self::option(true, 'Enables bold text.'),
            'emphasis.insertions' => self::option(true, 'Enables insertion syntax using `++text++`.'),
            'emphasis.italic' => self::option(true, 'Enables italic text.'),
            'emphasis.keystrokes' => self::option(true, 'Enables keystroke syntax using `[[Ctrl]]`.'),
            'emphasis.mark' => self::option(true, 'Enables mark syntax using `==text==`.'),
            'emphasis.strikethroughs' => self::option(true, 'Enables strikethrough syntax.'),
            'emphasis.subscript' => self::option(false, 'Enables subscript syntax using `~text~`.'),
            'emphasis.superscript' => self::option(false, 'Enables superscript syntax using `^text^`.'),
            'footnotes' => self::option(true, 'Enables Parsedown Extra footnotes.'),

            'headings.enabled' => self::feature(true, 'Enables heading parsing.', 'headings'),
            'headings.allowed_levels' => self::option(
                ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
                'Heading levels that may render.',
                self::RULE_HEADING_LEVELS
            ),
            'headings.auto_anchors.enabled' => self::feature(
                true,
                'Generates heading IDs.',
                'headings.auto_anchors'
            ),
            'headings.auto_anchors.blacklist' => self::option(
                [],
                'Heading IDs to skip when generating unique IDs.',
                self::RULE_STRING_LIST
            ),
            'headings.auto_anchors.delimiter' => self::option(
                '-',
                'Replacement delimiter used when building heading IDs.',
                self::RULE_NON_EMPTY
            ),
            'headings.auto_anchors.lowercase' => self::option(true, 'Lowercases generated heading IDs.'),
            'headings.auto_anchors.replacements' => self::option(
                [],
                'Regular-expression replacements applied before heading ID sanitization.',
                self::RULE_REGEX_MAP
            ),
            'headings.auto_anchors.transliterate' => self::option(false, 'Transliterates heading IDs toward ASCII.'),
            'headings.special_attributes' => self::option(
                true,
                'Enables Parsedown Extra heading attributes such as `{#custom-id}`.'
            ),
            'images' => self::option(true, 'Enables image parsing.'),

            'links.enabled' => self::feature(true, 'Enables link parsing.', 'links'),
            'links.current_host' => self::option('', 'Host name used when determining whether absolute links are external.'),
            'links.email_links' => self::option(true, 'Enables autolinked email addresses.'),
            'links.external_links.enabled' => self::feature(
                true,
                'Enables external links.',
                'links.external_links'
            ),
            'links.external_links.internal_hosts' => self::option(
                [],
                'Hostnames treated as internal even when absolute URLs are used.',
                self::RULE_STRING_LIST
            ),
            'links.external_links.nofollow' => self::option(false, 'Adds `nofollow` to external link `rel` attributes.'),
            'links.external_links.noopener' => self::option(false, 'Adds `noopener` to external link `rel` attributes.'),
            'links.external_links.noreferrer' => self::option(false, 'Adds `noreferrer` to external link `rel` attributes.'),
            'links.external_links.open_in_new_window' => self::option(false, 'Adds `target="_blank"` to external links.'),

            'lists.enabled' => self::feature(true, 'Enables ordered and unordered list parsing.', 'lists'),
            'lists.tasks' => self::option(true, 'Enables task-list checkboxes.'),

            'math.enabled' => self::feature(false, 'Enables math parsing.', 'math'),
            'math.block.enabled' => self::feature(true, 'Enables block math when math is enabled.', 'math.block'),
            'math.block.delimiters' => self::option(
                [['left' => '$$', 'right' => '$$']],
                'Block math delimiter pairs.',
                self::RULE_DELIMITER_PAIRS
            ),
            'math.inline.enabled' => self::feature(true, 'Enables inline math when math is enabled.', 'math.inline'),
            'math.inline.delimiters' => self::option(
                [['left' => '$', 'right' => '$']],
                'Inline math delimiter pairs.',
                self::RULE_DELIMITER_PAIRS
            ),

            'quotes' => self::option(true, 'Enables block quotes.'),
            'references' => self::option(true, 'Enables reference-style links.'),

            'smartypants.enabled' => self::feature(false, 'Enables Smartypants substitutions.', 'smartypants'),
            'smartypants.smart_angled_quotes' => self::option(true, 'Converts `<<quotes>>` when Smartypants is enabled.'),
            'smartypants.smart_backticks' => self::option(true, 'Converts double-backtick quotes when Smartypants is enabled.'),
            'smartypants.smart_dashes' => self::option(true, 'Converts double and triple dashes when Smartypants is enabled.'),
            'smartypants.smart_ellipses' => self::option(true, 'Converts three-dot ellipses when Smartypants is enabled.'),
            'smartypants.smart_quotes' => self::option(true, 'Converts straight quotes when Smartypants is enabled.'),
            'smartypants.substitutions.ellipses' => self::option('&hellip;', 'Replacement for ellipses.'),
            'smartypants.substitutions.left_angle_quote' => self::option('&laquo;', 'Replacement for left angle quotes.'),
            'smartypants.substitutions.left_double_quote' => self::option('&ldquo;', 'Replacement for left double quotes.'),
            'smartypants.substitutions.left_single_quote' => self::option('&lsquo;', 'Replacement for left single quotes.'),
            'smartypants.substitutions.mdash' => self::option('&mdash;', 'Replacement for em dashes.'),
            'smartypants.substitutions.ndash' => self::option('&ndash;', 'Replacement for en dashes.'),
            'smartypants.substitutions.right_angle_quote' => self::option('&raquo;', 'Replacement for right angle quotes.'),
            'smartypants.substitutions.right_double_quote' => self::option('&rdquo;', 'Replacement for right double quotes.'),
            'smartypants.substitutions.right_single_quote' => self::option('&rsquo;', 'Replacement for right single quotes.'),

            'tables.enabled' => self::feature(true, 'Enables table parsing.', 'tables'),
            'tables.tablespan' => self::option(true, 'Enables colspan and rowspan table span handling.'),
            'thematic_breaks' => self::option(true, 'Enables thematic breaks such as `---`, `***`, and `___`.'),

            'toc.enabled' => self::feature(true, 'Enables table-of-contents generation.', 'toc'),
            'toc.id' => self::option('toc', 'ID used on the generated ToC wrapper.'),
            'toc.levels' => self::option(
                ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
                'Heading levels included in generated ToCs.',
                self::RULE_HEADING_LEVELS
            ),
            'toc.tag' => self::option(
                '[TOC]',
                'Marker replaced by the generated ToC.',
                self::RULE_NON_EMPTY
            ),
            'typographer' => self::option(true, 'Enables typographer substitutions such as `(c)`, `(r)`, `(tm)`, and ellipses.'),
        ];

        foreach ($definitions as $path => $definition) {
            self::validate($path, $definition['default'], $definition);
        }

        return $definitions;
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        $defaults = [];

        foreach (self::definitions() as $path => $definition) {
            $defaults[$path] = $definition['default'];
        }

        return $defaults;
    }

    public static function resolve(string $path): string
    {
        return self::aliases()[$path] ?? $path;
    }

    /**
     * @return mixed
     */
    public function get(string $path)
    {
        $path = self::resolve($path);
        if (!isset(self::definitions()[$path])) {
            throw new \InvalidArgumentException("Invalid config path: {$path}");
        }

        return $this->values[$path];
    }

    /**
     * @param string|array<string, mixed> $path
     * @param mixed $value
     */
    public function set($path, $value = null): self
    {
        if (is_array($path)) {
            foreach ($path as $key => $item) {
                $this->set($key, $item);
            }

            return $this;
        }

        if (!is_string($path)) {
            throw new \InvalidArgumentException('Configuration path must be a string or an associative array.');
        }

        $definitions = self::definitions();
        if (isset($definitions[$path])) {
            return $this->setValue($path, $value, $definitions[$path]);
        }

        if (is_array($value) && $this->hasChildren($path, $definitions)) {
            foreach ($value as $key => $item) {
                $this->set($path . '.' . $key, $item);
            }

            return $this;
        }

        $path = self::resolve($path);
        if (!isset($definitions[$path])) {
            throw new \InvalidArgumentException("Invalid config path: {$path}");
        }

        return $this->setValue($path, $value, $definitions[$path]);
    }

    /**
     * @return array<string, mixed>
     */
    public function export(): array
    {
        return $this->values;
    }

    /**
     * @param mixed $default
     * @return array{type: string, default: mixed, description: string, validationRule: string|null, alias: null}
     */
    private static function option($default, string $description, ?string $validationRule = null): array
    {
        return [
            'type' => gettype($default),
            'default' => $default,
            'description' => $description,
            'validationRule' => $validationRule,
            'alias' => null,
        ];
    }

    /**
     * @return array{type: string, default: bool, description: string, validationRule: null, alias: string}
     */
    private static function feature(bool $default, string $description, string $alias): array
    {
        $definition = self::option($default, $description);
        $definition['alias'] = $alias;

        return $definition;
    }

    /**
     * @return array<string, string>
     */
    private static function aliases(): array
    {
        static $aliases = null;

        if ($aliases !== null) {
            return $aliases;
        }

        $aliases = [];
        foreach (self::definitions() as $path => $definition) {
            $alias = $definition['alias'];
            if ($alias === null) {
                continue;
            }

            if (isset($aliases[$alias]) || isset(self::definitions()[$alias])) {
                throw new \LogicException("Duplicate or conflicting configuration alias: {$alias}");
            }

            $aliases[$alias] = $path;
        }

        return $aliases;
    }

    /**
     * @param mixed $value
     * @param array<string, mixed> $definition
     */
    private function setValue(string $path, $value, array $definition): self
    {
        self::validate($path, $value, $definition);
        $this->values[$path] = $value;

        if ($this->onChange !== null) {
            ($this->onChange)();
        }

        return $this;
    }

    /**
     * @param array<string, array<string, mixed>> $definitions
     */
    private function hasChildren(string $path, array $definitions): bool
    {
        $prefix = $path . '.';

        foreach ($definitions as $candidate => $_) {
            if (strpos($candidate, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $value
     * @param array<string, mixed> $definition
     */
    private static function validate(string $path, $value, array $definition): void
    {
        $actualType = gettype($value);
        if ($definition['type'] !== $actualType) {
            throw new \InvalidArgumentException("Expected {$definition['type']}, got {$actualType}");
        }

        switch ($definition['validationRule']) {
            case self::RULE_ABBREVIATION_MAP:
                self::validateAbbreviations($path, $value);
                break;

            case self::RULE_STRING_LIST:
                self::validateStringList($path, $value);
                break;

            case self::RULE_HEADING_LEVELS:
                self::validateHeadingLevels($path, $value);
                break;

            case self::RULE_REGEX_MAP:
                self::validateRegexReplacements($path, $value);
                break;

            case self::RULE_DELIMITER_PAIRS:
                self::validateMathDelimiters($path, $value);
                break;

            case self::RULE_NON_EMPTY:
                if ($value === '') {
                    self::invalid($path, 'must not be empty');
                }
                break;

            case null:
                break;

            default:
                throw new \LogicException("Unknown configuration validation rule: {$definition['validationRule']}");
        }
    }

    private static function validateAbbreviations(string $path, array $abbreviations): void
    {
        foreach ($abbreviations as $abbreviation => $description) {
            if (!is_string($abbreviation) || $abbreviation === '' || !is_string($description)) {
                self::invalid($path, 'expected non-empty string keys and string descriptions');
            }
        }
    }

    private static function validateStringList(string $path, array $values): void
    {
        if (!self::isList($values)) {
            self::invalid($path, 'expected a list of non-empty strings');
        }

        foreach ($values as $value) {
            if (!is_string($value) || $value === '') {
                self::invalid($path, 'expected a list of non-empty strings');
            }
        }
    }

    private static function validateHeadingLevels(string $path, array $levels): void
    {
        if (!self::isList($levels)) {
            self::invalid($path, 'expected a list containing only h1 through h6');
        }

        foreach ($levels as $level) {
            if (!is_string($level) || !isset(self::HEADING_LEVELS[$level])) {
                self::invalid($path, 'expected a list containing only h1 through h6');
            }
        }
    }

    private static function validateRegexReplacements(string $path, array $replacements): void
    {
        foreach ($replacements as $pattern => $replacement) {
            if (
                !is_string($pattern)
                || $pattern === ''
                || !is_string($replacement)
                || @preg_match($pattern, '') === false
            ) {
                self::invalid($path, 'expected valid regular-expression keys and string replacements');
            }
        }
    }

    private static function validateMathDelimiters(string $path, array $delimiters): void
    {
        if (!self::isList($delimiters)) {
            self::invalid($path, 'expected a list of delimiter pairs');
        }

        foreach ($delimiters as $delimiter) {
            if (
                !is_array($delimiter)
                || !isset($delimiter['left'], $delimiter['right'])
                || !is_string($delimiter['left'])
                || !is_string($delimiter['right'])
                || $delimiter['left'] === ''
                || $delimiter['right'] === ''
            ) {
                self::invalid($path, 'expected non-empty string left/right delimiter pairs');
            }
        }
    }

    private static function isList(array $values): bool
    {
        return $values === [] || array_keys($values) === range(0, count($values) - 1);
    }

    private static function invalid(string $path, string $reason): void
    {
        throw new \InvalidArgumentException("Invalid config value for {$path}: {$reason}");
    }
}
