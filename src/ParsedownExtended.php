<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended;

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
    public const VERSION = '2.0.0';
    public const VERSION_PARSEDOWN_REQUIRED = '1.7.4';
    public const VERSION_PARSEDOWN_EXTRA_REQUIRED = '0.8.1';
    public const MIN_PHP_VERSION = '7.4';

    /** @var array $anchorRegister Registry for anchors generated during parsing */
    private array $anchorRegister = [];

    /** @var array $contentsListArray List of contents generated during parsing */
    private array $contentsListArray = [];

    /** @var int $firstHeadLevel The level of the first header parsed */
    private int $firstHeadLevel = 0;

    /** @var string $contentsListString String representation of the table of contents */
    private string $contentsListString = '';

    /** @var callable|null $createAnchorIDCallback Callback function for anchor creation */
    private $createAnchorIDCallback = null;

    /** @var array $config Configuration options */
    private array $config;

    /** @var array $configSchema Schema for validating configuration options */
    private array $configSchema;

    /** @var object|null $configHandler Cached configuration handler */
    private $configHandler = null;

    /** @var bool $legacyMode Flag indicating if legacy compatibility mode is enabled */
    private bool $legacyMode = false;

    /** @var array|null $emojiMap Cached emoji map for emoji replacements */
    private ?array $emojiMap = null;

    /** @var bool $predefinedAbbreviationsAdded Tracks whether predefined abbreviations have been merged */
    private bool $predefinedAbbreviationsAdded = false;

    /** @var array|null $internalHostsSet Cached set of internal hosts for link processing */
    private ?array $internalHostsSet = null;

    /** @var array $internalHosts List of internal hosts loaded from configuration */
    private array $internalHosts = [];

    /** @var array CONFIG_SCHEMA_DEFAULT Default configuration schema */
    private const CONFIG_SCHEMA_DEFAULT = [
        'abbreviations' => [
            'allow_custom' => true,
            'predefined'   => [],
        ],
        'code'      => ['blocks' => true, 'inline' => true],
        'comments'  => true,
        'definition_lists' => true,
        'diagrams' => [
            'enabled' => false,
            'chartjs' => true,
            'mermaid' => true,
        ],
        'emojis' => true,
        'emphasis' => [
            'bold'           => true,
            'italic'         => true,
            'strikethroughs' => true,
            'insertions'     => true,
            'subscript'      => false,
            'superscript'    => false,
            'keystrokes'     => true,
            'mark'           => true,
        ],
        'footnotes' => true,
        'headings' => [
            'allowed_levels' => ['h1','h2','h3','h4','h5','h6'],
            'auto_anchors' => [
                'delimiter'     => '-',
                'lowercase'     => true,
                'replacements'  => [],
                'transliterate' => false,
                'blacklist'     => [],
            ],
            'special_attributes' => true,
        ],
        'images' => true,
        'links' => [
            'email_links' => true,
            'external_links' => [
                'nofollow'           => true,
                'noopener'           => true,
                'noreferrer'         => true,
                'open_in_new_window' => true,
                'internal_hosts'     => [],
            ],
        ],
        'lists' => ['tasks' => true],
        'allow_raw_html' => true,
        'alerts' => [
            'types' => ['note','tip','important','warning','caution'],
            'class' => 'markdown-alert',
        ],
        'math' => [
            'enabled' => false,
            'inline' => [
                'delimiters' => [['left' => '$',  'right' => '$']],
            ],
            'block'  => [
                'delimiters' => [['left' => '$$', 'right' => '$$']],
            ],
        ],
        'quotes' => true,
        'smartypants' => [
            'enabled'             => false,
            'smart_angled_quotes' => true,
            'smart_backticks'     => true,
            'smart_dashes'        => true,
            'smart_ellipses'      => true,
            'smart_quotes'        => true,
            'substitutions' => [
                'ellipses'           => '&hellip;',
                'left_angle_quote'   => '&laquo;',
                'left_double_quote'  => '&ldquo;',
                'left_single_quote'  => '&lsquo;',
                'mdash'              => '&mdash;',
                'ndash'              => '&ndash;',
                'right_angle_quote'  => '&raquo;',
                'right_double_quote' => '&rdquo;',
                'right_single_quote' => '&rsquo;',
            ],
        ],
        'tables'         => ['tablespan' => true],
        'thematic_breaks'=> true,
        'toc' => [
            'levels' => ['h1','h2','h3','h4','h5','h6'],
            'tag'    => '[TOC]',
            'id'     => 'toc',
        ],
        'typographer' => true,
        'references'  => true,
    ];


    /** @var array $PATH_TO_BIT Stores a mapping of file or directory paths to their corresponding bit values. */
    private static array $PATH_TO_BIT   = [];

    /** @var array $BIT_TO_PATH Stores a mapping of bit values to their corresponding file or directory paths. */
    private static array $BIT_TO_PATH   = [];

    /** @var array $FLAT_SCHEMA Stores a flat schema of configuration options for easy access. */
    private static array $FLAT_SCHEMA   = [];

    /** @var int $DEFAULT_BITS Stores the default boolean mask. */
    private static int   $DEFAULT_BITS  = 0;   // default boolean mask

    /** @var array $DEFAULT_PAYLOAD Stores default non-boolean settings for the configuration. */
    private static array $DEFAULT_PAYLOAD = [];

    /** @var bool $COMPILED Indicates whether the schema has been compiled. */
    private static bool  $COMPILED = false;

    /** @var int $features Stores the feature flags for the instance. */
    private int   $features;  // 64‑bit mask of booleans

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

        $this->setLegacyMode();

        // Initialize the configuration schema
        if (!self::$COMPILED) {
            $this->compileSchema();
            self::$COMPILED = true;
        }

        // Initialize features and payload
        $this->features = self::$DEFAULT_BITS;
        $this->payload  = self::$DEFAULT_PAYLOAD;

        // Apply overrides if provided
        if ($overrides) {
            $this->applyOverrides($overrides);
        }


        // Add support for inline types (e.g., special formatting)
        $this->addInlineType('=', 'Marking');
        $this->addInlineType('+', 'Insertions');
        $this->addInlineType('[', 'Keystrokes');
        $this->addInlineType(['\\', '$'], 'MathNotation');
        $this->addInlineType('^', 'Superscript');
        $this->addInlineType('~', 'Subscript');
        $this->addInlineType(':', 'Emojis');
        $this->addInlineType(['<', '>', '-', '.', "'", '"', '`'], 'Smartypants');
        $this->addInlineType(['(', '.', '+', '!', '?'], 'Typographer');

        // Add support for block types (e.g., blocks of content)
        $this->addBlockType(['\\','$'], 'MathNotation');
        $this->addBlockType('>', 'Alert');

        // Reorganize 'SpecialCharacter' to ensure it is processed last in InlineTypes and BlockTypes
        foreach ($this->InlineTypes as &$list) {
            if (($key = array_search('SpecialCharacter', $list)) !== false) {
                unset($list[$key]);
                $list[] = 'SpecialCharacter'; // Append 'SpecialCharacter' at the end
            }
        }

        foreach ($this->BlockTypes as &$list) {
            if (($key = array_search('SpecialCharacter', $list)) !== false) {
                unset($list[$key]);
                $list[] = 'SpecialCharacter'; // Append 'SpecialCharacter' at the end
            }
        }
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


    /**
     * Method setLegacyMode
     *
     * Sets the legacy mode based on the version of Parsedown.
     *
     * @since 1.3.0
     *
     * @return void
     */
    private function setLegacyMode(): void
    {
        $parsedownVersion = preg_replace('/-.*$/', '', \Parsedown::version);

        // Enable legacy mode if Parsedown version is between 1.7.4 and below 1.8.0
        if (version_compare($parsedownVersion, '1.8.0') < 0 && version_compare($parsedownVersion, '1.7.4') >= 0) {
            $this->legacyMode = true;
        }
    }

    // Inline types
    // -------------------------------------------------------------------------

    /**
     * Processes inline code elements.
     *
     * Handles inline code if it is enabled in the configuration settings.
     *
     * @since 0.1.0
     *
     * @param array $Excerpt The portion of text being parsed
     * @return mixed|null The parsed element or null if not processed
     */
    protected function inlineCode($Excerpt)
    {
        $config = $this->config();

        if ($config->get('code') && $config->get('code.inline')) {
            return parent::inlineCode($Excerpt);
        }

        return null;
    }

    /**
     * Processes inline images.
     *
     * Handles inline images if the feature is enabled in the configuration.
     *
     * @since 0.1.0
     *
     * @param array $Excerpt The portion of text being parsed
     * @return mixed|null The parsed image element or null if not processed
     */
    protected function inlineImage($Excerpt)
    {
        $config = $this->config();

        if ($config->get('images')) {
            return parent::inlineImage($Excerpt);
        }

        return null;
    }

    /**
     * Processes inline HTML markup.
     *
     * Parses inline HTML if raw HTML is allowed in the configuration.
     *
     * @since 0.1.0
     *
     * @param array $Excerpt The portion of text being parsed
     * @return mixed|null The parsed HTML markup or null if not allowed
     */
    protected function inlineMarkup($Excerpt)
    {
        $config = $this->config();

        if ($config->get('allow_raw_html')) {
            return parent::inlineMarkup($Excerpt);
        }

        return null;
    }

    /**
     * Processes inline strikethrough elements.
     *
     * Handles inline strikethrough text if the emphasis is enabled in the configuration.
     *
     * @since 0.1.0
     *
     * @param array $Excerpt The portion of text being parsed
     * @return mixed|null The parsed strikethrough or null if not processed
     */
    protected function inlineStrikethrough($Excerpt)
    {
        $config = $this->config();

        if ($config->get('emphasis.strikethroughs') && $config->get('emphasis')) {
            return parent::inlineStrikethrough($Excerpt);
        }

        return null;
    }

    /**
     * Processes inline links.
     *
     * Extends link processing to handle custom link behaviors.
     *
     * @since 0.1.0
     *
     * @param array $Excerpt The portion of text being parsed
     * @return array|null The processed link element or null if not processed
     */
    protected function inlineLink($Excerpt)
    {
        return $this->processLinkElement(parent::inlineLink($Excerpt));
    }

    /**
     * Processes inline URLs.
     *
     * Extends the URL processing to include additional custom behavior, such as modifying the parsed URL element.
     *
     * @since 0.1.0
     *
     * @param array $Excerpt The portion of text being parsed
     * @return array|null The processed URL element or null if not processed
     */
    protected function inlineUrl($Excerpt)
    {
        return $this->processLinkElement(parent::inlineUrl($Excerpt));
    }

    /**
     * Processes inline URL tags.
     *
     * Handles parsing of inline URL tags, adding any custom behavior if needed.
     *
     * @since 0.1.0
     *
     * @param array $Excerpt The portion of text being parsed
     * @return array|null The processed URL tag or null if not processed
     */
    protected function inlineUrlTag($Excerpt)
    {
        return $this->processLinkElement(parent::inlineUrlTag($Excerpt));
    }



    /**
     * Processes inline email tags.
     *
     * Handles email links if the feature is enabled in the configuration.
     *
     * @since 0.1.0
     *
     * @param array $Excerpt The portion of text being parsed
     * @return mixed|null The parsed email tag or null if links are disabled
     */
    protected function inlineEmailTag($Excerpt)
    {
        $config = $this->config();

        if (!$config->get('links') || !$config->get('links.email_links')) {
            return null;
        }

        $Excerpt = parent::inlineEmailTag($Excerpt);

        if (isset($Excerpt['element']['attributes']['href'])) {
            $Excerpt['element']['attributes']['target'] = '_blank';
        }

        return $Excerpt;
    }


    /**
     * Processes link elements to add behavior control attributes.
     *
     * Extends parsed Markdown link elements to include attributes such as `nofollow`, `noopener`, and `noreferrer`
     * based on the configuration settings, particularly for external links. This helps control search engine indexing,
     * external page behavior, and referrer privacy.
     *
     * @since 1.3.0
     *
     * @param array $Excerpt The portion of text representing the link element.
     * @return array|null Modified link element with added attributes or null if the link is disallowed.
     */
    protected function processLinkElement($Excerpt)
    {
        $config = $this->config();

        // Fast fail for missing config or href
        if (!$config->get('links') || !$Excerpt || empty($Excerpt['element']['attributes']['href'])) {
            return null;
        }

        $href = $Excerpt['element']['attributes']['href'];

        // Only process external links if enabled
        if ($this->isExternalLink($href)) {
            if (!$config->get('links.external_links')) {
                return null;
            }

            // Only build rel if needed
            $rel = [];

            if ($config->get('links.external_links.nofollow')) {
                $rel[] = 'nofollow';
            }
            if ($config->get('links.external_links.noopener')) {
                $rel[] = 'noopener';
            }
            if ($config->get('links.external_links.noreferrer')) {
                $rel[] = 'noreferrer';
            }

            if ($config->get('links.external_links.open_in_new_window')) {
                $Excerpt['element']['attributes']['target'] = '_blank';
            }

            if ($rel) {
                $existing = $Excerpt['element']['attributes']['rel'] ?? '';
                $relString = trim($existing . ' ' . implode(' ', $rel));
                $Excerpt['element']['attributes']['rel'] = $relString;
            }
        }

        return $Excerpt;
    }

    /**
     * Determines if a given link is an external link.
     *
     * Checks if the link is either protocol-relative (starts with `//`) or absolute (`http://` or `https://`)
     * and if the host differs from the current server's host. It also checks against a list of internal hosts to identify external links.
     *
     * @since 1.3.0
     *
     * @param string $href The URL to check.
     * @return bool Returns true if the link is external, false otherwise.
     */
    private function isExternalLink(string $href): bool
    {
        // Early return for relative URLs (not starting with http(s):// or //)
        $protocolRelative = strncmp($href, '//', 2);
        if (
            $protocolRelative !== 0 &&
            stripos($href, 'http://') !== 0 &&
            stripos($href, 'https://') !== 0
        ) {
            return false;
        }

        // Normalize protocol-relative URLs for parse_url
        $url = ($protocolRelative === 0) ? 'http:' . $href : $href;
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) {
            return false;
        }

        // Normalize host (lowercase, strip www.)
        $host = strtolower($host);
        if (strpos($host, 'www.') === 0) {
            $host = substr($host, 4);
        }

        // Normalize current host
        $currentHost = $_SERVER['HTTP_HOST'] ?? '';
        $currentHost = strtolower($currentHost);
        if (strpos($currentHost, 'www.') === 0) {
            $currentHost = substr($currentHost, 4);
        }
        if ($host === $currentHost) {
            return false;
        }

        // Use cache for internal hosts set
        if ($this->internalHostsSet === null) {
            $this->internalHostsSet = [];
            $this->internalHosts = $this->config()->get('links.external_links.internal_hosts');
            foreach ($this->internalHosts as $h) {
                $h = strtolower($h);
                if (strpos($h, 'www.') === 0) {
                    $h = substr($h, 4);
                }
                $this->internalHostsSet[$h] = true;
            }
        }

        return !isset($this->internalHostsSet[$host]);
    }

    /**
     * Processes inline emphasis elements.
     *
     * Handles inline emphasis (like bold or italics) if enabled in the configuration.
     *
     * @since 0.1.0
     *
     * @param array $Excerpt The portion of text being parsed
     * @return array|null The parsed emphasis or null if not processed
     */
    protected function inlineEmphasis($Excerpt)
    {
        $config = $this->config();

        if (!$config->get('emphasis') || !isset($Excerpt['text'][1])) {
            return null; // If emphasis is disabled or the excerpt is too short, return null
        }

        $marker = $Excerpt['text'][0]; // Extract the marker character ('*', '_', etc.)

        // Check if the text matches bold emphasis using the marker
        if ($config->get('emphasis.bold') && preg_match($this->StrongRegex[$marker], $Excerpt['text'], $matches)) {
            $emphasis = 'strong'; // Use 'strong' for bold text
        }
        // Check if the text matches italic emphasis using the marker
        elseif ($config->get('emphasis.italic') && preg_match($this->EmRegex[$marker], $Excerpt['text'], $matches)) {
            $emphasis = 'em'; // Use 'em' for italic text
        } else {
            return null; // No valid emphasis match found
        }

        // Return the parsed emphasis element
        return [
            'extent' => strlen($matches[0]), // Length of the matched emphasis text
            'element' => [
                'name' => $emphasis, // 'strong' for bold or 'em' for italics
                'handler' => 'line', // Handler for further inline processing
                'text' => $matches[1], // The emphasized content
            ],
        ];
    }

    /**
     * Processes inline marking elements.
     *
     * Handles inline marking by using double equal signs (`==text==`). This will convert the marked text
     * into an HTML `<mark>` tag if the feature is enabled in the configuration.
     *
     * @since 1.2.0
     *
     * @param array $Excerpt The portion of text being parsed to identify marking.
     * @return array|null The parsed marking element or null if marking is disabled or not applicable.
     */
    protected function inlineMarking(array $Excerpt): ?array
    {
        $config = $this->config();

        // Check if marking is enabled in the configuration settings
        if (!$config->get('emphasis.mark') || !$config->get('emphasis')) {
            return null; // Return null if marking or emphasis is disabled
        }

        // Early return if the excerpt does not start with two '=' characters
        if (!isset($Excerpt['text'][1]) || $Excerpt['text'][1] !== '=') {
            return null;
        }

        // Match the double equal signs for marking (`==text==`) using regex
        if (preg_match('/^==((?:\\\\\=|[^=]|=[^=]*=)+?)==(?!=)/s', $Excerpt['text'], $matches)) {
            // Return the parsed marking element
            return [
                'extent' => strlen($matches[0]), // The length of the matched marking text
                'element' => [
                    'name' => 'mark', // The HTML tag used for marking
                    'text' => $matches[1], // The content inside the marking
                ],
            ];
        }

        return null; // If no match is found, return null
    }

    /**
     * Processes inline insertion elements.
     *
     * Handles inline insertions denoted by double plus signs (`++text++`). If enabled in the configuration,
     * this will convert the marked text into an HTML `<ins>` tag, which is commonly used to indicate additions.
     *
     * @since 1.2.0
     *
     * @param array $Excerpt The portion of text being parsed to identify insertions.
     * @return array|null The parsed insertion element or null if insertions are disabled or not applicable.
     */
    protected function inlineInsertions(array $Excerpt): ?array
    {
        $config = $this->config();

        // Check if insertions are enabled in the configuration settings
        if (!$config->get('emphasis.insertions') || !$config->get('emphasis')) {
            return null; // Return null if insertions or general emphasis is disabled
        }

        // Early return if the excerpt does not start with two '+' characters
        if (!isset($Excerpt['text'][1]) || $Excerpt['text'][1] !== '+') {
            return null;
        }

        // Match the double plus signs for insertions (`++text++`) using regex
        if (preg_match('/^\+\+((?:\\\\\+|[^\+]|\+[^\+]*\+)+?)\+\+(?!\+)/s', $Excerpt['text'], $matches)) {
            // Return the parsed insertion element
            return [
                'extent' => strlen($matches[0]), // The length of the matched insertion text
                'element' => [
                    'name' => 'ins', // The HTML tag used for insertions
                    'text' => $matches[1], // The content inside the insertion
                ],
            ];
        }

        return null; // If no match is found, return null
    }

    /**
     * Processes inline keystroke elements.
     *
     * Handles inline keystrokes denoted by double square brackets (`[[text]]`). If enabled in the configuration,
     * this will convert the enclosed text into an HTML `<kbd>` tag, which is typically used to represent user input or keystrokes.
     *
     * @since 1.0.0
     *
     * @param array $Excerpt The portion of text being parsed to identify keystrokes.
     * @return array|null The parsed keystroke element or null if keystrokes are disabled or not applicable.
     */
    protected function inlineKeystrokes(array $Excerpt): ?array
    {
        $config = $this->config();

        // Check if keystrokes are enabled in the configuration settings
        if (!$config->get('emphasis.keystrokes') || !$config->get('emphasis')) {
            return null; // Return null if keystrokes or general emphasis is disabled
        }

        // Early return if the excerpt does not start with two '[' characters
        if (!isset($Excerpt['text'][1]) || '[' !== $Excerpt['text'][1]) {
            return null;
        }

        // Match the double square brackets for keystrokes (`[[text]]`) using regex
        if (preg_match('/^(?<!\[)\[\[([^\[\]]*|[\[\]])\]\](?!\])/s', $Excerpt['text'], $matches)) {
            // Return the parsed keystroke element
            return [
                'extent' => strlen($matches[0]), // The length of the matched keystroke text
                'element' => [
                    'name' => 'kbd', // The HTML tag used for keystrokes
                    'text' => $matches[1], // The content inside the keystroke brackets
                ],
            ];
        }

        return null; // If no match is found, return null
    }


    /**
     * Processes inline superscript elements.
     *
     * Handles inline superscript denoted by a caret symbol (`^text^`). If enabled in the configuration,
     * this will convert the marked text into an HTML `<sup>` tag, which is typically used for superscripts in text.
     *
     * @since 1.0.0
     *
     * @param array $Excerpt The portion of text being parsed to identify superscript.
     * @return array|null The parsed superscript element or null if superscript is disabled or not applicable.
     */
    protected function inlineSuperscript(array $Excerpt): ?array
    {
        $config = $this->config();

        // Check if superscript is enabled in the configuration settings
        if (!$config->get('emphasis.superscript') || !$config->get('emphasis')) {
            return null; // Return null if superscript or general emphasis is disabled
        }

        // Early return if no text follows the caret
        if (!isset($Excerpt['text'][1]) || '^' === $Excerpt['text'][1]) {
            return null;
        }

        // Match the caret symbols for superscript (`^text^`) using regex
        if (preg_match('/^\^((?:\\\\\\^|[^\^]|\^[^\^]+?\^\^)+?)\^(?!\^)/s', $Excerpt['text'], $matches)) {
            // Return the parsed superscript element
            return [
                'extent' => strlen($matches[0]), // The length of the matched superscript text
                'element' => [
                    'name' => 'sup', // The HTML tag used for superscript
                    'text' => $matches[1], // The content inside the superscript markers
                ],
            ];
        }

        return null; // If no match is found, return null
    }


    /**
     * Processes inline subscript elements.
     *
     * Handles inline subscript denoted by a tilde (`~text~`). If enabled in the configuration,
     * this will convert the marked text into an HTML `<sub>` tag, which is typically used for subscripts in text.
     *
     * @since 1.0.0
     *
     * @param array $Excerpt The portion of text being parsed to identify subscript.
     * @return array|null The parsed subscript element or null if subscript is disabled or not applicable.
     */
    protected function inlineSubscript(array $Excerpt): ?array
    {
        $config = $this->config();

        // Check if subscript is enabled in the configuration settings
        if (!$config->get('emphasis.subscript') || !$config->get('emphasis')) {
            return null; // Return null if subscript or general emphasis is disabled
        }

        // Early return if no text follows the tilde or the next character is a tilde
        if (!isset($Excerpt['text'][1]) || '~' === $Excerpt['text'][1]) {
            return null;
        }

        // Match the tilde symbols for subscript (`~text~`) using regex
        if (preg_match('/^~((?:\\\\~|[^~]|~~[^~]*~~)+?)~(?!~)/s', $Excerpt['text'], $matches)) {
            // Return the parsed subscript element
            return [
                'extent' => strlen($matches[0]), // The length of the matched subscript text
                'element' => [
                    'name' => 'sub', // The HTML tag used for subscript
                    'text' => $matches[1], // The content inside the subscript markers
                ],
            ];
        }

        return null; // If no match is found, return null
    }


    /**
     * Processes inline math notation elements.
     *
     * Handles inline math notation using specific delimiters (e.g., `$...$`, `\\(...\\)`). If enabled in the configuration,
     * this function matches math notation within the specified delimiters and processes it accordingly.
     *
     * @since 1.1.2
     *
     * @param array $Excerpt The portion of text being parsed to identify math notation.
     * @return array|null The parsed math notation element or null if math parsing is disabled or not applicable.
     */
    protected function inlineMathNotation($Excerpt)
    {
        $config = $this->config();

        // Check if parsing of math notation is enabled in the configuration settings
        if (!$config->get('math') || !$config->get('math.inline')) {
            return null; // Return null if math or inline math is disabled
        }

        // Check if the excerpt has enough characters to proceed
        if (!isset($Excerpt['text'][1])) {
            return null; // Return null if there is insufficient text for math notation
        }

        // Check if there is whitespace before the excerpt (ensures math is not in the middle of a word)
        if ($Excerpt['before'] !== '' && preg_match('/\s/', $Excerpt['before']) === 0) {
            return null; // Return null if the math notation is not preceded by whitespace
        }

        // Iterate through the inline math delimiters (e.g., `$...$`, `\\(...\\)`)
        $delimiters = $config->get('math.inline.delimiters');
        foreach ($delimiters as $dConfig) {
            $leftMarker = preg_quote($dConfig['left'], '/');  // Escape the left delimiter for use in regex
            $rightMarker = preg_quote($dConfig['right'], '/'); // Escape the right delimiter for use in regex

            // Create the regex pattern for matching math notation
            if ($dConfig['left'][0] === '\\' || strlen($dConfig['left']) > 1) {
                $regex = '/^(?<!\S)' . $leftMarker . '(?![\r\n])((?:\\\\' . $rightMarker . '|\\\\' . $leftMarker . '|[^\r\n])+?)' . $rightMarker . '(?!\w)/s';
            } else {
                $regex = '/^(?<!\S)' . $leftMarker . '(?![\r\n])((?:\\\\' . $rightMarker . '|\\\\' . $leftMarker . '|[^' . $rightMarker . '\r\n])+?)' . $rightMarker . '(?!\w)/s';
            }

            // Match the regular expression pattern against the excerpt
            if (preg_match($regex, $Excerpt['text'], $matches)) {
                // Return the parsed math element
                return [
                    'extent' => strlen($matches[0]), // The length of the matched math notation
                    'element' => [
                        'text' => $matches[0], // The matched math content
                    ],
                ];
            }
        }

        return null; // If no match is found, return null
    }


    /**
     * Processes inline escape sequences.
     *
     * Handles escape sequences to allow special characters to be rendered as literals instead of being interpreted.
     * Specifically, if a character is preceded by a backslash, it is treated as an escaped character.
     * Additionally, it ensures that math delimiters are not mistakenly escaped.
     *
     * @since 0.1.0
     *
     * @param array $Excerpt The portion of text being parsed to identify escape sequences.
     * @return array|null The parsed escape sequence element or null if no valid escape sequence is found.
     */
    protected function inlineEscapeSequence($Excerpt)
    {
        $config = $this->config();

        // If math is enabled, check for any inline math delimiters that might need special handling
        if ($config->get('math')) {
            $delimiters = $config->get('math.inline.delimiters');
            foreach ($delimiters as $dConfig) {
                $leftMarker = preg_quote($dConfig['left'], '/');  // Escape the left delimiter for use in regex
                $rightMarker = preg_quote($dConfig['right'], '/'); // Escape the right delimiter for use in regex

                // Create the regex pattern for matching math notation
                if ($dConfig['left'][0] === '\\' || strlen($dConfig['left']) > 1) {
                    $regex = '/^(?<!\S)' . $leftMarker . '(?![\r\n])((?:\\\\' . $rightMarker . '|\\\\' . $leftMarker . '|[^\r\n])+?)' . $rightMarker . '(?!\w)/s';
                } else {
                    $regex = '/^(?<!\S)' . $leftMarker . '(?![\r\n])((?:\\\\' . $rightMarker . '|\\\\' . $leftMarker . '|[^' . $rightMarker . '\r\n])+?)' . $rightMarker . '(?!\w)/s';
                }

                // If a math notation match is found, return null as it's not an escape sequence
                if (preg_match($regex, $Excerpt['text'])) {
                    return null;
                }
            }
        }

        // Check if the character following the backslash is a special character that should be escaped
        if (isset($Excerpt['text'][1]) && in_array($Excerpt['text'][1], $this->specialCharacters)) {
            // Return the escaped character
            return [
                'markup' => $Excerpt['text'][1], // The character to be escaped
                'extent' => 2, // The length of the escape sequence (backslash + character)
            ];
        }

        // If no valid escape sequence is found, return null
        return null;
    }


    /**
     * Processes inline typographic substitutions.
     *
     * This function handles typographic improvements, such as replacing plain text with their typographic equivalents.
     * It processes symbols like (c) to ©, (r) to ®, and smart ellipses based on the user's configuration.
     * This is particularly useful for enhancing readability by applying typographer rules.
     *
     * @since 1.0.1
     *
     * @param array $Excerpt The portion of text being parsed for typographic substitutions.
     * @return array|null The parsed typographic substitutions or null if the typographer feature is disabled.
     */
    protected function inlineTypographer(array $Excerpt): ?array
    {
        $config = $this->config();

        if (
            !$config->get('typographer') ||
            empty($Excerpt['text'])
        ) {
            return null;
        }

        static $substitutions = null;
        static $lastEllipsesKey = null;

        // Only update ellipses if config changes
        $ellipsesKey = $config->get('smartypants') && $config->get('smartypants.smart_ellipses')
            ? $config->get('smartypants.substitutions.ellipses')
            : '...';

        if ($substitutions === null || $ellipsesKey !== $lastEllipsesKey) {
            $lastEllipsesKey = $ellipsesKey;
            $ellipses = $ellipsesKey === '...' ? '...' : html_entity_decode($ellipsesKey);

            $substitutions = [
                '/\(c\)/i'      => '©',
                '/\(r\)/i'      => '®',
                '/\(tm\)/i'     => '™',
                '/\(p\)/i'      => '¶',
                '/\+-/i'        => '±',
                '/\!\.{3,}/i'   => '!..',
                '/\?\.{3,}/i'   => '?..',
                '/\.{2,}/i'     => $ellipses,
            ];
        }

        $result = preg_replace(array_keys($substitutions), array_values($substitutions), $Excerpt['text'], -1, $count);

        if ($count > 0 && $result !== $Excerpt['text']) {
            return [
                'extent' => strlen($Excerpt['text']),
                'element' => [
                    'text' => $result,
                ],
            ];
        }

        return null;
    }


    /**
     * Processes inline Smartypants substitutions.
     *
     * This function handles typographic improvements to the text, such as converting straight quotes to curly quotes,
     * converting double angle quotes, converting dashes into em or en dashes, and ellipses into the proper character.
     * These changes enhance readability and align text formatting with common typographic standards.
     *
     * @since 1.0.0
     *
     * @param array $Excerpt The portion of text being parsed for Smartypants substitutions.
     * @return array|null The parsed Smartypants substitution or null if Smartypants is disabled.
     */
    protected function inlineSmartypants($Excerpt)
    {
        $config = $this->config();

        // Check if Smartypants is enabled in the configuration settings
        if (!$config->get('smartypants')) {
            return null; // Return null if Smartypants is disabled
        }

        // Substitutions: Load the characters to use for the specific Smartypants transformations
        $substitutions = [
            'left_double_quote' => html_entity_decode($config->get('smartypants.substitutions.left_double_quote')),
            'right_double_quote' => html_entity_decode($config->get('smartypants.substitutions.right_double_quote')),
            'left_single_quote' => html_entity_decode($config->get('smartypants.substitutions.left_single_quote')),
            'right_single_quote' => html_entity_decode($config->get('smartypants.substitutions.right_single_quote')),
            'left_angle_quote' => html_entity_decode($config->get('smartypants.substitutions.left_angle_quote')),
            'right_angle_quote' => html_entity_decode($config->get('smartypants.substitutions.right_angle_quote')),
            'mdash' => html_entity_decode($config->get('smartypants.substitutions.mdash')),
            'ndash' => html_entity_decode($config->get('smartypants.substitutions.ndash')),
            'ellipses' => html_entity_decode($config->get('smartypants.substitutions.ellipses')),
        ];

        $text = $Excerpt['text'];
        $first = $text[0] ?? '';

        // ``like this''
        if ('`' === $first && $config->get('smartypants.smart_backticks')) {
            if (preg_match('/^``(?!\s)([^"\'`]+)\'\'/i', $text, $matches)) {
                if (strlen(trim($Excerpt['before'])) > 0) {
                    return null;
                }

                return [
                    'extent' => strlen($matches[0]),
                    'element' => [
                        'text' => $substitutions['left_double_quote'] . $matches[1] . $substitutions['right_double_quote'],
                    ],
                ];
            }
        }

        // "like this" or 'like this'
        if (('"' === $first || "'" === $first) && $config->get('smartypants.smart_quotes')) {
            if (preg_match('/^(\")(?!\s)([^\"]+)\"|^(?<!\w)(\')(?!\s)([^\']+)\'/i', $text, $matches)) {
                if (strlen(trim($Excerpt['before'])) > 0) {
                    return null;
                }

                if (isset($matches[3]) && $matches[3] === "'") {
                    return [
                        'extent' => strlen($matches[0]),
                        'element' => [
                            'text' => $substitutions['left_single_quote'] . $matches[4] . $substitutions['right_single_quote'],
                        ],
                    ];
                }

                return [
                    'extent' => strlen($matches[0]),
                    'element' => [
                        'text' => $substitutions['left_double_quote'] . $matches[2] . $substitutions['right_double_quote'],
                    ],
                ];
            }
        }

        // <<like this>>
        if ('<' === $first && $config->get('smartypants.smart_angled_quotes')) {
            if (preg_match('/^<{2}(?!\s)([^<>]+)>{2}/i', $text, $matches)) {
                if (strlen(trim($Excerpt['before'])) > 0) {
                    return null;
                }

                return [
                    'extent' => strlen($matches[0]),
                    'element' => [
                        'text' => $substitutions['left_angle_quote'] . $matches[1] . $substitutions['right_angle_quote'],
                    ],
                ];
            }
        }

        // -- or ---
        if ('-' === $first && $config->get('smartypants.smart_dashes')) {
            if (preg_match('/^(-{2,3})(?!-)/', $text, $matches)) {
                if ('---' === $matches[1]) {
                    return [
                        'extent' => strlen($matches[1]),
                        'element' => [
                            'text' => $substitutions['mdash'],
                        ],
                    ];
                }

                if ('--' === $matches[1]) {
                    return [
                        'extent' => 2,
                        'element' => [
                            'text' => $substitutions['ndash'],
                        ],
                    ];
                }
            }
        }

        // ...
        if ('.' === $first && $config->get('smartypants.smart_ellipses')) {
            if (preg_match('/^(?<!\.)(\.{3})(?!\.)/i', $text, $matches)) {
                return [
                    'extent' => strlen($matches[0]),
                    'element' => [
                        'text' => $substitutions['ellipses'],
                    ],
                ];
            }
        }

        return null;
    }


    /**
     * Processes inline emoji replacements.
     *
     * This function handles the conversion of text-based emoji shortcuts (e.g., `:smile:`) to their corresponding emoji characters (e.g., 😄).
     * Emojis are replaced based on a predefined emoji map if the emoji feature is enabled in the configuration.
     *
     * @since 1.0.0
     *
     * @param array $Excerpt The portion of text being parsed to identify emoji codes.
     * @return array|null The parsed emoji element or null if emojis are disabled or no match is found.
     */
    protected function inlineEmojis(array $Excerpt): ?array
    {
        // Check if emoji processing is enabled in the configuration settings
        if (!$this->config()->get('emojis')) {
            return null; // Return null if emoji replacement is disabled
        }

        // Early return if there is no closing ':' to form an emoji code
        if (!isset($Excerpt['text'][1]) || false === strpos($Excerpt['text'], ':', 1)) {
            return null;
        }

        // Check for an emoji code before loading the large map
        if (!preg_match('/(?<=\s|^):([a-zA-Z0-9_]+):(?=\s|$)/', $Excerpt['text'], $matches) || !preg_match('/^(\s|)$/', $Excerpt['before'])) {
            return null;
        }

        // Lazily load emoji map only once
        if ($this->emojiMap === null) {
            $this->emojiMap = [
                "grinning_face" => "😀", "grinning_face_with_big_eyes" => "😃", "grinning_face_with_smiling_eyes" => "😄", "beaming_face_with_smiling_eyes" => "😁",
                "grinning_squinting_face" => "😆", "grinning_face_with_sweat" => "😅", "rolling_on_the_floor_laughing" => "🤣", "face_with_tears_of_joy" => "😂",
                "slightly_smiling_face" => "🙂", "upside_down_face" => "🙃", "melting_face" => "🫠", "winking_face" => "😉",
                "smiling_face_with_smiling_eyes" => "😊", "smiling_face_with_halo" => "😇", "smiling_face_with_hearts" => "🥰", "smiling_face_with_heart_eyes" => "😍",
                "star_struck" => "🤩", "face_blowing_a_kiss" => "😘", "kissing_face" => "😗", "smiling_face" => "☺️",
                "kissing_face_with_closed_eyes" => "😚", "kissing_face_with_smiling_eyes" => "😙", "smiling_face_with_tear" => "🥲", "face_savoring_food" => "😋",
                "face_with_tongue" => "😛", "winking_face_with_tongue" => "😜", "zany_face" => "🤪", "squinting_face_with_tongue" => "😝",
                "money_mouth_face" => "🤑", "smiling_face_with_open_hands" => "🤗", "face_with_hand_over_mouth" => "🤭", "face_with_open_eyes_and_hand_over_mouth" => "🫢",
                "face_with_peeking_eye" => "🫣", "shushing_face" => "🤫", "thinking_face" => "🤔", "saluting_face" => "🫡",
                "zipper_mouth_face" => "🤐", "face_with_raised_eyebrow" => "🤨", "neutral_face" => "😐", "expressionless_face" => "😑",
                "face_without_mouth" => "😶", "dotted_line_face" => "🫥", "face_in_clouds" => "😶‍🌫️", "smirking_face" => "😏",
                "unamused_face" => "😒", "face_with_rolling_eyes" => "🙄", "grimacing_face" => "😬", "face_exhaling" => "😮‍💨",
                "lying_face" => "🤥", "shaking_face" => "🫨", "head_shaking_horizontally" => "🙂‍↔️", "head_shaking_vertically" => "🙂‍↕️",
                "relieved_face" => "😌", "pensive_face" => "😔", "sleepy_face" => "😪", "drooling_face" => "🤤",
                "sleeping_face" => "😴", "face_with_bags_under_eyes" => "🫩", "face_with_medical_mask" => "😷", "face_with_thermometer" => "🤒",
                "face_with_head_bandage" => "🤕", "nauseated_face" => "🤢", "face_vomiting" => "🤮", "sneezing_face" => "🤧",
                "hot_face" => "🥵", "cold_face" => "🥶", "woozy_face" => "🥴", "face_with_crossed_out_eyes" => "😵",
                "face_with_spiral_eyes" => "😵‍💫", "exploding_head" => "🤯", "cowboy_hat_face" => "🤠", "partying_face" => "🥳",
                "disguised_face" => "🥸", "smiling_face_with_sunglasses" => "😎", "nerd_face" => "🤓", "face_with_monocle" => "🧐",
                "confused_face" => "😕", "face_with_diagonal_mouth" => "🫤", "worried_face" => "😟", "slightly_frowning_face" => "🙁",
                "frowning_face" => "☹️", "face_with_open_mouth" => "😮", "hushed_face" => "😯", "astonished_face" => "😲",
                "flushed_face" => "😳", "pleading_face" => "🥺", "face_holding_back_tears" => "🥹", "frowning_face_with_open_mouth" => "😦",
                "anguished_face" => "😧", "fearful_face" => "😨", "anxious_face_with_sweat" => "😰", "sad_but_relieved_face" => "😥",
                "crying_face" => "😢", "loudly_crying_face" => "😭", "face_screaming_in_fear" => "😱", "confounded_face" => "😖",
                "persevering_face" => "😣", "disappointed_face" => "😞", "downcast_face_with_sweat" => "😓", "weary_face" => "😩",
                "tired_face" => "😫", "yawning_face" => "🥱", "face_with_steam_from_nose" => "😤", "enraged_face" => "😡",
                "angry_face" => "😠", "face_with_symbols_on_mouth" => "🤬", "smiling_face_with_horns" => "😈", "angry_face_with_horns" => "👿",
                "skull" => "💀", "skull_and_crossbones" => "☠️", "pile_of_poo" => "💩", "clown_face" => "🤡",
                "ogre" => "👹", "goblin" => "👺", "ghost" => "👻", "alien" => "👽",
                "alien_monster" => "👾", "robot" => "🤖", "grinning_cat" => "😺", "grinning_cat_with_smiling_eyes" => "😸",
                "cat_with_tears_of_joy" => "😹", "smiling_cat_with_heart_eyes" => "😻", "cat_with_wry_smile" => "😼", "kissing_cat" => "😽",
                "weary_cat" => "🙀", "crying_cat" => "😿", "pouting_cat" => "😾", "see_no_evil_monkey" => "🙈",
                "hear_no_evil_monkey" => "🙉", "speak_no_evil_monkey" => "🙊", "love_letter" => "💌", "heart_with_arrow" => "💘",
                "heart_with_ribbon" => "💝", "sparkling_heart" => "💖", "growing_heart" => "💗", "beating_heart" => "💓",
                "revolving_hearts" => "💞", "two_hearts" => "💕", "heart_decoration" => "💟", "heart_exclamation" => "❣️",
                "broken_heart" => "💔", "heart_on_fire" => "❤️‍🔥", "mending_heart" => "❤️‍🩹", "red_heart" => "❤️",
                "pink_heart" => "🩷", "orange_heart" => "🧡", "yellow_heart" => "💛", "green_heart" => "💚",
                "blue_heart" => "💙", "light_blue_heart" => "🩵", "purple_heart" => "💜", "brown_heart" => "🤎",
                "black_heart" => "🖤", "grey_heart" => "🩶", "white_heart" => "🤍", "kiss_mark" => "💋",
                "hundred_points" => "💯", "anger_symbol" => "💢", "collision" => "💥", "dizzy" => "💫",
                "sweat_droplets" => "💦", "dashing_away" => "💨", "hole" => "🕳️", "speech_balloon" => "💬",
                "eye_in_speech_bubble" => "👁️‍🗨️", "left_speech_bubble" => "🗨️", "right_anger_bubble" => "🗯️", "thought_balloon" => "💭",
                "zzz" => "💤", "waving_hand" => "👋", "raised_back_of_hand" => "🤚", "hand_with_fingers_splayed" => "🖐️",
                "raised_hand" => "✋", "vulcan_salute" => "🖖", "rightwards_hand" => "🫱", "leftwards_hand" => "🫲",
                "palm_down_hand" => "🫳", "palm_up_hand" => "🫴", "leftwards_pushing_hand" => "🫷", "rightwards_pushing_hand" => "🫸",
                "ok_hand" => "👌", "pinched_fingers" => "🤌", "pinching_hand" => "🤏", "victory_hand" => "✌️",
                "crossed_fingers" => "🤞", "hand_with_index_finger_and_thumb_crossed" => "🫰", "love_you_gesture" => "🤟", "sign_of_the_horns" => "🤘",
                "call_me_hand" => "🤙", "backhand_index_pointing_left" => "👈", "backhand_index_pointing_right" => "👉", "backhand_index_pointing_up" => "👆",
                "middle_finger" => "🖕", "backhand_index_pointing_down" => "👇", "index_pointing_up" => "☝️", "index_pointing_at_the_viewer" => "🫵",
                "thumbs_up" => "👍", "thumbs_down" => "👎", "raised_fist" => "✊", "oncoming_fist" => "👊",
                "left_facing_fist" => "🤛", "right_facing_fist" => "🤜", "clapping_hands" => "👏", "raising_hands" => "🙌",
                "heart_hands" => "🫶", "open_hands" => "👐", "palms_up_together" => "🤲", "handshake" => "🤝",
                "folded_hands" => "🙏", "writing_hand" => "✍️", "nail_polish" => "💅", "selfie" => "🤳",
                "flexed_biceps" => "💪", "mechanical_arm" => "🦾", "mechanical_leg" => "🦿", "leg" => "🦵",
                "foot" => "🦶", "ear" => "👂", "ear_with_hearing_aid" => "🦻", "nose" => "👃",
                "brain" => "🧠", "anatomical_heart" => "🫀", "lungs" => "🫁", "tooth" => "🦷",
                "bone" => "🦴", "eyes" => "👀", "eye" => "👁️", "tongue" => "👅",
                "mouth" => "👄", "biting_lip" => "🫦", "baby" => "👶", "child" => "🧒",
                "boy" => "👦", "girl" => "👧", "person" => "🧑", "person_blond_hair" => "👱",
                "man" => "👨", "person_beard" => "🧔", "man_beard" => "🧔‍♂️", "woman_beard" => "🧔‍♀️",
                "man_red_hair" => "👨‍🦰", "man_curly_hair" => "👨‍🦱", "man_white_hair" => "👨‍🦳", "man_bald" => "👨‍🦲",
                "woman" => "👩", "woman_red_hair" => "👩‍🦰", "person_red_hair" => "🧑‍🦰", "woman_curly_hair" => "👩‍🦱",
                "person_curly_hair" => "🧑‍🦱", "woman_white_hair" => "👩‍🦳", "person_white_hair" => "🧑‍🦳", "woman_bald" => "👩‍🦲",
                "person_bald" => "🧑‍🦲", "woman_blond_hair" => "👱‍♀️", "man_blond_hair" => "👱‍♂️", "older_person" => "🧓",
                "old_man" => "👴", "old_woman" => "👵", "person_frowning" => "🙍", "man_frowning" => "🙍‍♂️",
                "woman_frowning" => "🙍‍♀️", "person_pouting" => "🙎", "man_pouting" => "🙎‍♂️", "woman_pouting" => "🙎‍♀️",
                "person_gesturing_no" => "🙅", "man_gesturing_no" => "🙅‍♂️", "woman_gesturing_no" => "🙅‍♀️", "person_gesturing_ok" => "🙆",
                "man_gesturing_ok" => "🙆‍♂️", "woman_gesturing_ok" => "🙆‍♀️", "person_tipping_hand" => "💁", "man_tipping_hand" => "💁‍♂️",
                "woman_tipping_hand" => "💁‍♀️", "person_raising_hand" => "🙋", "man_raising_hand" => "🙋‍♂️", "woman_raising_hand" => "🙋‍♀️",
                "deaf_person" => "🧏", "deaf_man" => "🧏‍♂️", "deaf_woman" => "🧏‍♀️", "person_bowing" => "🙇",
                "man_bowing" => "🙇‍♂️", "woman_bowing" => "🙇‍♀️", "person_facepalming" => "🤦", "man_facepalming" => "🤦‍♂️",
                "woman_facepalming" => "🤦‍♀️", "person_shrugging" => "🤷", "man_shrugging" => "🤷‍♂️", "woman_shrugging" => "🤷‍♀️",
                "health_worker" => "🧑‍⚕️", "man_health_worker" => "👨‍⚕️", "woman_health_worker" => "👩‍⚕️", "student" => "🧑‍🎓",
                "man_student" => "👨‍🎓", "woman_student" => "👩‍🎓", "teacher" => "🧑‍🏫", "man_teacher" => "👨‍🏫",
                "woman_teacher" => "👩‍🏫", "judge" => "🧑‍⚖️", "man_judge" => "👨‍⚖️", "woman_judge" => "👩‍⚖️",
                "farmer" => "🧑‍🌾", "man_farmer" => "👨‍🌾", "woman_farmer" => "👩‍🌾", "cook" => "🧑‍🍳",
                "man_cook" => "👨‍🍳", "woman_cook" => "👩‍🍳", "mechanic" => "🧑‍🔧", "man_mechanic" => "👨‍🔧",
                "woman_mechanic" => "👩‍🔧", "factory_worker" => "🧑‍🏭", "man_factory_worker" => "👨‍🏭", "woman_factory_worker" => "👩‍🏭",
                "office_worker" => "🧑‍💼", "man_office_worker" => "👨‍💼", "woman_office_worker" => "👩‍💼", "scientist" => "🧑‍🔬",
                "man_scientist" => "👨‍🔬", "woman_scientist" => "👩‍🔬", "technologist" => "🧑‍💻", "man_technologist" => "👨‍💻",
                "woman_technologist" => "👩‍💻", "singer" => "🧑‍🎤", "man_singer" => "👨‍🎤", "woman_singer" => "👩‍🎤",
                "artist" => "🧑‍🎨", "man_artist" => "👨‍🎨", "woman_artist" => "👩‍🎨", "pilot" => "🧑‍✈️",
                "man_pilot" => "👨‍✈️", "woman_pilot" => "👩‍✈️", "astronaut" => "🧑‍🚀", "man_astronaut" => "👨‍🚀",
                "woman_astronaut" => "👩‍🚀", "firefighter" => "🧑‍🚒", "man_firefighter" => "👨‍🚒", "woman_firefighter" => "👩‍🚒",
                "police_officer" => "👮", "man_police_officer" => "👮‍♂️", "woman_police_officer" => "👮‍♀️", "detective" => "🕵️",
                "man_detective" => "🕵️‍♂️", "woman_detective" => "🕵️‍♀️", "guard" => "💂", "man_guard" => "💂‍♂️",
                "woman_guard" => "💂‍♀️", "ninja" => "🥷", "construction_worker" => "👷", "man_construction_worker" => "👷‍♂️",
                "woman_construction_worker" => "👷‍♀️", "person_with_crown" => "🫅", "prince" => "🤴", "princess" => "👸",
                "person_wearing_turban" => "👳", "man_wearing_turban" => "👳‍♂️", "woman_wearing_turban" => "👳‍♀️", "person_with_skullcap" => "👲",
                "woman_with_headscarf" => "🧕", "person_in_tuxedo" => "🤵", "man_in_tuxedo" => "🤵‍♂️", "woman_in_tuxedo" => "🤵‍♀️",
                "person_with_veil" => "👰", "man_with_veil" => "👰‍♂️", "woman_with_veil" => "👰‍♀️", "pregnant_woman" => "🤰",
                "pregnant_man" => "🫃", "pregnant_person" => "🫄", "breast_feeding" => "🤱", "woman_feeding_baby" => "👩‍🍼",
                "man_feeding_baby" => "👨‍🍼", "person_feeding_baby" => "🧑‍🍼", "baby_angel" => "👼", "santa_claus" => "🎅",
                "mrs_claus" => "🤶", "mx_claus" => "🧑‍🎄", "superhero" => "🦸", "man_superhero" => "🦸‍♂️",
                "woman_superhero" => "🦸‍♀️", "supervillain" => "🦹", "man_supervillain" => "🦹‍♂️", "woman_supervillain" => "🦹‍♀️",
                "mage" => "🧙", "man_mage" => "🧙‍♂️", "woman_mage" => "🧙‍♀️", "fairy" => "🧚",
                "man_fairy" => "🧚‍♂️", "woman_fairy" => "🧚‍♀️", "vampire" => "🧛", "man_vampire" => "🧛‍♂️",
                "woman_vampire" => "🧛‍♀️", "merperson" => "🧜", "merman" => "🧜‍♂️", "mermaid" => "🧜‍♀️",
                "elf" => "🧝", "man_elf" => "🧝‍♂️", "woman_elf" => "🧝‍♀️", "genie" => "🧞",
                "man_genie" => "🧞‍♂️", "woman_genie" => "🧞‍♀️", "zombie" => "🧟", "man_zombie" => "🧟‍♂️",
                "woman_zombie" => "🧟‍♀️", "troll" => "🧌", "person_getting_massage" => "💆", "man_getting_massage" => "💆‍♂️",
                "woman_getting_massage" => "💆‍♀️", "person_getting_haircut" => "💇", "man_getting_haircut" => "💇‍♂️", "woman_getting_haircut" => "💇‍♀️",
                "person_walking" => "🚶", "man_walking" => "🚶‍♂️", "woman_walking" => "🚶‍♀️", "person_walking_facing_right" => "🚶‍➡️",
                "woman_walking_facing_right" => "🚶‍♀️‍➡️", "man_walking_facing_right" => "🚶‍♂️‍➡️", "person_standing" => "🧍", "man_standing" => "🧍‍♂️",
                "woman_standing" => "🧍‍♀️", "person_kneeling" => "🧎", "man_kneeling" => "🧎‍♂️", "woman_kneeling" => "🧎‍♀️",
                "person_kneeling_facing_right" => "🧎‍➡️", "woman_kneeling_facing_right" => "🧎‍♀️‍➡️", "man_kneeling_facing_right" => "🧎‍♂️‍➡️", "person_with_white_cane" => "🧑‍🦯",
                "person_with_white_cane_facing_right" => "🧑‍🦯‍➡️", "man_with_white_cane" => "👨‍🦯", "man_with_white_cane_facing_right" => "👨‍🦯‍➡️", "woman_with_white_cane" => "👩‍🦯",
                "woman_with_white_cane_facing_right" => "👩‍🦯‍➡️", "person_in_motorized_wheelchair" => "🧑‍🦼", "person_in_motorized_wheelchair_facing_right" => "🧑‍🦼‍➡️", "man_in_motorized_wheelchair" => "👨‍🦼",
                "man_in_motorized_wheelchair_facing_right" => "👨‍🦼‍➡️", "woman_in_motorized_wheelchair" => "👩‍🦼", "woman_in_motorized_wheelchair_facing_right" => "👩‍🦼‍➡️", "person_in_manual_wheelchair" => "🧑‍🦽",
                "person_in_manual_wheelchair_facing_right" => "🧑‍🦽‍➡️", "man_in_manual_wheelchair" => "👨‍🦽", "man_in_manual_wheelchair_facing_right" => "👨‍🦽‍➡️", "woman_in_manual_wheelchair" => "👩‍🦽",
                "woman_in_manual_wheelchair_facing_right" => "👩‍🦽‍➡️", "person_running" => "🏃", "man_running" => "🏃‍♂️", "woman_running" => "🏃‍♀️",
                "person_running_facing_right" => "🏃‍➡️", "woman_running_facing_right" => "🏃‍♀️‍➡️", "man_running_facing_right" => "🏃‍♂️‍➡️", "woman_dancing" => "💃",
                "man_dancing" => "🕺", "person_in_suit_levitating" => "🕴️", "people_with_bunny_ears" => "👯", "men_with_bunny_ears" => "👯‍♂️",
                "women_with_bunny_ears" => "👯‍♀️", "person_in_steamy_room" => "🧖", "man_in_steamy_room" => "🧖‍♂️", "woman_in_steamy_room" => "🧖‍♀️",
                "person_climbing" => "🧗", "man_climbing" => "🧗‍♂️", "woman_climbing" => "🧗‍♀️", "person_fencing" => "🤺",
                "horse_racing" => "🏇", "skier" => "⛷️", "snowboarder" => "🏂", "person_golfing" => "🏌️",
                "man_golfing" => "🏌️‍♂️", "woman_golfing" => "🏌️‍♀️", "person_surfing" => "🏄", "man_surfing" => "🏄‍♂️",
                "woman_surfing" => "🏄‍♀️", "person_rowing_boat" => "🚣", "man_rowing_boat" => "🚣‍♂️", "woman_rowing_boat" => "🚣‍♀️",
                "person_swimming" => "🏊", "man_swimming" => "🏊‍♂️", "woman_swimming" => "🏊‍♀️", "person_bouncing_ball" => "⛹️",
                "man_bouncing_ball" => "⛹️‍♂️", "woman_bouncing_ball" => "⛹️‍♀️", "person_lifting_weights" => "🏋️", "man_lifting_weights" => "🏋️‍♂️",
                "woman_lifting_weights" => "🏋️‍♀️", "person_biking" => "🚴", "man_biking" => "🚴‍♂️", "woman_biking" => "🚴‍♀️",
                "person_mountain_biking" => "🚵", "man_mountain_biking" => "🚵‍♂️", "woman_mountain_biking" => "🚵‍♀️", "person_cartwheeling" => "🤸",
                "man_cartwheeling" => "🤸‍♂️", "woman_cartwheeling" => "🤸‍♀️", "people_wrestling" => "🤼", "men_wrestling" => "🤼‍♂️",
                "women_wrestling" => "🤼‍♀️", "person_playing_water_polo" => "🤽", "man_playing_water_polo" => "🤽‍♂️", "woman_playing_water_polo" => "🤽‍♀️",
                "person_playing_handball" => "🤾", "man_playing_handball" => "🤾‍♂️", "woman_playing_handball" => "🤾‍♀️", "person_juggling" => "🤹",
                "man_juggling" => "🤹‍♂️", "woman_juggling" => "🤹‍♀️", "person_in_lotus_position" => "🧘", "man_in_lotus_position" => "🧘‍♂️",
                "woman_in_lotus_position" => "🧘‍♀️", "person_taking_bath" => "🛀", "person_in_bed" => "🛌", "people_holding_hands" => "🧑‍🤝‍🧑",
                "women_holding_hands" => "👭", "woman_and_man_holding_hands" => "👫", "men_holding_hands" => "👬", "kiss" => "💏",
                "kiss_woman_man" => "👩‍❤️‍💋‍👨", "kiss_man_man" => "👨‍❤️‍💋‍👨", "kiss_woman_woman" => "👩‍❤️‍💋‍👩", "couple_with_heart" => "💑",
                "couple_with_heart_woman_man" => "👩‍❤️‍👨", "couple_with_heart_man_man" => "👨‍❤️‍👨", "couple_with_heart_woman_woman" => "👩‍❤️‍👩", "family_man_woman_boy" => "👨‍👩‍👦",
                "family_man_woman_girl" => "👨‍👩‍👧", "family_man_woman_girl_boy" => "👨‍👩‍👧‍👦", "family_man_woman_boy_boy" => "👨‍👩‍👦‍👦", "family_man_woman_girl_girl" => "👨‍👩‍👧‍👧",
                "family_man_man_boy" => "👨‍👨‍👦", "family_man_man_girl" => "👨‍👨‍👧", "family_man_man_girl_boy" => "👨‍👨‍👧‍👦", "family_man_man_boy_boy" => "👨‍👨‍👦‍👦",
                "family_man_man_girl_girl" => "👨‍👨‍👧‍👧", "family_woman_woman_boy" => "👩‍👩‍👦", "family_woman_woman_girl" => "👩‍👩‍👧", "family_woman_woman_girl_boy" => "👩‍👩‍👧‍👦",
                "family_woman_woman_boy_boy" => "👩‍👩‍👦‍👦", "family_woman_woman_girl_girl" => "👩‍👩‍👧‍👧", "family_man_boy" => "👨‍👦", "family_man_boy_boy" => "👨‍👦‍👦",
                "family_man_girl" => "👨‍👧", "family_man_girl_boy" => "👨‍👧‍👦", "family_man_girl_girl" => "👨‍👧‍👧", "family_woman_boy" => "👩‍👦",
                "family_woman_boy_boy" => "👩‍👦‍👦", "family_woman_girl" => "👩‍👧", "family_woman_girl_boy" => "👩‍👧‍👦", "family_woman_girl_girl" => "👩‍👧‍👧",
                "speaking_head" => "🗣️", "bust_in_silhouette" => "👤", "busts_in_silhouette" => "👥", "people_hugging" => "🫂",
                "family" => "👪", "family_adult_adult_child" => "🧑‍🧑‍🧒", "family_adult_adult_child_child" => "🧑‍🧑‍🧒‍🧒", "family_adult_child" => "🧑‍🧒",
                "family_adult_child_child" => "🧑‍🧒‍🧒", "footprints" => "👣", "fingerprint" => "🫆", "monkey_face" => "🐵",
                "monkey" => "🐒", "gorilla" => "🦍", "orangutan" => "🦧", "dog_face" => "🐶",
                "dog" => "🐕", "guide_dog" => "🦮", "service_dog" => "🐕‍🦺", "poodle" => "🐩",
                "wolf" => "🐺", "fox" => "🦊", "raccoon" => "🦝", "cat_face" => "🐱",
                "cat" => "🐈", "black_cat" => "🐈‍⬛", "lion" => "🦁", "tiger_face" => "🐯",
                "tiger" => "🐅", "leopard" => "🐆", "horse_face" => "🐴", "moose" => "🫎",
                "donkey" => "🫏", "horse" => "🐎", "unicorn" => "🦄", "zebra" => "🦓",
                "deer" => "🦌", "bison" => "🦬", "cow_face" => "🐮", "ox" => "🐂",
                "water_buffalo" => "🐃", "cow" => "🐄", "pig_face" => "🐷", "pig" => "🐖",
                "boar" => "🐗", "pig_nose" => "🐽", "ram" => "🐏", "ewe" => "🐑",
                "goat" => "🐐", "camel" => "🐪", "two_hump_camel" => "🐫", "llama" => "🦙",
                "giraffe" => "🦒", "elephant" => "🐘", "mammoth" => "🦣", "rhinoceros" => "🦏",
                "hippopotamus" => "🦛", "mouse_face" => "🐭", "mouse" => "🐁", "rat" => "🐀",
                "hamster" => "🐹", "rabbit_face" => "🐰", "rabbit" => "🐇", "chipmunk" => "🐿️",
                "beaver" => "🦫", "hedgehog" => "🦔", "bat" => "🦇", "bear" => "🐻",
                "polar_bear" => "🐻‍❄️", "koala" => "🐨", "panda" => "🐼", "sloth" => "🦥",
                "otter" => "🦦", "skunk" => "🦨", "kangaroo" => "🦘", "badger" => "🦡",
                "paw_prints" => "🐾", "turkey" => "🦃", "chicken" => "🐔", "rooster" => "🐓",
                "hatching_chick" => "🐣", "baby_chick" => "🐤", "front_facing_baby_chick" => "🐥", "bird" => "🐦",
                "penguin" => "🐧", "dove" => "🕊️", "eagle" => "🦅", "duck" => "🦆",
                "swan" => "🦢", "owl" => "🦉", "dodo" => "🦤", "feather" => "🪶",
                "flamingo" => "🦩", "peacock" => "🦚", "parrot" => "🦜", "wing" => "🪽",
                "black_bird" => "🐦‍⬛", "goose" => "🪿", "phoenix" => "🐦‍🔥", "frog" => "🐸",
                "crocodile" => "🐊", "turtle" => "🐢", "lizard" => "🦎", "snake" => "🐍",
                "dragon_face" => "🐲", "dragon" => "🐉", "sauropod" => "🦕", "t_rex" => "🦖",
                "spouting_whale" => "🐳", "whale" => "🐋", "dolphin" => "🐬", "seal" => "🦭",
                "fish" => "🐟", "tropical_fish" => "🐠", "blowfish" => "🐡", "shark" => "🦈",
                "octopus" => "🐙", "spiral_shell" => "🐚", "coral" => "🪸", "jellyfish" => "🪼",
                "crab" => "🦀", "lobster" => "🦞", "shrimp" => "🦐", "squid" => "🦑",
                "oyster" => "🦪", "snail" => "🐌", "butterfly" => "🦋", "bug" => "🐛",
                "ant" => "🐜", "honeybee" => "🐝", "beetle" => "🪲", "lady_beetle" => "🐞",
                "cricket" => "🦗", "cockroach" => "🪳", "spider" => "🕷️", "spider_web" => "🕸️",
                "scorpion" => "🦂", "mosquito" => "🦟", "fly" => "🪰", "worm" => "🪱",
                "microbe" => "🦠", "bouquet" => "💐", "cherry_blossom" => "🌸", "white_flower" => "💮",
                "lotus" => "🪷", "rosette" => "🏵️", "rose" => "🌹", "wilted_flower" => "🥀",
                "hibiscus" => "🌺", "sunflower" => "🌻", "blossom" => "🌼", "tulip" => "🌷",
                "hyacinth" => "🪻", "seedling" => "🌱", "potted_plant" => "🪴", "evergreen_tree" => "🌲",
                "deciduous_tree" => "🌳", "palm_tree" => "🌴", "cactus" => "🌵", "sheaf_of_rice" => "🌾",
                "herb" => "🌿", "shamrock" => "☘️", "four_leaf_clover" => "🍀", "maple_leaf" => "🍁",
                "fallen_leaf" => "🍂", "leaf_fluttering_in_wind" => "🍃", "empty_nest" => "🪹", "nest_with_eggs" => "🪺",
                "mushroom" => "🍄", "leafless_tree" => "🪾", "grapes" => "🍇", "melon" => "🍈",
                "watermelon" => "🍉", "tangerine" => "🍊", "lemon" => "🍋", "lime" => "🍋‍🟩",
                "banana" => "🍌", "pineapple" => "🍍", "mango" => "🥭", "red_apple" => "🍎",
                "green_apple" => "🍏", "pear" => "🍐", "peach" => "🍑", "cherries" => "🍒",
                "strawberry" => "🍓", "blueberries" => "🫐", "kiwi_fruit" => "🥝", "tomato" => "🍅",
                "olive" => "🫒", "coconut" => "🥥", "avocado" => "🥑", "eggplant" => "🍆",
                "potato" => "🥔", "carrot" => "🥕", "ear_of_corn" => "🌽", "hot_pepper" => "🌶️",
                "bell_pepper" => "🫑", "cucumber" => "🥒", "leafy_green" => "🥬", "broccoli" => "🥦",
                "garlic" => "🧄", "onion" => "🧅", "peanuts" => "🥜", "beans" => "🫘",
                "chestnut" => "🌰", "ginger_root" => "🫚", "pea_pod" => "🫛", "brown_mushroom" => "🍄‍🟫",
                "root_vegetable" => "🫜", "bread" => "🍞", "croissant" => "🥐", "baguette_bread" => "🥖",
                "flatbread" => "🫓", "pretzel" => "🥨", "bagel" => "🥯", "pancakes" => "🥞",
                "waffle" => "🧇", "cheese_wedge" => "🧀", "meat_on_bone" => "🍖", "poultry_leg" => "🍗",
                "cut_of_meat" => "🥩", "bacon" => "🥓", "hamburger" => "🍔", "french_fries" => "🍟",
                "pizza" => "🍕", "hot_dog" => "🌭", "sandwich" => "🥪", "taco" => "🌮",
                "burrito" => "🌯", "tamale" => "🫔", "stuffed_flatbread" => "🥙", "falafel" => "🧆",
                "egg" => "🥚", "cooking" => "🍳", "shallow_pan_of_food" => "🥘", "pot_of_food" => "🍲",
                "fondue" => "🫕", "bowl_with_spoon" => "🥣", "green_salad" => "🥗", "popcorn" => "🍿",
                "butter" => "🧈", "salt" => "🧂", "canned_food" => "🥫", "bento_box" => "🍱",
                "rice_cracker" => "🍘", "rice_ball" => "🍙", "cooked_rice" => "🍚", "curry_rice" => "🍛",
                "steaming_bowl" => "🍜", "spaghetti" => "🍝", "roasted_sweet_potato" => "🍠", "oden" => "🍢",
                "sushi" => "🍣", "fried_shrimp" => "🍤", "fish_cake_with_swirl" => "🍥", "moon_cake" => "🥮",
                "dango" => "🍡", "dumpling" => "🥟", "fortune_cookie" => "🥠", "takeout_box" => "🥡",
                "soft_ice_cream" => "🍦", "shaved_ice" => "🍧", "ice_cream" => "🍨", "doughnut" => "🍩",
                "cookie" => "🍪", "birthday_cake" => "🎂", "shortcake" => "🍰", "cupcake" => "🧁",
                "pie" => "🥧", "chocolate_bar" => "🍫", "candy" => "🍬", "lollipop" => "🍭",
                "custard" => "🍮", "honey_pot" => "🍯", "baby_bottle" => "🍼", "glass_of_milk" => "🥛",
                "hot_beverage" => "☕", "teapot" => "🫖", "teacup_without_handle" => "🍵", "sake" => "🍶",
                "bottle_with_popping_cork" => "🍾", "wine_glass" => "🍷", "cocktail_glass" => "🍸", "tropical_drink" => "🍹",
                "beer_mug" => "🍺", "clinking_beer_mugs" => "🍻", "clinking_glasses" => "🥂", "tumbler_glass" => "🥃",
                "pouring_liquid" => "🫗", "cup_with_straw" => "🥤", "bubble_tea" => "🧋", "beverage_box" => "🧃",
                "mate" => "🧉", "ice" => "🧊", "chopsticks" => "🥢", "fork_and_knife_with_plate" => "🍽️",
                "fork_and_knife" => "🍴", "spoon" => "🥄", "kitchen_knife" => "🔪", "jar" => "🫙",
                "amphora" => "🏺", "globe_showing_europe_africa" => "🌍", "globe_showing_americas" => "🌎", "globe_showing_asia_australia" => "🌏",
                "globe_with_meridians" => "🌐", "world_map" => "🗺️", "map_of_japan" => "🗾", "compass" => "🧭",
                "snow_capped_mountain" => "🏔️", "mountain" => "⛰️", "volcano" => "🌋", "mount_fuji" => "🗻",
                "camping" => "🏕️", "beach_with_umbrella" => "🏖️", "desert" => "🏜️", "desert_island" => "🏝️",
                "national_park" => "🏞️", "stadium" => "🏟️", "classical_building" => "🏛️", "building_construction" => "🏗️",
                "brick" => "🧱", "rock" => "🪨", "wood" => "🪵", "hut" => "🛖",
                "houses" => "🏘️", "derelict_house" => "🏚️", "house" => "🏠", "house_with_garden" => "🏡",
                "office_building" => "🏢", "japanese_post_office" => "🏣", "post_office" => "🏤", "hospital" => "🏥",
                "bank" => "🏦", "hotel" => "🏨", "love_hotel" => "🏩", "convenience_store" => "🏪",
                "school" => "🏫", "department_store" => "🏬", "factory" => "🏭", "japanese_castle" => "🏯",
                "castle" => "🏰", "wedding" => "💒", "tokyo_tower" => "🗼", "statue_of_liberty" => "🗽",
                "church" => "⛪", "mosque" => "🕌", "hindu_temple" => "🛕", "synagogue" => "🕍",
                "shinto_shrine" => "⛩️", "kaaba" => "🕋", "fountain" => "⛲", "tent" => "⛺",
                "foggy" => "🌁", "night_with_stars" => "🌃", "cityscape" => "🏙️", "sunrise_over_mountains" => "🌄",
                "sunrise" => "🌅", "cityscape_at_dusk" => "🌆", "sunset" => "🌇", "bridge_at_night" => "🌉",
                "hot_springs" => "♨️", "carousel_horse" => "🎠", "playground_slide" => "🛝", "ferris_wheel" => "🎡",
                "roller_coaster" => "🎢", "barber_pole" => "💈", "circus_tent" => "🎪", "locomotive" => "🚂",
                "railway_car" => "🚃", "high_speed_train" => "🚄", "bullet_train" => "🚅", "train" => "🚆",
                "metro" => "🚇", "light_rail" => "🚈", "station" => "🚉", "tram" => "🚊",
                "monorail" => "🚝", "mountain_railway" => "🚞", "tram_car" => "🚋", "bus" => "🚌",
                "oncoming_bus" => "🚍", "trolleybus" => "🚎", "minibus" => "🚐", "ambulance" => "🚑",
                "fire_engine" => "🚒", "police_car" => "🚓", "oncoming_police_car" => "🚔", "taxi" => "🚕",
                "oncoming_taxi" => "🚖", "automobile" => "🚗", "oncoming_automobile" => "🚘", "sport_utility_vehicle" => "🚙",
                "pickup_truck" => "🛻", "delivery_truck" => "🚚", "articulated_lorry" => "🚛", "tractor" => "🚜",
                "racing_car" => "🏎️", "motorcycle" => "🏍️", "motor_scooter" => "🛵", "manual_wheelchair" => "🦽",
                "motorized_wheelchair" => "🦼", "auto_rickshaw" => "🛺", "bicycle" => "🚲", "kick_scooter" => "🛴",
                "skateboard" => "🛹", "roller_skate" => "🛼", "bus_stop" => "🚏", "motorway" => "🛣️",
                "railway_track" => "🛤️", "oil_drum" => "🛢️", "fuel_pump" => "⛽", "wheel" => "🛞",
                "police_car_light" => "🚨", "horizontal_traffic_light" => "🚥", "vertical_traffic_light" => "🚦", "stop_sign" => "🛑",
                "construction" => "🚧", "anchor" => "⚓", "ring_buoy" => "🛟", "sailboat" => "⛵",
                "canoe" => "🛶", "speedboat" => "🚤", "passenger_ship" => "🛳️", "ferry" => "⛴️",
                "motor_boat" => "🛥️", "ship" => "🚢", "airplane" => "✈️", "small_airplane" => "🛩️",
                "airplane_departure" => "🛫", "airplane_arrival" => "🛬", "parachute" => "🪂", "seat" => "💺",
                "helicopter" => "🚁", "suspension_railway" => "🚟", "mountain_cableway" => "🚠", "aerial_tramway" => "🚡",
                "satellite" => "🛰️", "rocket" => "🚀", "flying_saucer" => "🛸", "bellhop_bell" => "🛎️",
                "luggage" => "🧳", "hourglass_done" => "⌛", "hourglass_not_done" => "⏳", "watch" => "⌚",
                "alarm_clock" => "⏰", "stopwatch" => "⏱️", "timer_clock" => "⏲️", "mantelpiece_clock" => "🕰️",
                "twelve_o_clock" => "🕛", "twelve_thirty" => "🕧", "one_o_clock" => "🕐", "one_thirty" => "🕜",
                "two_o_clock" => "🕑", "two_thirty" => "🕝", "three_o_clock" => "🕒", "three_thirty" => "🕞",
                "four_o_clock" => "🕓", "four_thirty" => "🕟", "five_o_clock" => "🕔", "five_thirty" => "🕠",
                "six_o_clock" => "🕕", "six_thirty" => "🕡", "seven_o_clock" => "🕖", "seven_thirty" => "🕢",
                "eight_o_clock" => "🕗", "eight_thirty" => "🕣", "nine_o_clock" => "🕘", "nine_thirty" => "🕤",
                "ten_o_clock" => "🕙", "ten_thirty" => "🕥", "eleven_o_clock" => "🕚", "eleven_thirty" => "🕦",
                "new_moon" => "🌑", "waxing_crescent_moon" => "🌒", "first_quarter_moon" => "🌓", "waxing_gibbous_moon" => "🌔",
                "full_moon" => "🌕", "waning_gibbous_moon" => "🌖", "last_quarter_moon" => "🌗", "waning_crescent_moon" => "🌘",
                "crescent_moon" => "🌙", "new_moon_face" => "🌚", "first_quarter_moon_face" => "🌛", "last_quarter_moon_face" => "🌜",
                "thermometer" => "🌡️", "sun" => "☀️", "full_moon_face" => "🌝", "sun_with_face" => "🌞",
                "ringed_planet" => "🪐", "star" => "⭐", "glowing_star" => "🌟", "shooting_star" => "🌠",
                "milky_way" => "🌌", "cloud" => "☁️", "sun_behind_cloud" => "⛅", "cloud_with_lightning_and_rain" => "⛈️",
                "sun_behind_small_cloud" => "🌤️", "sun_behind_large_cloud" => "🌥️", "sun_behind_rain_cloud" => "🌦️", "cloud_with_rain" => "🌧️",
                "cloud_with_snow" => "🌨️", "cloud_with_lightning" => "🌩️", "tornado" => "🌪️", "fog" => "🌫️",
                "wind_face" => "🌬️", "cyclone" => "🌀", "rainbow" => "🌈", "closed_umbrella" => "🌂",
                "umbrella" => "☂️", "umbrella_with_rain_drops" => "☔", "umbrella_on_ground" => "⛱️", "high_voltage" => "⚡",
                "snowflake" => "❄️", "snowman" => "☃️", "snowman_without_snow" => "⛄", "comet" => "☄️",
                "fire" => "🔥", "droplet" => "💧", "water_wave" => "🌊", "jack_o_lantern" => "🎃",
                "christmas_tree" => "🎄", "fireworks" => "🎆", "sparkler" => "🎇", "firecracker" => "🧨",
                "sparkles" => "✨", "balloon" => "🎈", "party_popper" => "🎉", "confetti_ball" => "🎊",
                "tanabata_tree" => "🎋", "pine_decoration" => "🎍", "japanese_dolls" => "🎎", "carp_streamer" => "🎏",
                "wind_chime" => "🎐", "moon_viewing_ceremony" => "🎑", "red_envelope" => "🧧", "ribbon" => "🎀",
                "wrapped_gift" => "🎁", "reminder_ribbon" => "🎗️", "admission_tickets" => "🎟️", "ticket" => "🎫",
                "military_medal" => "🎖️", "trophy" => "🏆", "sports_medal" => "🏅", "1st_place_medal" => "🥇",
                "2nd_place_medal" => "🥈", "3rd_place_medal" => "🥉", "soccer_ball" => "⚽", "baseball" => "⚾",
                "softball" => "🥎", "basketball" => "🏀", "volleyball" => "🏐", "american_football" => "🏈",
                "rugby_football" => "🏉", "tennis" => "🎾", "flying_disc" => "🥏", "bowling" => "🎳",
                "cricket_game" => "🏏", "field_hockey" => "🏑", "ice_hockey" => "🏒", "lacrosse" => "🥍",
                "ping_pong" => "🏓", "badminton" => "🏸", "boxing_glove" => "🥊", "martial_arts_uniform" => "🥋",
                "goal_net" => "🥅", "flag_in_hole" => "⛳", "ice_skate" => "⛸️", "fishing_pole" => "🎣",
                "diving_mask" => "🤿", "running_shirt" => "🎽", "skis" => "🎿", "sled" => "🛷",
                "curling_stone" => "🥌", "bullseye" => "🎯", "yo_yo" => "🪀", "kite" => "🪁",
                "water_pistol" => "🔫", "pool_8_ball" => "🎱", "crystal_ball" => "🔮", "magic_wand" => "🪄",
                "video_game" => "🎮", "joystick" => "🕹️", "slot_machine" => "🎰", "game_die" => "🎲",
                "puzzle_piece" => "🧩", "teddy_bear" => "🧸", "pinata" => "🪅", "mirror_ball" => "🪩",
                "nesting_dolls" => "🪆", "spade_suit" => "♠️", "heart_suit" => "♥️", "diamond_suit" => "♦️",
                "club_suit" => "♣️", "chess_pawn" => "♟️", "joker" => "🃏", "mahjong_red_dragon" => "🀄",
                "flower_playing_cards" => "🎴", "performing_arts" => "🎭", "framed_picture" => "🖼️", "artist_palette" => "🎨",
                "thread" => "🧵", "sewing_needle" => "🪡", "yarn" => "🧶", "knot" => "🪢",
                "glasses" => "👓", "sunglasses" => "🕶️", "goggles" => "🥽", "lab_coat" => "🥼",
                "safety_vest" => "🦺", "necktie" => "👔", "t_shirt" => "👕", "jeans" => "👖",
                "scarf" => "🧣", "gloves" => "🧤", "coat" => "🧥", "socks" => "🧦",
                "dress" => "👗", "kimono" => "👘", "sari" => "🥻", "one_piece_swimsuit" => "🩱",
                "briefs" => "🩲", "shorts" => "🩳", "bikini" => "👙", "woman_s_clothes" => "👚",
                "folding_hand_fan" => "🪭", "purse" => "👛", "handbag" => "👜", "clutch_bag" => "👝",
                "shopping_bags" => "🛍️", "backpack" => "🎒", "thong_sandal" => "🩴", "man_s_shoe" => "👞",
                "running_shoe" => "👟", "hiking_boot" => "🥾", "flat_shoe" => "🥿", "high_heeled_shoe" => "👠",
                "woman_s_sandal" => "👡", "ballet_shoes" => "🩰", "woman_s_boot" => "👢", "hair_pick" => "🪮",
                "crown" => "👑", "woman_s_hat" => "👒", "top_hat" => "🎩", "graduation_cap" => "🎓",
                "billed_cap" => "🧢", "military_helmet" => "🪖", "rescue_worker_s_helmet" => "⛑️", "prayer_beads" => "📿",
                "lipstick" => "💄", "ring" => "💍", "gem_stone" => "💎", "muted_speaker" => "🔇",
                "speaker_low_volume" => "🔈", "speaker_medium_volume" => "🔉", "speaker_high_volume" => "🔊", "loudspeaker" => "📢",
                "megaphone" => "📣", "postal_horn" => "📯", "bell" => "🔔", "bell_with_slash" => "🔕",
                "musical_score" => "🎼", "musical_note" => "🎵", "musical_notes" => "🎶", "studio_microphone" => "🎙️",
                "level_slider" => "🎚️", "control_knobs" => "🎛️", "microphone" => "🎤", "headphone" => "🎧",
                "radio" => "📻", "saxophone" => "🎷", "accordion" => "🪗", "guitar" => "🎸",
                "musical_keyboard" => "🎹", "trumpet" => "🎺", "violin" => "🎻", "banjo" => "🪕",
                "drum" => "🥁", "long_drum" => "🪘", "maracas" => "🪇", "flute" => "🪈",
                "harp" => "🪉", "mobile_phone" => "📱", "mobile_phone_with_arrow" => "📲", "telephone" => "☎️",
                "telephone_receiver" => "📞", "pager" => "📟", "fax_machine" => "📠", "battery" => "🔋",
                "low_battery" => "🪫", "electric_plug" => "🔌", "laptop" => "💻", "desktop_computer" => "🖥️",
                "printer" => "🖨️", "keyboard" => "⌨️", "computer_mouse" => "🖱️", "trackball" => "🖲️",
                "computer_disk" => "💽", "floppy_disk" => "💾", "optical_disk" => "💿", "dvd" => "📀",
                "abacus" => "🧮", "movie_camera" => "🎥", "film_frames" => "🎞️", "film_projector" => "📽️",
                "clapper_board" => "🎬", "television" => "📺", "camera" => "📷", "camera_with_flash" => "📸",
                "video_camera" => "📹", "videocassette" => "📼", "magnifying_glass_tilted_left" => "🔍", "magnifying_glass_tilted_right" => "🔎",
                "candle" => "🕯️", "light_bulb" => "💡", "flashlight" => "🔦", "red_paper_lantern" => "🏮",
                "diya_lamp" => "🪔", "notebook_with_decorative_cover" => "📔", "closed_book" => "📕", "open_book" => "📖",
                "green_book" => "📗", "blue_book" => "📘", "orange_book" => "📙", "books" => "📚",
                "notebook" => "📓", "ledger" => "📒", "page_with_curl" => "📃", "scroll" => "📜",
                "page_facing_up" => "📄", "newspaper" => "📰", "rolled_up_newspaper" => "🗞️", "bookmark_tabs" => "📑",
                "bookmark" => "🔖", "label" => "🏷️", "money_bag" => "💰", "coin" => "🪙",
                "yen_banknote" => "💴", "dollar_banknote" => "💵", "euro_banknote" => "💶", "pound_banknote" => "💷",
                "money_with_wings" => "💸", "credit_card" => "💳", "receipt" => "🧾", "chart_increasing_with_yen" => "💹",
                "envelope" => "✉️", "e_mail" => "📧", "incoming_envelope" => "📨", "envelope_with_arrow" => "📩",
                "outbox_tray" => "📤", "inbox_tray" => "📥", "package" => "📦", "closed_mailbox_with_raised_flag" => "📫",
                "closed_mailbox_with_lowered_flag" => "📪", "open_mailbox_with_raised_flag" => "📬", "open_mailbox_with_lowered_flag" => "📭", "postbox" => "📮",
                "ballot_box_with_ballot" => "🗳️", "pencil" => "✏️", "black_nib" => "✒️", "fountain_pen" => "🖋️",
                "pen" => "🖊️", "paintbrush" => "🖌️", "crayon" => "🖍️", "memo" => "📝",
                "briefcase" => "💼", "file_folder" => "📁", "open_file_folder" => "📂", "card_index_dividers" => "🗂️",
                "calendar" => "📅", "tear_off_calendar" => "📆", "spiral_notepad" => "🗒️", "spiral_calendar" => "🗓️",
                "card_index" => "📇", "chart_increasing" => "📈", "chart_decreasing" => "📉", "bar_chart" => "📊",
                "clipboard" => "📋", "pushpin" => "📌", "round_pushpin" => "📍", "paperclip" => "📎",
                "linked_paperclips" => "🖇️", "straight_ruler" => "📏", "triangular_ruler" => "📐", "scissors" => "✂️",
                "card_file_box" => "🗃️", "file_cabinet" => "🗄️", "wastebasket" => "🗑️", "locked" => "🔒",
                "unlocked" => "🔓", "locked_with_pen" => "🔏", "locked_with_key" => "🔐", "key" => "🔑",
                "old_key" => "🗝️", "hammer" => "🔨", "axe" => "🪓", "pick" => "⛏️",
                "hammer_and_pick" => "⚒️", "hammer_and_wrench" => "🛠️", "dagger" => "🗡️", "crossed_swords" => "⚔️",
                "bomb" => "💣", "boomerang" => "🪃", "bow_and_arrow" => "🏹", "shield" => "🛡️",
                "carpentry_saw" => "🪚", "wrench" => "🔧", "screwdriver" => "🪛", "nut_and_bolt" => "🔩",
                "gear" => "⚙️", "clamp" => "🗜️", "balance_scale" => "⚖️", "white_cane" => "🦯",
                "link" => "🔗", "broken_chain" => "⛓️‍💥", "chains" => "⛓️", "hook" => "🪝",
                "toolbox" => "🧰", "magnet" => "🧲", "ladder" => "🪜", "shovel" => "🪏",
                "alembic" => "⚗️", "test_tube" => "🧪", "petri_dish" => "🧫", "dna" => "🧬",
                "microscope" => "🔬", "telescope" => "🔭", "satellite_antenna" => "📡", "syringe" => "💉",
                "drop_of_blood" => "🩸", "pill" => "💊", "adhesive_bandage" => "🩹", "crutch" => "🩼",
                "stethoscope" => "🩺", "x_ray" => "🩻", "door" => "🚪", "elevator" => "🛗",
                "mirror" => "🪞", "window" => "🪟", "bed" => "🛏️", "couch_and_lamp" => "🛋️",
                "chair" => "🪑", "toilet" => "🚽", "plunger" => "🪠", "shower" => "🚿",
                "bathtub" => "🛁", "mouse_trap" => "🪤", "razor" => "🪒", "lotion_bottle" => "🧴",
                "safety_pin" => "🧷", "broom" => "🧹", "basket" => "🧺", "roll_of_paper" => "🧻",
                "bucket" => "🪣", "soap" => "🧼", "bubbles" => "🫧", "toothbrush" => "🪥",
                "sponge" => "🧽", "fire_extinguisher" => "🧯", "shopping_cart" => "🛒", "cigarette" => "🚬",
                "coffin" => "⚰️", "headstone" => "🪦", "funeral_urn" => "⚱️", "nazar_amulet" => "🧿",
                "hamsa" => "🪬", "moai" => "🗿", "placard" => "🪧", "identification_card" => "🪪",
                "atm_sign" => "🏧", "litter_in_bin_sign" => "🚮", "potable_water" => "🚰", "wheelchair_symbol" => "♿",
                "men_s_room" => "🚹", "women_s_room" => "🚺", "restroom" => "🚻", "baby_symbol" => "🚼",
                "water_closet" => "🚾", "passport_control" => "🛂", "customs" => "🛃", "baggage_claim" => "🛄",
                "left_luggage" => "🛅", "warning" => "⚠️", "children_crossing" => "🚸", "no_entry" => "⛔",
                "prohibited" => "🚫", "no_bicycles" => "🚳", "no_smoking" => "🚭", "no_littering" => "🚯",
                "non_potable_water" => "🚱", "no_pedestrians" => "🚷", "no_mobile_phones" => "📵", "no_one_under_eighteen" => "🔞",
                "radioactive" => "☢️", "biohazard" => "☣️", "up_arrow" => "⬆️", "up_right_arrow" => "↗️",
                "right_arrow" => "➡️", "down_right_arrow" => "↘️", "down_arrow" => "⬇️", "down_left_arrow" => "↙️",
                "left_arrow" => "⬅️", "up_left_arrow" => "↖️", "up_down_arrow" => "↕️", "left_right_arrow" => "↔️",
                "right_arrow_curving_left" => "↩️", "left_arrow_curving_right" => "↪️", "right_arrow_curving_up" => "⤴️", "right_arrow_curving_down" => "⤵️",
                "clockwise_vertical_arrows" => "🔃", "counterclockwise_arrows_button" => "🔄", "back_arrow" => "🔙", "end_arrow" => "🔚",
                "on_arrow" => "🔛", "soon_arrow" => "🔜", "top_arrow" => "🔝", "place_of_worship" => "🛐",
                "atom_symbol" => "⚛️", "om" => "🕉️", "star_of_david" => "✡️", "wheel_of_dharma" => "☸️",
                "yin_yang" => "☯️", "latin_cross" => "✝️", "orthodox_cross" => "☦️", "star_and_crescent" => "☪️",
                "peace_symbol" => "☮️", "menorah" => "🕎", "dotted_six_pointed_star" => "🔯", "khanda" => "🪯",
                "aries" => "♈", "taurus" => "♉", "gemini" => "♊", "cancer" => "♋",
                "leo" => "♌", "virgo" => "♍", "libra" => "♎", "scorpio" => "♏",
                "sagittarius" => "♐", "capricorn" => "♑", "aquarius" => "♒", "pisces" => "♓",
                "ophiuchus" => "⛎", "shuffle_tracks_button" => "🔀", "repeat_button" => "🔁", "repeat_single_button" => "🔂",
                "play_button" => "▶️", "fast_forward_button" => "⏩", "next_track_button" => "⏭️", "play_or_pause_button" => "⏯️",
                "reverse_button" => "◀️", "fast_reverse_button" => "⏪", "last_track_button" => "⏮️", "upwards_button" => "🔼",
                "fast_up_button" => "⏫", "downwards_button" => "🔽", "fast_down_button" => "⏬", "pause_button" => "⏸️",
                "stop_button" => "⏹️", "record_button" => "⏺️", "eject_button" => "⏏️", "cinema" => "🎦",
                "dim_button" => "🔅", "bright_button" => "🔆", "antenna_bars" => "📶", "wireless" => "🛜",
                "vibration_mode" => "📳", "mobile_phone_off" => "📴", "female_sign" => "♀️", "male_sign" => "♂️",
                "transgender_symbol" => "⚧️", "multiply" => "✖️", "plus" => "➕", "minus" => "➖",
                "divide" => "➗", "heavy_equals_sign" => "🟰", "infinity" => "♾️", "double_exclamation_mark" => "‼️",
                "exclamation_question_mark" => "⁉️", "red_question_mark" => "❓", "white_question_mark" => "❔", "white_exclamation_mark" => "❕",
                "red_exclamation_mark" => "❗", "wavy_dash" => "〰️", "currency_exchange" => "💱", "heavy_dollar_sign" => "💲",
                "medical_symbol" => "⚕️", "recycling_symbol" => "♻️", "fleur_de_lis" => "⚜️", "trident_emblem" => "🔱",
                "name_badge" => "📛", "japanese_symbol_for_beginner" => "🔰", "hollow_red_circle" => "⭕", "check_mark_button" => "✅",
                "check_box_with_check" => "☑️", "check_mark" => "✔️", "cross_mark" => "❌", "cross_mark_button" => "❎",
                "curly_loop" => "➰", "double_curly_loop" => "➿", "part_alternation_mark" => "〽️", "eight_spoked_asterisk" => "✳️",
                "eight_pointed_star" => "✴️", "sparkle" => "❇️", "copyright" => "©️", "registered" => "®️",
                "trade_mark" => "™️", "splatter" => "🫟", "keycap_number_sign" => "#️⃣", "keycap_asterisk" => "*️⃣",
                "keycap_0" => "0️⃣", "keycap_1" => "1️⃣", "keycap_2" => "2️⃣", "keycap_3" => "3️⃣",
                "keycap_4" => "4️⃣", "keycap_5" => "5️⃣", "keycap_6" => "6️⃣", "keycap_7" => "7️⃣",
                "keycap_8" => "8️⃣", "keycap_9" => "9️⃣", "keycap_10" => "🔟", "input_latin_uppercase" => "🔠",
                "input_latin_lowercase" => "🔡", "input_numbers" => "🔢", "input_symbols" => "🔣", "input_latin_letters" => "🔤",
                "a_button" => "🅰️", "ab_button" => "🆎", "b_button" => "🅱️", "cl_button" => "🆑",
                "cool_button" => "🆒", "free_button" => "🆓", "information" => "ℹ️", "id_button" => "🆔",
                "circled_m" => "Ⓜ️", "new_button" => "🆕", "ng_button" => "🆖", "o_button" => "🅾️",
                "ok_button" => "🆗", "p_button" => "🅿️", "sos_button" => "🆘", "up_button" => "🆙",
                "vs_button" => "🆚", "japanese_here_button" => "🈁", "japanese_service_charge_button" => "🈂️", "japanese_monthly_amount_button" => "🈷️",
                "japanese_not_free_of_charge_button" => "🈶", "japanese_reserved_button" => "🈯", "japanese_bargain_button" => "🉐", "japanese_discount_button" => "🈹",
                "japanese_free_of_charge_button" => "🈚", "japanese_prohibited_button" => "🈲", "japanese_acceptable_button" => "🉑", "japanese_application_button" => "🈸",
                "japanese_passing_grade_button" => "🈴", "japanese_vacancy_button" => "🈳", "japanese_congratulations_button" => "㊗️", "japanese_secret_button" => "㊙️",
                "japanese_open_for_business_button" => "🈺", "japanese_no_vacancy_button" => "🈵", "red_circle" => "🔴", "orange_circle" => "🟠",
                "yellow_circle" => "🟡", "green_circle" => "🟢", "blue_circle" => "🔵", "purple_circle" => "🟣",
                "brown_circle" => "🟤", "black_circle" => "⚫", "white_circle" => "⚪", "red_square" => "🟥",
                "orange_square" => "🟧", "yellow_square" => "🟨", "green_square" => "🟩", "blue_square" => "🟦",
                "purple_square" => "🟪", "brown_square" => "🟫", "black_large_square" => "⬛", "white_large_square" => "⬜",
                "black_medium_square" => "◼️", "white_medium_square" => "◻️", "black_medium_small_square" => "◾", "white_medium_small_square" => "◽",
                "black_small_square" => "▪️", "white_small_square" => "▫️", "large_orange_diamond" => "🔶", "large_blue_diamond" => "🔷",
                "small_orange_diamond" => "🔸", "small_blue_diamond" => "🔹", "red_triangle_pointed_up" => "🔺", "red_triangle_pointed_down" => "🔻",
                "diamond_with_a_dot" => "💠", "radio_button" => "🔘", "white_square_button" => "🔳", "black_square_button" => "🔲",
                "chequered_flag" => "🏁", "triangular_flag" => "🚩", "crossed_flags" => "🎌", "black_flag" => "🏴",
                "white_flag" => "🏳️", "rainbow_flag" => "🏳️‍🌈", "transgender_flag" => "🏳️‍⚧️", "pirate_flag" => "🏴‍☠️",
                "flag_ascension_island" => "🇦🇨", "flag_andorra" => "🇦🇩", "flag_united_arab_emirates" => "🇦🇪", "flag_afghanistan" => "🇦🇫",
                "flag_antigua_barbuda" => "🇦🇬", "flag_anguilla" => "🇦🇮", "flag_albania" => "🇦🇱", "flag_armenia" => "🇦🇲",
                "flag_angola" => "🇦🇴", "flag_antarctica" => "🇦🇶", "flag_argentina" => "🇦🇷", "flag_american_samoa" => "🇦🇸",
                "flag_austria" => "🇦🇹", "flag_australia" => "🇦🇺", "flag_aruba" => "🇦🇼", "flag_aland_islands" => "🇦🇽",
                "flag_azerbaijan" => "🇦🇿", "flag_bosnia_herzegovina" => "🇧🇦", "flag_barbados" => "🇧🇧", "flag_bangladesh" => "🇧🇩",
                "flag_belgium" => "🇧🇪", "flag_burkina_faso" => "🇧🇫", "flag_bulgaria" => "🇧🇬", "flag_bahrain" => "🇧🇭",
                "flag_burundi" => "🇧🇮", "flag_benin" => "🇧🇯", "flag_st_barthelemy" => "🇧🇱", "flag_bermuda" => "🇧🇲",
                "flag_brunei" => "🇧🇳", "flag_bolivia" => "🇧🇴", "flag_caribbean_netherlands" => "🇧🇶", "flag_brazil" => "🇧🇷",
                "flag_bahamas" => "🇧🇸", "flag_bhutan" => "🇧🇹", "flag_bouvet_island" => "🇧🇻", "flag_botswana" => "🇧🇼",
                "flag_belarus" => "🇧🇾", "flag_belize" => "🇧🇿", "flag_canada" => "🇨🇦", "flag_cocos_islands" => "🇨🇨",
                "flag_congo_kinshasa" => "🇨🇩", "flag_central_african_republic" => "🇨🇫", "flag_congo_brazzaville" => "🇨🇬", "flag_switzerland" => "🇨🇭",
                "flag_cote_d_ivoire" => "🇨🇮", "flag_cook_islands" => "🇨🇰", "flag_chile" => "🇨🇱", "flag_cameroon" => "🇨🇲",
                "flag_china" => "🇨🇳", "flag_colombia" => "🇨🇴", "flag_clipperton_island" => "🇨🇵", "flag_sark" => "🇨🇶",
                "flag_costa_rica" => "🇨🇷", "flag_cuba" => "🇨🇺", "flag_cape_verde" => "🇨🇻", "flag_curacao" => "🇨🇼",
                "flag_christmas_island" => "🇨🇽", "flag_cyprus" => "🇨🇾", "flag_czechia" => "🇨🇿", "flag_germany" => "🇩🇪",
                "flag_diego_garcia" => "🇩🇬", "flag_djibouti" => "🇩🇯", "flag_denmark" => "🇩🇰", "flag_dominica" => "🇩🇲",
                "flag_dominican_republic" => "🇩🇴", "flag_algeria" => "🇩🇿", "flag_ceuta_melilla" => "🇪🇦", "flag_ecuador" => "🇪🇨",
                "flag_estonia" => "🇪🇪", "flag_egypt" => "🇪🇬", "flag_western_sahara" => "🇪🇭", "flag_eritrea" => "🇪🇷",
                "flag_spain" => "🇪🇸", "flag_ethiopia" => "🇪🇹", "flag_european_union" => "🇪🇺", "flag_finland" => "🇫🇮",
                "flag_fiji" => "🇫🇯", "flag_falkland_islands" => "🇫🇰", "flag_micronesia" => "🇫🇲", "flag_faroe_islands" => "🇫🇴",
                "flag_france" => "🇫🇷", "flag_gabon" => "🇬🇦", "flag_united_kingdom" => "🇬🇧", "flag_grenada" => "🇬🇩",
                "flag_georgia" => "🇬🇪", "flag_french_guiana" => "🇬🇫", "flag_guernsey" => "🇬🇬", "flag_ghana" => "🇬🇭",
                "flag_gibraltar" => "🇬🇮", "flag_greenland" => "🇬🇱", "flag_gambia" => "🇬🇲", "flag_guinea" => "🇬🇳",
                "flag_guadeloupe" => "🇬🇵", "flag_equatorial_guinea" => "🇬🇶", "flag_greece" => "🇬🇷", "flag_south_georgia_south_sandwich_islands" => "🇬🇸",
                "flag_guatemala" => "🇬🇹", "flag_guam" => "🇬🇺", "flag_guinea_bissau" => "🇬🇼", "flag_guyana" => "🇬🇾",
                "flag_hong_kong_sar_china" => "🇭🇰", "flag_heard_mcdonald_islands" => "🇭🇲", "flag_honduras" => "🇭🇳", "flag_croatia" => "🇭🇷",
                "flag_haiti" => "🇭🇹", "flag_hungary" => "🇭🇺", "flag_canary_islands" => "🇮🇨", "flag_indonesia" => "🇮🇩",
                "flag_ireland" => "🇮🇪", "flag_israel" => "🇮🇱", "flag_isle_of_man" => "🇮🇲", "flag_india" => "🇮🇳",
                "flag_british_indian_ocean_territory" => "🇮🇴", "flag_iraq" => "🇮🇶", "flag_iran" => "🇮🇷", "flag_iceland" => "🇮🇸",
                "flag_italy" => "🇮🇹", "flag_jersey" => "🇯🇪", "flag_jamaica" => "🇯🇲", "flag_jordan" => "🇯🇴",
                "flag_japan" => "🇯🇵", "flag_kenya" => "🇰🇪", "flag_kyrgyzstan" => "🇰🇬", "flag_cambodia" => "🇰🇭",
                "flag_kiribati" => "🇰🇮", "flag_comoros" => "🇰🇲", "flag_st_kitts_nevis" => "🇰🇳", "flag_north_korea" => "🇰🇵",
                "flag_south_korea" => "🇰🇷", "flag_kuwait" => "🇰🇼", "flag_cayman_islands" => "🇰🇾", "flag_kazakhstan" => "🇰🇿",
                "flag_laos" => "🇱🇦", "flag_lebanon" => "🇱🇧", "flag_st_lucia" => "🇱🇨", "flag_liechtenstein" => "🇱🇮",
                "flag_sri_lanka" => "🇱🇰", "flag_liberia" => "🇱🇷", "flag_lesotho" => "🇱🇸", "flag_lithuania" => "🇱🇹",
                "flag_luxembourg" => "🇱🇺", "flag_latvia" => "🇱🇻", "flag_libya" => "🇱🇾", "flag_morocco" => "🇲🇦",
                "flag_monaco" => "🇲🇨", "flag_moldova" => "🇲🇩", "flag_montenegro" => "🇲🇪", "flag_st_martin" => "🇲🇫",
                "flag_madagascar" => "🇲🇬", "flag_marshall_islands" => "🇲🇭", "flag_north_macedonia" => "🇲🇰", "flag_mali" => "🇲🇱",
                "flag_myanmar" => "🇲🇲", "flag_mongolia" => "🇲🇳", "flag_macao_sar_china" => "🇲🇴", "flag_northern_mariana_islands" => "🇲🇵",
                "flag_martinique" => "🇲🇶", "flag_mauritania" => "🇲🇷", "flag_montserrat" => "🇲🇸", "flag_malta" => "🇲🇹",
                "flag_mauritius" => "🇲🇺", "flag_maldives" => "🇲🇻", "flag_malawi" => "🇲🇼", "flag_mexico" => "🇲🇽",
                "flag_malaysia" => "🇲🇾", "flag_mozambique" => "🇲🇿", "flag_namibia" => "🇳🇦", "flag_new_caledonia" => "🇳🇨",
                "flag_niger" => "🇳🇪", "flag_norfolk_island" => "🇳🇫", "flag_nigeria" => "🇳🇬", "flag_nicaragua" => "🇳🇮",
                "flag_netherlands" => "🇳🇱", "flag_norway" => "🇳🇴", "flag_nepal" => "🇳🇵", "flag_nauru" => "🇳🇷",
                "flag_niue" => "🇳🇺", "flag_new_zealand" => "🇳🇿", "flag_oman" => "🇴🇲", "flag_panama" => "🇵🇦",
                "flag_peru" => "🇵🇪", "flag_french_polynesia" => "🇵🇫", "flag_papua_new_guinea" => "🇵🇬", "flag_philippines" => "🇵🇭",
                "flag_pakistan" => "🇵🇰", "flag_poland" => "🇵🇱", "flag_st_pierre_miquelon" => "🇵🇲", "flag_pitcairn_islands" => "🇵🇳",
                "flag_puerto_rico" => "🇵🇷", "flag_palestinian_territories" => "🇵🇸", "flag_portugal" => "🇵🇹", "flag_palau" => "🇵🇼",
                "flag_paraguay" => "🇵🇾", "flag_qatar" => "🇶🇦", "flag_reunion" => "🇷🇪", "flag_romania" => "🇷🇴",
                "flag_serbia" => "🇷🇸", "flag_russia" => "🇷🇺", "flag_rwanda" => "🇷🇼", "flag_saudi_arabia" => "🇸🇦",
                "flag_solomon_islands" => "🇸🇧", "flag_seychelles" => "🇸🇨", "flag_sudan" => "🇸🇩", "flag_sweden" => "🇸🇪",
                "flag_singapore" => "🇸🇬", "flag_st_helena" => "🇸🇭", "flag_slovenia" => "🇸🇮", "flag_svalbard_jan_mayen" => "🇸🇯",
                "flag_slovakia" => "🇸🇰", "flag_sierra_leone" => "🇸🇱", "flag_san_marino" => "🇸🇲", "flag_senegal" => "🇸🇳",
                "flag_somalia" => "🇸🇴", "flag_suriname" => "🇸🇷", "flag_south_sudan" => "🇸🇸", "flag_sao_tome_principe" => "🇸🇹",
                "flag_el_salvador" => "🇸🇻", "flag_sint_maarten" => "🇸🇽", "flag_syria" => "🇸🇾", "flag_eswatini" => "🇸🇿",
                "flag_tristan_da_cunha" => "🇹🇦", "flag_turks_caicos_islands" => "🇹🇨", "flag_chad" => "🇹🇩", "flag_french_southern_territories" => "🇹🇫",
                "flag_togo" => "🇹🇬", "flag_thailand" => "🇹🇭", "flag_tajikistan" => "🇹🇯", "flag_tokelau" => "🇹🇰",
                "flag_timor_leste" => "🇹🇱", "flag_turkmenistan" => "🇹🇲", "flag_tunisia" => "🇹🇳", "flag_tonga" => "🇹🇴",
                "flag_turkiye" => "🇹🇷", "flag_trinidad_tobago" => "🇹🇹", "flag_tuvalu" => "🇹🇻", "flag_taiwan" => "🇹🇼",
                "flag_tanzania" => "🇹🇿", "flag_ukraine" => "🇺🇦", "flag_uganda" => "🇺🇬", "flag_u_s_outlying_islands" => "🇺🇲",
                "flag_united_nations" => "🇺🇳", "flag_united_states" => "🇺🇸", "flag_uruguay" => "🇺🇾", "flag_uzbekistan" => "🇺🇿",
                "flag_vatican_city" => "🇻🇦", "flag_st_vincent_grenadines" => "🇻🇨", "flag_venezuela" => "🇻🇪", "flag_british_virgin_islands" => "🇻🇬",
                "flag_u_s_virgin_islands" => "🇻🇮", "flag_vietnam" => "🇻🇳", "flag_vanuatu" => "🇻🇺", "flag_wallis_futuna" => "🇼🇫",
                "flag_samoa" => "🇼🇸", "flag_kosovo" => "🇽🇰", "flag_yemen" => "🇾🇪", "flag_mayotte" => "🇾🇹",
                "flag_south_africa" => "🇿🇦", "flag_zambia" => "🇿🇲", "flag_zimbabwe" => "🇿🇼", "flag_england" => "🏴󠁧󠁢󠁥󠁮󠁧󠁿",
                "flag_scotland" => "🏴󠁧󠁢󠁳󠁣󠁴󠁿", "flag_wales" => "🏴󠁧󠁢󠁷󠁬󠁳󠁿",
            ];
        }

        $emojiCode = $matches[1]; // Extract the emoji code without colons

        // Check if the emoji code exists in the map
        if (isset($this->emojiMap[$emojiCode])) {
            return [
                'extent' => strlen($matches[0]), // Length of the matched emoji code including colons
                'element' => [
                    'text' => $this->emojiMap[$emojiCode], // Replace emoji code with corresponding emoji
                ],
            ];
        }

        // If no emoji code matches, return null
        return null;
    }


    // Block types
    // -------------------------------------------------------------------------

    /**
     * Parses attribute data for headings.
     *
     * Handles parsing of attribute data for headings if the feature is enabled.
     *
     * @since 0.1.0
     *
     * @param string $attributeString The attribute string to be parsed.
     * @return array The parsed attributes or an empty array if not applicable.
     */
    protected function parseAttributeData($attributeString)
    {
        // Check if special attributes for headings are enabled
        if ($this->config()->get('headings.special_attributes')) {
            return parent::parseAttributeData($attributeString); // Delegate to parent class
        }

        return []; // Return an empty array if the feature is disabled
    }

    /**
     * Handles the parsing of footnote blocks.
     *
     * @since 0.1.0
     *
     * @param array $Line The line to be processed as a footnote.
     * @return mixed The parsed footnote block if enabled, otherwise nothing.
     */
    protected function blockFootnote($Line)
    {
        // Check if footnotes are enabled
        if ($this->config()->get('footnotes')) {
            return parent::blockFootnote($Line); // Delegate to parent class
        }

        return null;
    }

    /**
     * Handles the parsing of definition list blocks.
     *
     * @since 0.1.0
     *
     * @param array $Line The current line to be processed.
     * @param array $Block The current block context.
     * @return mixed The parsed definition list block if enabled, otherwise nothing.
     */
    protected function blockDefinitionList($Line, $Block)
    {
        // Check if definition lists are enabled
        if ($this->config()->get('definition_lists')) {
            return parent::blockDefinitionList($Line, $Block); // Delegate to parent class
        }

        return null;
    }

    /**
     * Handles the parsing of code blocks.
     *
     * @since 0.1.0
     *
     * @param array $Line The current line to be processed.
     * @param array|null $Block The current block context.
     * @return mixed The parsed code block if enabled, otherwise nothing.
     */
    protected function blockCode($Line, $Block = null)
    {
        // Check if code blocks are enabled
        if ($this->config()->get('code') && $this->config()->get('code.blocks')) {
            return parent::blockCode($Line, $Block); // Delegate to parent class
        }

        return null;
    }

    /**
     * Handles the parsing of HTML comment blocks.
     *
     * @since 0.1.0
     *
     * @param array $Line The current line to be processed as a comment.
     * @return mixed The parsed comment block if enabled, otherwise nothing.
     */
    protected function blockComment($Line)
    {
        // Check if HTML comments are enabled
        if ($this->config()->get('comments')) {
            return parent::blockComment($Line); // Delegate to parent class
        }

        return null;
    }

    /**
     * Handles the parsing of list blocks.
     *
     * @since 0.1.0
     *
     * @param array $Line The current line to be processed.
     * @param array|null $CurrentBlock The current block context.
     * @return mixed The parsed list block if enabled, otherwise nothing.
     */
    protected function blockList($Line, ?array $CurrentBlock = null)
    {
        // Check if lists are enabled
        if ($this->config()->get('lists')) {
            return parent::blockList($Line, $CurrentBlock); // Delegate to parent class
        }

        return null;
    }

    /**
     * Handles the parsing of block quote elements.
     *
     * @since 0.1.0
     *
     * @param array $Line The current line to be processed as a block quote.
     * @return mixed The parsed block quote if enabled, otherwise nothing.
     */
    protected function blockQuote($Line)
    {
        // Check if block quotes are enabled
        if ($this->config()->get('quotes')) {
            return parent::blockQuote($Line); // Delegate to parent class
        }

        return null;
    }

    /**
     * Handles the parsing of horizontal rule blocks.
     *
     * @since 0.1.0
     *
     * @param array $Line The current line to be processed.
     * @return mixed The parsed horizontal rule if enabled, otherwise nothing.
     */
    protected function blockRule($Line)
    {
        // Check if thematic breaks (horizontal rules) are enabled
        if ($this->config()->get('thematic_breaks')) {
            return parent::blockRule($Line); // Delegate to parent class
        }

        return null;
    }

    /**
     * Handles the parsing of raw HTML markup blocks.
     *
     * @since 0.1.0
     *
     * @param array $Line The current line to be processed as raw HTML.
     * @return mixed The parsed HTML block if allowed, otherwise nothing.
     */
    protected function blockMarkup($Line)
    {
        // Check if raw HTML is allowed
        if ($this->config()->get('allow_raw_html')) {
            return parent::blockMarkup($Line); // Delegate to parent class
        }

        return null;
    }

    /**
     * Handles the parsing of reference blocks.
     *
     * @since 0.1.0
     *
     * @param array $Line The current line to be processed as a reference.
     * @return mixed The parsed reference block if enabled, otherwise nothing.
     */
    protected function blockReference($Line)
    {
        // Check if references are enabled
        if ($this->config()->get('references')) {
            return parent::blockReference($Line); // Delegate to parent class
        }

        return null;
    }

    /**
     * Handles the parsing of table blocks.
     *
     * @since 0.1.0
     *
     * @param array $Line The current line to be processed.
     * @param array|null $Block The current block context.
     * @return mixed The parsed table block if enabled, otherwise nothing.
     */
    protected function blockTable($Line, $Block = null)
    {
        // Check if tables are enabled
        if ($this->config()->get('tables')) {
            return parent::blockTable($Line, $Block); // Delegate to parent class
        }

        return null;
    }


    /**
     * Processes alert blocks within the parsed Markdown text.
     *
     * This function identifies and processes blocks starting with a specific alert syntax, such as `> [!NOTE]`.
     * Alerts are styled based on their type (e.g., Note, Warning, etc.) and formatted as HTML div elements with appropriate classes.
     *
     * @since 1.3.0
     *
     * @param array $Line The line being processed for an alert block.
     * @return array|null The parsed alert block if matched, otherwise null.
     */
    protected function blockAlert($Line): ?array
    {
        // Check if alerts are enabled in the configuration settings
        if (!$this->config()->get('alerts')) {
            return null; // Return null if alert blocks are disabled
        }

        // Retrieve the alert types from the config (e.g., 'NOTE', 'WARNING')
        $alertTypes = $this->config()->get('alerts.types');

        // Build the regex pattern dynamically based on the alert types
        $alertTypesPattern = implode('|', array_map('strtoupper', $alertTypes));

        // Create the full regex pattern for matching alert block syntax
        $pattern = '/^> \[!(' . $alertTypesPattern . ')\]/';

        // Check if the line matches the alert pattern
        if (preg_match($pattern, $Line['text'], $matches)) {
            $type = strtolower($matches[1]); // Extract the alert type and convert to lowercase
            $title = ucfirst($type); // Capitalize the first letter for the alert title

            // Get class name for alerts from the configuration
            $class = $this->config()->get('alerts.class');

            // Build the alert block with appropriate HTML attributes and content
            return [
                'element' => [
                    'name' => 'div',
                    'attributes' => [
                        'class' => "{$class} {$class}-{$type}", // Add alert type as a class (e.g., 'alert alert-note')
                    ],
                    'handler' => 'elements', // Use 'elements' because we'll be adding more content elements later
                    'text' => [
                        [
                            'name' => 'p',
                            'attributes' => [
                                'class' => "{$class}-title", // Assign title-specific class for the alert
                            ],
                            'text' => $title, // Set the alert title (e.g., "Note")
                        ],
                    ],
                ],
            ]; // Return the parsed alert block
        }

        return null; // Return null if the line does not match the alert pattern
    }

    /**
     * Continues processing alert blocks by adding subsequent lines to the current alert block.
     *
     * @since 1.3.0
     *
     * @param array $Line The current line being processed.
     * @param array $Block The current block being extended.
     * @return array|null The updated alert block or null if the continuation is not applicable.
     */
    protected function blockAlertContinue($Line, array $Block)
    {
        // Retrieve the alert types from the config (e.g., 'NOTE', 'WARNING')
        $alertTypes = $this->config()->get('alerts.types');

        // Build the regex pattern dynamically based on the alert types
        $alertTypesPattern = implode('|', array_map('strtoupper', $alertTypes));

        // Create the full regex pattern for identifying new alert blocks
        $pattern = '/^> \[!(' . $alertTypesPattern . ')\]/';

        // If the line matches a new alert block, terminate the current one
        if (preg_match($pattern, $Line['text'])) {
            return null; // Return null to terminate the current alert block
        }

        // Check if the line continues the current alert block with '>' followed by content
        if ($Line['text'][0] === '>' && preg_match('/^> ?(.*)/', $Line['text'], $matches)) {
            // If the block was interrupted, add an empty paragraph for spacing
            if (isset($Block['interrupted'])) {
                $Block['element']['text'][] = ['text' => ''];
                unset($Block['interrupted']); // Reset the interrupted status
            }

            // Append the new line content to the current block
            $Block['element']['text'][] = [
                'name' => 'p',
                'text' => $matches[1], // Add the text following the '>'
            ];

            return $Block; // Return the updated block
        }

        // If the line does not start with '>' and the block is not interrupted, append it
        if (!isset($Block['interrupted'])) {
            $Block['element']['text'][] = [
                'name' => 'p',
                'text' => $Line['text'], // Add the text directly to the alert block
            ];

            return $Block; // Return the updated block
        }

        return null; // Return null if the continuation conditions are not met
    }

    /**
     * Completes the alert block.
     *
     * @since 1.3.0
     *
     * @param array $Block The current block being finalized.
     * @return array The completed alert block.
     */
    protected function blockAlertComplete($Block)
    {
        return $Block; // Finalize and return the alert block
    }


    // BUG: Breaks formatting if written in a single line

    /**
     * Processes block-level math notation.
     *
     * This function identifies and processes blocks of text surrounded by specific math delimiters (e.g., `$$` or `\\[ ... \\]`)
     * to be formatted as math elements.
     *
     * @since 1.1.2
     *
     * @param array $Line The line being processed for a math block.
     * @return array|null The parsed math block if matched, otherwise null.
     */
    protected function blockMathNotation($Line)
    {
        $config = $this->config();

        // Check if math notation block-level parsing is enabled in the configuration settings
        if (!$config->get('math') || !$config->get('math.block')) {
            return null; // Return null if math block parsing is disabled
        }

        // Iterate over each configured math block delimiter (e.g., `$$`, `\\[`)
        $delimiters = $config->get('math.block.delimiters');
        foreach ($delimiters as $dConfig) {

            // Escape the math delimiters for regex usage
            $leftMarker = preg_quote($dConfig['left'], '/');
            $rightMarker = preg_quote($dConfig['right'], '/');

            // Build the regex pattern to match the opening delimiter, content, and optional closing delimiter
            $regex = '/^(?<!\\\\)('. $leftMarker . ')(.*?)(?:(' . $rightMarker . ')|$)/';

            // Check if the line matches the math block pattern
            if (preg_match($regex, $Line['text'], $matches)) {
                return [
                    'element' => [
                        'text' => $matches[2], // Extract and store the math content between the delimiters
                    ],
                    'start' => $dConfig['left'], // Store the start marker (e.g., `$$`)
                    'end' => $dConfig['right'], // Store the end marker (e.g., `$$`)
                ];
            }
        }

        return null; // Return null if the line does not match any configured math block pattern
    }

    /**
     * Continues processing block-level math notation by adding subsequent lines.
     *
     * This function handles the continuation of a math block until the closing delimiter is found.
     *
     * @since 1.1.2
     *
     * @param array $Line The current line being processed.
     * @param array $Block The current math block being extended.
     * @return array|null The updated math block or null if the continuation is not applicable.
     */
    protected function blockMathNotationContinue($Line, $Block)
    {
        // If the math block is already complete, return null
        if (isset($Block['complete'])) {
            return null;
        }

        // Handle interrupted lines in the math block by adding newlines
        if (isset($Block['interrupted'])) {
            // Convert the 'interrupted' flag to an integer to determine the number of newlines
            $Block['interrupted'] = (int) $Block['interrupted'];

            // Append the appropriate number of newlines to maintain line breaks
            $Block['element']['text'] .= str_repeat("\n", $Block['interrupted']);
            unset($Block['interrupted']); // Reset the interrupted flag
        }

        // Double escape the right marker to properly build the regex pattern for closing delimiter
        $rightMarker = preg_quote($Block['end'], '/');
        $regex = '/^(?<!\\\\)(' . $rightMarker . ')(.*)/';

        // Check if the current line contains the closing delimiter
        if (preg_match($regex, $Line['text'], $matches)) {
            $Block['complete'] = true; // Mark the block as complete
            $Block['math'] = true; // Indicate this is a math block
            $Block['element']['text'] = $Block['start'] . $Block['element']['text'] . $Block['end'] . $matches[2];

            return $Block; // Return the completed block
        }

        // Append the current line's text to the math block
        $Block['element']['text'] .= "\n" . $Line['body'];

        return $Block; // Return the updated block
    }

    /**
     * Completes the block-level math notation.
     *
     * This function is called when a math block is finalized.
     *
     * @since 1.1.2
     *
     * @param array $Block The current block being finalized.
     * @return array The completed math block.
     */
    protected function blockMathNotationComplete($Block)
    {
        return $Block; // Finalize and return the completed math block
    }


    /**
     * Processes fenced code blocks with special handling for extensions like Mermaid and Chart.js.
     *
     * This function extends the standard fenced code block parsing to handle additional languages that may
     * require specific rendering, such as diagrams (e.g., Mermaid, Chart.js). The type of element rendered depends
     * on the specified language, and different HTML elements may be used based on the context.
     *
     * @since 0.1.0
     *
     * @param array $Line The line being processed for a fenced code block.
     * @return array|null The parsed code block or diagram block if applicable, otherwise null.
     */
    protected function blockFencedCode($Line)
    {
        $config = $this->config();

        // Check if code block parsing is enabled in the configuration settings
        if (!$config->get('code') || !$config->get('code.blocks')) {
            return null; // Return null if code block parsing is disabled
        }

        // Use the parent class to parse the fenced code block
        $Block = parent::blockFencedCode($Line);
        $marker = $Line['text'][0]; // Identify the marker character (e.g., backticks)
        $openerLength = strspn($Line['text'], $marker); // Determine the length of the opening markers

        // Extract the language identifier from the fenced code line
        $parts = explode(' ', trim(substr($Line['text'], $openerLength)), 2);
        $language = strtolower($parts[0]); // Convert the language identifier to lowercase

        // Check if diagram support is enabled in the configuration
        if (!$config->get('diagrams')) {
            return $Block; // Return the standard code block if diagrams are disabled
        }

        // Define custom handlers for specific code block extensions like Mermaid and Chart.js
        $extensions = [
            'mermaid' => ['div', 'mermaid'], // Mermaid diagrams rendered inside a <div> with class "mermaid"
            'chart' => ['canvas', 'chartjs'], // Chart.js diagrams rendered inside a <canvas> with class "chartjs"
            // Additional languages can be added here as needed
        ];

        // If the specified language matches one of the configured extensions, customize the element
        if (isset($extensions[$language])) {
            [$elementName, $class] = $extensions[$language]; // Extract the element name and class for the language

            // Return different structures depending on the legacy mode setting
            if (!$this->legacyMode) {
                // Structure for version 1.8 or newer
                return [
                    'char' => $marker, // Store the marker character
                    'openerLength' => $openerLength, // Store the length of the opener
                    'element' => [
                        'name' => $elementName, // Set the element name (e.g., 'div', 'canvas')
                        'element' => [
                            'text' => '', // Placeholder for content
                        ],
                        'attributes' => [
                            'class' => $class, // Add the class for styling (e.g., 'mermaid', 'chartjs')
                        ],
                    ],
                ];
            } else {
                // Structure for version 1.7 or older
                return [
                    'char' => $marker, // Store the marker character
                    'openerLength' => $openerLength, // Store the length of the opener
                    'element' => [
                        'name' => $elementName, // Set the element name (e.g., 'div', 'canvas')
                        'handler' => 'element', // Handler type for processing elements
                        'text' => [
                            'text' => '', // Placeholder for content
                        ],
                        'attributes' => [
                            'class' => $class, // Add the class for styling (e.g., 'mermaid', 'chartjs')
                        ],
                    ],
                ];
            }
        }

        // Return the standard code block if no special handling is needed
        return $Block;
    }


    /**
     * Processes list items, including handling task list syntax for checkboxes.
     *
     * This function processes list items in Markdown and handles special task list syntax (e.g., `- [x]` or `- [ ]`).
     * It converts list items into appropriate HTML markup, rendering checkboxes when task lists are enabled.
     * The function also maintains compatibility with older parsing modes.
     *
     * @since 0.1.0
     *
     * @param array $lines The lines that make up the list item being processed.
     * @return mixed The parsed list item markup, either as a string for legacy mode or as an array of elements.
     */
    protected function li($lines)
    {
        $config = $this->config();

        // Check if task lists are enabled in the configuration settings
        if (!$config->get('lists.tasks')) {
            return parent::li($lines); // Return the default list item if task lists are not enabled
        }

        // Handling for legacy mode (older versions of the parser)
        if ($this->legacyMode) {
            // Generate markup for the list item lines
            $markup = $this->lines($lines);

            // Get first 4 characters of the generated markup to check for a task checkbox
            $firstFourChars = substr($markup, 4, 4);

            // Check if the list item starts with a checkbox (e.g., `[x]` or `[ ]`)
            if (preg_match('/^\[[x ]\]/i', $firstFourChars, $matches)) {
                // Check if the checkbox is checked (`[x]`) or unchecked (`[ ]`)
                $inputAttributes = [
                    'type'     => 'checkbox',
                    'disabled' => 'disabled',
                ];

                if (strtolower($matches[0]) === '[x]') {
                    $inputAttributes['checked'] = 'checked';
                }

                // Build the input element using Parsedown's element structure
                $inputElement = $this->element([
                    'name'       => 'input',
                    'attributes' => $inputAttributes,
                ]);

                // Replace the checkbox marker with the generated input element
                $markup = substr_replace($markup, $inputElement, 4, 4);
            }

            // Trim the markup and handle paragraph tags to format correctly
            $trimmedMarkup = trim($markup);
            if (!in_array('', $lines) && substr($trimmedMarkup, 0, 3) === '<p>') {
                $markup = $trimmedMarkup;
                $markup = substr($markup, 3); // Remove opening paragraph tag

                $position = strpos($markup, "</p>");
                $markup = substr_replace($markup, '', $position, 4); // Remove closing paragraph tag
            }

            return $markup; // Return the final markup for the list item
        } else {
            // Handling for the newer version of the parser
            $Elements = $this->linesElements($lines);

            // Extract the text of the first element to check for a task list checkbox
            $text = $Elements[0]['handler']['argument'];
            $firstFourChars = substr($text, 0, 4);

            // Check if the list item starts with a checkbox (e.g., `[x]` or `[ ]`)
            if (preg_match('/^\[[x ]\]/i', $firstFourChars, $matches)) {
                // Remove the checkbox marker from the beginning of the text
                $Elements[0]['handler']['argument'] = substr_replace($text, '', 0, 4);

                // Set the appropriate attributes based on whether the checkbox is checked or unchecked
                // Prepare attributes for the checkbox element
                $inputAttributes = [
                    'type'     => 'checkbox',
                    'disabled' => 'disabled',
                ];

                if (strtolower($matches[0]) === '[x]') {
                    $inputAttributes['checked'] = 'checked';
                }

                // Insert the checkbox element at the beginning of the list item
                array_unshift($Elements, [
                    'name'       => 'input',
                    'attributes' => $inputAttributes,
                    'autobreak'  => false,
                ]);
            }

            // Remove unnecessary paragraph tags for the list item if not interrupted
            if (!in_array('', $lines) && isset($Elements[1]['name']) && $Elements[1]['name'] === 'p') {
                unset($Elements[1]['name']); // Remove paragraph wrapper
            }

            return $Elements; // Return the final array of elements for the list item
        }
    }


    /**
     * Processes ATX-style headers (e.g., `# Header Text`).
     *
     * This function processes ATX-style headers, checks if the heading levels are allowed, generates an anchor ID for the
     * header, and adds it to the Table of Contents (TOC) if applicable.
     *
     * @since 0.1.0
     *
     * @param array $Line The line being processed to determine if it is a header.
     * @return array|null The parsed header block with added attributes or null if the header is not allowed.
     */
    protected function blockHeader($Line)
    {
        $config = $this->config();

        // Check if headings are enabled in the configuration settings
        if (!$config->get('headings')) {
            return null; // Return null if headings are disabled
        }

        // Use the parent class to parse the header block
        $Block = parent::blockHeader($Line);

        if (!empty($Block)) {
            // Extract the text and level of the header
            $text = $Block['element']['text'] ?? $Block['element']['handler']['argument'] ?? '';
            $level = $Block['element']['name'];

            // Check if the header level is allowed (e.g., h1, h2, etc.)
            if (!in_array($level, $config->get('headings.allowed_levels'))) {
                return null; // Return null if the heading level is not allowed
            }

            // Generate an anchor ID for the header element
            // If an ID attribute is not set, use the text to create the ID
            $id = $Block['element']['attributes']['id'] ?? $text;
            $id = $this->createAnchorID($id);

            // Set the 'id' attribute for the header element
            $Block['element']['attributes'] = ['id' => $id];

            // Check if the heading level should be included in the Table of Contents (TOC)
            // Also ensure we skip adding it to TOC if it is disabled in the config
            if (!$config->get('toc') || !in_array($level, $config->get('toc.levels'))) {
                return $Block; // Return the block if it should not be part of the TOC
            }

            // Add the heading to the Table of Contents
            $this->setContentsList(['text' => $text, 'id' => $id, 'level' => $level]);

            return $Block; // Return the modified header block
        }

        return null; // Return null if the header block is empty
    }

    /**
     * Processes Setext-style headers (e.g., `Header Text` followed by `===` or `---`).
     *
     * This function processes Setext-style headers, checks if the heading levels are allowed, generates an anchor ID for the
     * header, and adds it to the Table of Contents (TOC) if applicable.
     *
     * @since 0.1.0
     *
     * @param array $Line The line being processed for a Setext header.
     * @param array|null $Block The existing block context (if any).
     * @return array|null The parsed Setext header block with added attributes or null if the header is not allowed.
     */
    protected function blockSetextHeader($Line, $Block = null)
    {
        $config = $this->config();

        // Check if headings are enabled in the configuration settings
        if (!$config->get('headings')) {
            return null; // Return null if headings are disabled
        }

        // Use the parent class to parse the Setext header block
        $Block = parent::blockSetextHeader($Line, $Block);

        if (!empty($Block)) {
            // Extract the text and level of the header
            $text = $Block['element']['text'] ?? $Block['element']['handler']['argument'] ?? '';
            $level = $Block['element']['name'];

            // Check if the header level is allowed (e.g., h1, h2, etc.)
            if (!in_array($level, $config->get('headings.allowed_levels'))) {
                return null; // Return null if the heading level is not allowed
            }

            // Generate an anchor ID for the header element
            // If an ID attribute is not set, use the text to create the ID
            $id = $Block['element']['attributes']['id'] ?? $text;
            $id = $this->createAnchorID($id);

            // Set the 'id' attribute for the header element
            $Block['element']['attributes'] = ['id' => $id];

            // Check if the heading level should be included in the Table of Contents (TOC)
            // Also ensure we skip adding it to TOC if it is disabled in the config
            if (!$config->get('toc') || !in_array($level, $config->get('toc.levels'))) {
                return $Block; // Return the block if it should not be part of the TOC
            }

            // Add the heading to the Table of Contents
            $this->setContentsList(['text' => $text, 'id' => $id, 'level' => $level]);

            return $Block; // Return the modified Setext header block
        }

        return null; // Return null if the Setext header block is empty
    }


    /**
     * Processes abbreviation blocks.
     *
     * This function handles the parsing of abbreviation definitions. It checks if abbreviations are enabled
     * in the configuration and whether custom abbreviations are allowed. If custom abbreviations are allowed,
     * it delegates the parsing to the parent class method.
     *
     * @since 0.1.0
     *
     * @param array $Line The line being processed to determine if it defines an abbreviation.
     * @return array|null The parsed abbreviation block or null if abbreviations are disabled or custom abbreviations are not allowed.
     */
    protected function blockAbbreviation($Line)
    {
        $config = $this->config();

        // Check if abbreviation support is enabled in the configuration settings
        if ($config->get('abbreviations')) {

            // If custom abbreviations are allowed, delegate to the parent class to handle parsing
            if ($config->get('abbreviations.allow_custom')) {
                return parent::blockAbbreviation($Line); // Parse custom abbreviation using parent method
            }

            // If custom abbreviations are not allowed, return null to prevent processing
            return null;
        }

        // Return null if abbreviations are completely disabled in the configuration
        return null;
    }


    /**
     * Completes the processing of table blocks.
     *
     * This function processes table blocks after the initial parsing to handle special features such as column spans
     * and row spans. It processes each cell in the table, merging cells where indicated by specific characters
     * (e.g., '>' for colspan and '^' for rowspan). The implementation handles both legacy and modern parsing modes.
     *
     * @since 1.0.1
     *
     * @param array $block The parsed table block to be processed further.
     * @return array The completed and modified table block.
     */
    protected function blockTableComplete(array $block): array
    {
        // Check if table spanning (colspan and rowspan) is enabled
        if (!$this->config()->get('tables.tablespan')) {
            return $block; // Return the original block if spanning is not enabled
        }

        // Reference to header elements depending on legacy mode or newer version
        if ($this->legacyMode === true) {
            // Version 1.7
            $headerElements = &$block['element']['text'][0]['text'][0]['text'];
        } else {
            // Version 1.8
            $headerElements = &$block['element']['elements'][0]['elements'][0]['elements'];
        }

        // Process colspan in header elements
        for ($index = count($headerElements) - 1; $index >= 0; --$index) {
            $colspan = 1;
            $headerElement = &$headerElements[$index];

            if ($this->legacyMode === true) {
                // Version 1.7
                while ($index && $headerElements[$index - 1]['text'] === '>') {
                    $colspan++;
                    $PreviousHeaderElement = &$headerElements[--$index];
                    $PreviousHeaderElement['merged'] = true;
                    if (isset($PreviousHeaderElement['attributes'])) {
                        $headerElement['attributes'] = $PreviousHeaderElement['attributes'];
                    }
                }
            } else {
                // Version 1.8
                while ($index && '>' === $headerElements[$index - 1]['handler']['argument']) {
                    $colspan++;
                    $PreviousHeaderElement = &$headerElements[--$index];
                    $PreviousHeaderElement['merged'] = true;
                    if (isset($PreviousHeaderElement['attributes'])) {
                        $headerElement['attributes'] = $PreviousHeaderElement['attributes'];
                    }
                }
            }

            // Assign colspan attribute if colspan is greater than 1
            if ($colspan > 1) {
                if (!isset($headerElement['attributes'])) {
                    $headerElement['attributes'] = [];
                }
                $headerElement['attributes']['colspan'] = $colspan;
            }
        }

        // Remove merged header elements
        for ($index = count($headerElements) - 1; $index >= 0; --$index) {
            if (isset($headerElements[$index]['merged'])) {
                array_splice($headerElements, $index, 1);
            }
        }

        // Reference to table rows based on legacy or modern mode
        if ($this->legacyMode === true) {
            // Version 1.7
            $rows = &$block['element']['text'][1]['text'];
        } else {
            // Version 1.8
            $rows = &$block['element']['elements'][1]['elements'];
        }

        // Process colspan for rows
        foreach ($rows as &$row) {
            if ($this->legacyMode === true) {
                // Version 1.7
                $elements = &$row['text'];
            } else {
                // Version 1.8
                $elements = &$row['elements'];
            }

            for ($index = count($elements) - 1; $index >= 0; --$index) {
                $colspan = 1;
                $element = &$elements[$index];

                if ($this->legacyMode === true) {
                    // Version 1.7
                    while ($index && $elements[$index - 1]['text'] === '>') {
                        $colspan++;
                        $PreviousElement = &$elements[--$index];
                        $PreviousElement['merged'] = true;
                        if (isset($PreviousElement['attributes'])) {
                            $element['attributes'] = $PreviousElement['attributes'];
                        }
                    }
                } else {
                    // Version 1.8
                    while ($index && '>' === $elements[$index - 1]['handler']['argument']) {
                        ++$colspan;
                        $PreviousElement = &$elements[--$index];
                        $PreviousElement['merged'] = true;
                        if (isset($PreviousElement['attributes'])) {
                            $element['attributes'] = $PreviousElement['attributes'];
                        }
                    }
                }

                // Assign colspan attribute if colspan is greater than 1
                if ($colspan > 1) {
                    if (!isset($element['attributes'])) {
                        $element['attributes'] = [];
                    }
                    $element['attributes']['colspan'] = $colspan;
                }
            }
        }

        // Process rowspan for rows
        foreach ($rows as $rowNo => &$row) {
            if ($this->legacyMode === true) {
                // Version 1.7
                $elements = &$row['text'];
            } else {
                // Version 1.8
                $elements = &$row['elements'];
            }

            foreach ($elements as $index => &$element) {
                $rowspan = 1;

                if (isset($element['merged'])) {
                    continue; // Skip merged elements
                }

                if ($this->legacyMode === true) {
                    // Version 1.7
                    while (
                        $rowNo + $rowspan < count($rows) &&
                        $index < count($rows[$rowNo + $rowspan]['text']) &&
                        $rows[$rowNo + $rowspan]['text'][$index]['text'] === '^' &&
                        (@$element['attributes']['colspan'] ?: null) === (@$rows[$rowNo + $rowspan]['text'][$index]['attributes']['colspan'] ?: null)
                    ) {
                        $rows[$rowNo + $rowspan]['text'][$index]['merged'] = true;
                        $rowspan++;
                    }
                } else {
                    // Version 1.8
                    while (
                        $rowNo + $rowspan < count($rows) &&
                        $index < count($rows[$rowNo + $rowspan]['elements']) &&
                        '^' === $rows[$rowNo + $rowspan]['elements'][$index]['handler']['argument'] &&
                        (@$element['attributes']['colspan'] ?: null) === (@$rows[$rowNo + $rowspan]['elements'][$index]['attributes']['colspan'] ?: null)
                    ) {
                        $rows[$rowNo + $rowspan]['elements'][$index]['merged'] = true;
                        $rowspan++;
                    }
                }

                // Assign rowspan attribute if rowspan is greater than 1
                if ($rowspan > 1) {
                    if (!isset($element['attributes'])) {
                        $element['attributes'] = [];
                    }
                    $element['attributes']['rowspan'] = $rowspan;
                }
            }
        }

        // Remove merged elements after processing row spans
        foreach ($rows as &$row) {
            if ($this->legacyMode === true) {
                // Version 1.7
                $elements = &$row['text'];
            } else {
                // Version 1.8
                $elements = &$row['elements'];
            }

            for ($index = count($elements) - 1; $index >= 0; --$index) {
                if (isset($elements[$index]['merged'])) {
                    array_splice($elements, $index, 1); // Remove merged element
                }
            }
        }

        return $block; // Return the completed and modified table block
    }


    // Functions related to Table of Contents
    // Modified version of ToC by @KEINOS
    // -------------------------------------------------------------------------

    /**
     * Parses the provided text and handles escaping/unescaping of ToC tags.
     *
     * This function processes the given text, escaping the ToC tags temporarily,
     * parsing the Markdown text into HTML, and then unescaping the ToC tags to
     * include them in the final output.
     *
     * @since 1.0.0
     *
     * @param string $text The input Markdown text to be parsed.
     * @return string The parsed HTML text with ToC tags properly handled.
     */
    public function body(string $text): string
    {
        /**
         * Reset the internal state for Table of Contents to avoid data persisting
         * when the same instance parses multiple markdown strings.
         */
        $this->anchorRegister = [];
        $this->contentsListArray = [];
        $this->contentsListString = '';
        $this->firstHeadLevel = 0;
        $this->predefinedAbbreviationsAdded = false;

        $text = $this->encodeTag($text); // Escapes ToC tag temporarily
        $html = parent::text($text);     // Parses the markdown text
        return $this->decodeTag($html);  // Unescapes the ToC tag
    }

    /**
     * Retrieves the Table of Contents (ToC) in the specified format.
     *
     * This function returns the ToC either as a formatted string or as a JSON
     * string. If an unknown type is provided, an exception is thrown.
     *
     * @since 1.0.0
     *
     * @param string $type_return The desired return format: 'string' or 'json'.
     * @return string The Table of Contents in the specified format.
     * @throws \InvalidArgumentException If an unknown return type is provided.
     */
    public function contentsList(string $type_return = 'string'): string
    {

        switch (strtolower($type_return)) {
            case 'string':
                return $this->contentsListString ? $this->body($this->contentsListString) : '';
            case 'json':
                return json_encode($this->contentsListArray);
            default:
                $backtrace = debug_backtrace();
                $caller = $backtrace[1] ?? $backtrace[0];
                $errorMessage = "Unknown return type '{$type_return}' given while parsing ToC. Called in " . ($caller['file'] ?? 'unknown') . " on line " . ($caller['line'] ?? 'unknown');
                throw new \InvalidArgumentException($errorMessage);
        }
    }

    /**
     * Sets a callback function for creating anchor IDs for headers.
     *
     * This allows the user to provide custom logic for generating anchor IDs for
     * the headers found in the Markdown content.
     *
     * @since 1.2.0
     *
     * @param callable $callback The callback function to generate anchor IDs.
     * @return void
     */
    public function setCreateAnchorIDCallback(callable $callback): void
    {
        $this->createAnchorIDCallback = $callback;
    }

    /**
     * Creates an anchor ID for a given header text.
     *
     * This function generates a unique anchor ID for a header, allowing for custom
     * callbacks to be used for the generation logic. If no callback is provided,
     * default logic is used, including transliteration, normalization, and sanitization.
     *
     * @since 1.0.0
     *
     * @param string $text The header text for which an anchor ID is generated.
     * @return string|null The generated anchor ID or null if auto anchors are disabled.
     */
    protected function createAnchorID(string $text): ?string
    {
        $config = $this->config();

        // Check if automatic anchor generation is enabled in the settings
        if (!$config->get('headings.auto_anchors')) {
            return null; // Return null if auto anchors are disabled
        }

        // If a user-defined callback is provided, use it to generate the anchor ID
        if (is_callable($this->createAnchorIDCallback)) {
            return call_user_func($this->createAnchorIDCallback, $text, $this->config());
        }

        // Convert text to lowercase if configured to do so
        if ($config->get('headings.auto_anchors.lowercase')) {
            if (extension_loaded('mbstring')) {
                $text = mb_strtolower($text);
            } else {
                $text = strtolower($text);
            }
        }

        // Apply replacements to the text based on the configuration settings
        if ($config->get('headings.auto_anchors.replacements')) {
            $text = preg_replace(array_keys($config->get('headings.auto_anchors.replacements')), $config->get('headings.auto_anchors.replacements'), $text);
        }

        // Normalize the text (ensure proper encoding)
        $text = $this->normalizeString($text);

        // Transliterate text if configured to do so
        if ($config->get('headings.auto_anchors.transliterate')) {
            $text = $this->transliterate($text);
        }

        // Sanitize the text to make it a valid anchor ID
        $text = $this->sanitizeAnchor($text);

        // Ensure the generated anchor ID is unique
        return $this->uniquifyAnchorID($text);
    }

    /**
     * Normalizes the given string to UTF-8 encoding.
     *
     * This function ensures that the given text is properly encoded to UTF-8, using
     * `mb_convert_encoding` if available. If `mbstring` is not available, it returns
     * the raw string as there is no equivalent alternative.
     *
     * @since 1.2.0
     *
     * @param string $text The input string to be normalized.
     * @return string The normalized string.
     */
    protected function normalizeString(string $text)
    {
        if (extension_loaded('mbstring')) {
            return mb_convert_encoding($text, 'UTF-8', mb_list_encodings());
        } else {
            return $text; // Return raw text as there is no good alternative for mb_convert_encoding
        }
    }

    /**
     * Transliterates the given string to ASCII format.
     *
     * This function attempts to transliterate text to ASCII, making it suitable for
     * use in anchor IDs. It uses PHP's `Transliterator` class if available. If not,
     * a manual transliteration method is used as a fallback.
     *
     * @since 1.2.0
     *
     * @param string $text The text to be transliterated.
     * @return string The transliterated text.
     */
    protected function transliterate(string $text): string
    {
        if (class_exists('\Transliterator')) {
            $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII;');
            if ($transliterator) {
                return $transliterator->transliterate($text);
            }
        }

        return $this->manualTransliterate($text); // Use manual transliteration if `Transliterator` is not available
    }



    /**
     * Manually transliterates a string from various alphabets to ASCII.
     *
     * This function converts characters from different scripts (Latin, Greek, Cyrillic, etc.) into their ASCII equivalents.
     * It uses a predefined character map to replace accented or special characters with simpler ASCII versions.
     *
     * @since 1.3.0
     *
     * @param string $text The input text to be transliterated.
     * @return string The transliterated ASCII string.
     */
    protected function manualTransliterate(string $text): string
    {
        // Character mapping from different alphabets to their ASCII equivalents
        $characterMap = [
            // Latin
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'AA', 'Æ' => 'AE', 'Ç' => 'C',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
            'Ø' => 'OE', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
            'ß' => 'ss',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'aa', 'æ' => 'ae', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
            'ø' => 'oe', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
            'ÿ' => 'y',

            // Latin symbols
            '©' => '(c)', '®' => '(r)', '™' => '(tm)',

            // Greek
            'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => 'TH',
            'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => 'X', 'Ο' => 'O', 'Π' => 'P',
            'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'O',
            'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'O', 'Ϊ' => 'I',
            'Ϋ' => 'Y',
            'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => 'th',
            'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => 'x', 'ο' => 'o', 'π' => 'p',
            'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'o',
            'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'o', 'ς' => 's',
            'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',

            // Turkish
            'Ş' => 'S', 'İ' => 'I', 'Ğ' => 'G',
            'ş' => 's', 'ı' => 'i', 'ğ' => 'g',

            // Russian
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
            'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
            'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'Kh', 'Ц' => 'Ts',
            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Shch', 'Ъ' => 'U', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
            'Я' => 'Ya',
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
            'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
            'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ъ' => 'u', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
            'я' => 'ya',

            // Ukrainian
            'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
            'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',

            // Czech
            'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U',
            'Ž' => 'Z',
            'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
            'ž' => 'z',

            // Polish
            'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'E', 'Ł' => 'L', 'Ń' => 'N', 'Ś' => 'S', 'Ź' => 'Z',
            'Ż' => 'Z',
            'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ś' => 's', 'ź' => 'z',
            'ż' => 'z',

            // Latvian
            'Ā' => 'A', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'I', 'Ķ' => 'K', 'Ļ' => 'L', 'Ņ' => 'N', 'Ū' => 'U',
            'ā' => 'a', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n', 'ū' => 'u',
        ];

        // Perform the character replacements based on the map
        return strtr($text, $characterMap);
    }

    /**
     * Sanitizes a string to make it suitable for use as an HTML anchor ID.
     *
     * This function replaces non-alphanumeric characters in the string with a delimiter
     * (e.g., hyphen), ensuring the result is suitable as an HTML ID. Consecutive delimiters
     * are collapsed into a single delimiter, and leading/trailing delimiters are trimmed.
     *
     * @since 1.2.0
     *
     * @param string $text The input text to be sanitized.
     * @return string The sanitized string suitable for use as an anchor ID.
     */
    protected function sanitizeAnchor(string $text): string
    {
        $config = $this->config();

        // Get the delimiter used to replace non-alphanumeric characters (e.g., '-')
        $delimiter = $config->get('headings.auto_anchors.delimiter');

        // Replace any character that is not a letter or number with the delimiter
        $text = preg_replace('/[^\p{L}\p{Nd}]+/u', $delimiter, $text);

        // Collapse consecutive delimiters into a single delimiter
        $text = preg_replace('/(' . preg_quote($delimiter, '/') . '){2,}/', '$1', $text);

        // Trim any leading or trailing delimiters
        return trim($text, $delimiter);
    }

    /**
     * Ensures that the generated anchor ID is unique.
     *
     * This function keeps track of generated anchor IDs to avoid duplicates. If an anchor ID has already been used,
     * it appends a unique suffix to it. Blacklisted anchor IDs are also skipped to ensure the final anchor is valid.
     *
     * @since 1.2.0
     *
     * @param string $text The base anchor ID text.
     * @return string A unique anchor ID.
     */
    protected function uniquifyAnchorID(string $text): string
    {
        // Retrieve the blacklist of forbidden anchor IDs from the configuration
        $config = $this->config();
        $blacklist = $config->get('headings.auto_anchors.blacklist');

        // Store the original text to use as the base for creating unique variants
        $originalText = $text;

        // Initialize or increment the counter for this specific anchor text
        if (!isset($this->anchorRegister[$text])) {
            $this->anchorRegister[$text] = 0;
        } else {
            $this->anchorRegister[$text]++;
        }

        // Adjust the anchor ID to ensure it is unique and not in the blacklist
        while (true) {
            // Generate the potential anchor ID with the count as suffix (if needed)
            $potentialId = $originalText . ($this->anchorRegister[$text] > 0 ? '-' . $this->anchorRegister[$text] : '');

            // Check if the potential ID is not blacklisted
            if (!in_array($potentialId, $blacklist)) {
                break; // The ID is valid and not blacklisted, so we can use it
            }

            // Increment the counter to generate the next potential ID
            $this->anchorRegister[$text]++;
        }

        // If no suffix is required, return the original anchor text
        if ($this->anchorRegister[$text] === 0) {
            return $originalText;
        }

        // Return the unique anchor ID with the appropriate suffix
        return $originalText . '-' . $this->anchorRegister[$text];
    }


    /**
     * Decodes the ToC tag by replacing a hashed version with the original tag.
     *
     * This function looks for the hashed ToC tag within the text and replaces it with the original ToC tag,
     * effectively decoding the tag back to its original form.
     *
     * @since 1.2.0
     *
     * @param string $text The input text containing the hashed ToC tag.
     * @return string The text with the hashed ToC tag replaced by the original tag.
     */
    protected function decodeTag(string $text): string
    {
        $config = $this->config();
        $salt = $this->getSalt(); // Retrieve the salt used for hashing
        $tag_origin = $config->get('toc.tag'); // Get the original ToC tag
        $tag_hashed = hash('sha256', $salt . $tag_origin); // Generate the hashed version of the ToC tag

        // If the hashed tag is not found, return the original text
        if (strpos($text, $tag_hashed) === false) {
            return $text;
        }

        // Replace the hashed tag with the original tag
        return str_replace($tag_hashed, $tag_origin, $text);
    }

    /**
     * Encodes the ToC tag by replacing it with a hashed version.
     *
     * This function looks for the original ToC tag in the text and replaces it with a hashed version,
     * effectively encoding it to avoid conflicts during parsing.
     *
     * @since 1.2.0
     *
     * @param string $text The input text containing the ToC tag.
     * @return string The text with the original ToC tag replaced by the hashed version.
     */
    protected function encodeTag(string $text): string
    {
        $config = $this->config();
        $salt = $this->getSalt(); // Retrieve the salt used for hashing
        $tag_origin = $config->get('toc.tag'); // Get the original ToC tag

        // If the original tag is not found, return the original text
        if (strpos($text, $tag_origin) === false) {
            return $text;
        }

        // Generate the hashed version of the ToC tag and replace the original tag
        $tag_hashed = hash('sha256', $salt . $tag_origin);
        return str_replace($tag_origin, $tag_hashed, $text);
    }

    /**
     * Fetches plain text from a given input by stripping tags.
     *
     * This function parses the given text using line formatting, then strips any HTML tags and trims whitespace,
     * effectively extracting plain text.
     *
     * @since 1.0.0
     *
     * @param string $text The input text to be fetched.
     * @return string The plain text version of the input.
     */
    protected function fetchText($text): string
    {
        return trim(strip_tags($this->line($text)));
    }

    /**
     * Generates or retrieves a salt value for use in hashing.
     *
     * This function generates a unique salt value based on the current timestamp if it hasn't been set yet.
     * The salt is used to create a unique hash for ToC tags, making them harder to predict.
     *
     * @since 1.0.0
     *
     * @return string The generated or retrieved salt value.
     */
    protected function getSalt(): string
    {
        static $salt;
        if (isset($salt)) {
            return $salt; // Return the previously generated salt
        }

        // Generate a new salt based on the current timestamp
        $salt = hash('md5', (string) time());
        return $salt;
    }

    /**
     * Adds an entry to the contents list in both array and string formats.
     *
     * This function stores a representation of the contents as both an array and a formatted string.
     * The array format can be used for structured data, while the string format is used for Markdown.
     *
     * @since 1.0.0
     *
     * @param array $Content The content entry containing 'text', 'id', and 'level' keys.
     * @return void
     */
    protected function setContentsList(array $Content): void
    {
        // Stores content as an array
        $this->setContentsListAsArray($Content);
        // Stores content as a string in Markdown list format
        $this->setContentsListAsString($Content);
    }

    /**
     * Stores the given content entry in the Table of Contents array.
     *
     * This function adds the content entry to the `contentsListArray`, which is used to hold a structured
     * representation of all ToC entries.
     *
     * @since 1.0.0
     *
     * @param array $Content The content entry to be stored.
     * @return void
     */
    protected function setContentsListAsArray(array $Content): void
    {
        $this->contentsListArray[] = $Content; // Append content to the contents list array
    }

    /**
     * Adds the given content entry to the Table of Contents string.
     *
     * This function creates a formatted Markdown list item for the content and appends it to the
     * Table of Contents string, which is used to generate the ToC in Markdown format.
     *
     * @since 1.0.0
     *
     * @param array $Content The content entry containing 'text', 'id', and 'level' keys.
     * @return void
     */
    protected function setContentsListAsString(array $Content): void
    {
        $text = $this->fetchText($Content['text']); // Fetch the plain text of the content
        $id = $Content['id']; // Get the ID of the content
        $level = (int) trim($Content['level'], 'h'); // Get the level of the heading and convert to an integer
        $link = "[{$text}](#{$id})"; // Create a Markdown link to the heading

        // Set the first heading level if it hasn't been set yet
        if ($this->firstHeadLevel === 0) {
            $this->firstHeadLevel = $level;
        }

        // Calculate the indent level for the list item
        $indentLevel = max(1, $level - ($this->firstHeadLevel - 1));
        $indent = str_repeat('  ', $indentLevel); // Create the appropriate indent based on the level

        // Append the formatted list item to the contents list string
        $this->contentsListString .= "{$indent}- {$link}" . PHP_EOL;
    }

    /**
     * Parses the given Markdown text and replaces the ToC tag with the generated Table of Contents.
     *
     * This function calls the `body()` method to parse Markdown, and then replaces the placeholder
     * ToC tag with the generated Table of Contents in HTML format.
     *
     * @since 0.1.0
     *
     * @param string $text The input Markdown text.
     * @return string The parsed HTML text with the ToC embedded.
     */
    public function text($text): string
    {
        $config = $this->config();
        $html = $this->body($text); // Parse the Markdown text into HTML

        // If ToC functionality is disabled in the config, return the parsed HTML as is
        if (!$config->get('toc')) {
            return $html;
        }

        // Get the original ToC tag and check if it is in the input text
        $tag_origin = $config->get('toc.tag');
        if (strpos($text, $tag_origin) === false) {
            return $html; // Return HTML if the ToC tag is not found
        }

        // Replace the ToC placeholder with the actual ToC content
        $toc_data = $this->contentsList();
        $toc_id = $config->get('toc.id');
        return str_replace("<p>{$tag_origin}</p>", "<div id=\"{$toc_id}\">{$toc_data}</div>", $html);
    }

    /**
     * Processes unmarked text, adding predefined abbreviations if configured.
     *
     * This function extends the parent class's functionality by adding predefined
     * abbreviations from the configuration, before processing the unmarked text.
     *
     * @since 0.1.0
     *
     * @param string $text The input text to be processed.
     * @return string The processed text with abbreviations applied.
     */
    protected function unmarkedText($text)
    {
        $config = $this->config();

        if (!$this->predefinedAbbreviationsAdded) {
            // Add predefined abbreviations to the definition data once per parse
            foreach ($config->get('abbreviations.predefined') as $abbreviation => $description) {
                $this->DefinitionData['Abbreviation'][$abbreviation] = $description;
            }
            $this->predefinedAbbreviationsAdded = true;
        }

        // Call the parent method to handle the rest of the text processing
        return parent::unmarkedText($text);
    }

    // Helper functions
    // -------------------------------------------------------------------------

    /**
     * Registers an inline type marker with a corresponding handler function.
     *
     * This function ensures that a given marker is registered for inline parsing, associating it with
     * a handler function that will handle the inline behavior for that marker.
     *
     * @since 1.1.2
     *
     * @param mixed $markers One or more markers to register (can be a string or an array).
     * @param string $funcName The name of the handler function associated with the marker(s).
     * @return void
     */
    private function addInlineType($markers, string $funcName): void
    {
        // Ensure $markers is always an array, even if a single marker is passed as a string
        $markers = (array) $markers;

        foreach ($markers as $marker) {
            // If the marker is not already registered, initialize it
            if (!isset($this->InlineTypes[$marker])) {
                $this->InlineTypes[$marker] = [];
            }

            // Add the marker to the special characters array if it's not already present
            if (!in_array($marker, $this->specialCharacters)) {
                $this->specialCharacters[] = $marker;
            }

            // Add the function name to the beginning of the marker's handlers for priority
            array_unshift($this->InlineTypes[$marker], $funcName);

            // Append the marker to the inline marker list
            $this->inlineMarkerList .= $marker;
        }
    }

    /**
     * Registers a block type marker with a corresponding handler function.
     *
     * This function ensures that a given marker is registered for block parsing, associating it with
     * a handler function that will handle the block behavior for that marker.
     *
     * @since 1.1.2
     *
     * @param mixed $markers One or more markers to register (can be a string or an array).
     * @param string $funcName The name of the handler function associated with the marker(s).
     * @return void
     */
    private function addBlockType($markers, string $funcName): void
    {
        // Ensure $markers is always an array, even if a single marker is passed as a string
        $markers = (array) $markers;

        foreach ($markers as $marker) {
            // If the marker is not already registered, initialize it
            if (!isset($this->BlockTypes[$marker])) {
                $this->BlockTypes[$marker] = [];
            }

            // Add the marker to the special characters array if it's not already present
            if (!in_array($marker, $this->specialCharacters)) {
                $this->specialCharacters[] = $marker;
            }

            // Add the function name to the beginning of the marker's handlers for priority
            array_unshift($this->BlockTypes[$marker], $funcName);
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
     * Returns a singleton configuration handler object for managing feature flags and payload settings.
     *
     * The handler provides methods to get, set, and export configuration values, supporting both bitmask-based
     * feature toggles and arbitrary payload data. The configuration schema and path-to-bit mapping are provided
     * statically. The handler validates types and throws exceptions for invalid paths or types.
     *
     * @return object Configuration handler with the following public methods:
     *                - get(string $path): mixed
     *                - set(string|array $path, mixed $value = null): self
     *                - export(): array
     *                - bind(int &$features, array &$payload): void
     *
     * @throws \InvalidArgumentException If an invalid config path or type is provided to set().
     */
    public function config(): object
    {
        static $handler = null;

        if ($handler === null) {
            $handler = new class(self::$PATH_TO_BIT, self::$FLAT_SCHEMA) {
                private array $p2b;
                private array $schema;
                private int   $features;
                private array $payload;

                public function __construct(array $p2b, array $schema)
                {
                    $this->p2b    = $p2b;
                    $this->schema = $schema;
                }
                public function bind(int &$f, array &$p): void
                {
                    $this->features = &$f;
                    $this->payload  = &$p;
                }

                /* ---------------------------- GET ----------------------- */
                public function get(string $path)
                {
                    $path = $this->normalisePath($path);
                    if (!isset($this->schema[$path])) {
                        throw new \InvalidArgumentException("Invalid config path: {$path}");
                    }
                    if (isset($this->p2b[$path])) {
                        return ( ($this->features & $this->p2b[$path]) !== 0 );
                    }
                    return $this->payload[$path] ?? null;
                }

                /* ---------------------------- SET ----------------------- */
                public function set($path, $value = null): self
                {
                    if (is_array($path)) {
                        foreach ($path as $k => $v) { $this->set($k, $v); }
                        return $this;
                    }

                    if (is_string($path) && is_array($value) && !isset($this->schema[$path])) {
                        $prefix = $path . '.';
                        $hasChild = false;
                        foreach ($this->schema as $key => $_) {
                            if (strpos($key, $prefix) === 0) { $hasChild = true; break; }
                        }
                        if ($hasChild) {
                            foreach ($value as $k => $v) { $this->set($prefix . $k, $v); }
                            return $this;
                        }
                    }

                    $path = $this->normalisePath($path);

                    if (!isset($this->schema[$path])) {
                        throw new \InvalidArgumentException("Invalid config path: {$path}");
                    }
                    $this->validate($value, $this->schema[$path]['type']);

                    if (isset($this->p2b[$path])) {
                        $bit = $this->p2b[$path];
                        $this->features = $value ? ($this->features | $bit)
                                                  : ($this->features & ~$bit);
                    } else {
                        $this->payload[$path] = $value;
                    }
                    return $this;
                }

                public function export(): array
                {
                    $flat = $this->payload;
                    foreach ($this->p2b as $p => $b) {
                        $flat[$p] = (($this->features & $b) !== 0);
                    }
                    return $flat;
                }

                /* -------------------- helpers --------------------------- */
                private function normalisePath(string $p): string
                {
                    if (!isset($this->schema[$p]) && isset($this->schema[$p . '.enabled'])) {
                        return $p . '.enabled';
                    }
                    return $p;
                }
                private function validate($val, string $expected): void
                {
                    $actual = gettype($val);
                    if ($expected !== $actual) {
                        throw new \InvalidArgumentException("Expected {$expected}, got {$actual}");
                    }
                }
            };
        }
        $handler->bind($this->features, $this->payload);
        return $handler;
    }

    /**
     * Compiles the configuration schema by recursively traversing the schema definition.
     *
     * This method walks through the CONFIG_SCHEMA_DEFAULT array, registering boolean options
     * and payloads for each configuration path. For associative array branches, it registers
     * an "enabled" boolean (defaulting to true unless specified), and recurses into child nodes.
     * For leaf nodes, it distinguishes between booleans (registered as boolean options) and
     * other types (registered as payloads).
     *
     * @return void
     */
    private function compileSchema(): void
    {
        $bitIndex = 0;

        $walk = function (array $node, string $prefix = '') use (&$walk, &$bitIndex): void {
            foreach ($node as $k => $v) {
                $path = $prefix === '' ? $k : $prefix . '.' . $k;

                // branch (associative => object)
                if (is_array($v) && $v !== [] && array_keys($v) !== range(0, count($v) - 1)) {
                    // implicit enabled=true unless provided
                    $enabledDefault = true;
                    if (array_key_exists('enabled', $v)) {
                        $enabledDefault = (bool)$v['enabled'];
                    }
                    $this->registerBoolean("{$path}.enabled", $enabledDefault, $bitIndex);
                    if (array_key_exists('enabled', $v)) {
                        unset($v['enabled']); // don't recurse into it
                    }
                    $walk($v, $path);
                    continue;
                }

                // leaf boolean
                if (is_bool($v)) {
                    $this->registerBoolean($path, $v, $bitIndex);
                    continue;
                }

                // leaf non‑boolean (string, int, array, …)
                $this->registerPayload($path, $v);
            }
        };

        $walk(self::CONFIG_SCHEMA_DEFAULT);
    }

    /**
     * Registers a boolean feature flag with a unique bit index and default value.
     *
     * Maps the given feature path to a bit position, stores its schema, and updates
     * the default bits if the feature is enabled by default. Throws an exception if
     * more than 64 boolean features are registered.
     *
     * @param string $path      The unique path identifying the feature.
     * @param bool   $default   The default value for the feature (enabled or disabled).
     * @param int    &$bitIndex Reference to the current bit index, incremented after assignment.
     *
     * @throws \RuntimeException If more than 64 boolean features are registered.
     */
    private function registerBoolean(string $path, bool $default, int &$bitIndex): void
    {
        if ($bitIndex > 63) {
            throw new \RuntimeException('Exceeded 64 boolean feature bits');
        }
        $bit = 1 << $bitIndex++;
        self::$PATH_TO_BIT[$path] = $bit;
        self::$BIT_TO_PATH[$bit]  = $path;
        self::$FLAT_SCHEMA[$path] = ['type' => 'boolean', 'default' => $default];
        if ($default) {
            self::$DEFAULT_BITS |= $bit;
        }
    }

    /**
     * Registers a payload path with its default value and type in the schema.
     *
     * @param string $path    The unique path identifier for the payload.
     * @param mixed  $default The default value to associate with the payload path.
     *
     * @return void
     */
    private function registerPayload(string $path, $default): void
    {
        self::$FLAT_SCHEMA[$path]   = ['type' => gettype($default), 'default' => $default];
        self::$DEFAULT_PAYLOAD[$path] = $default;
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
    private function applyOverrides(array $ovr, string $prefix = ''): void
    {
        foreach ($ovr as $k => $v) {
            $path = $prefix === '' ? $k : $prefix . '.' . $k;

            if (is_array($v) && !isset(self::$FLAT_SCHEMA[$path])) {
                $this->applyOverrides($v, $path);
                continue;
            }
            $this->config()->set($path, $v);
        }
    }



    // Overwriting core Parsedown functions
    // -------------------------------------------------------------------------

    /**
     * Handle an element based on the legacy mode.
     *
     * This function extends the core Parsedown behavior to handle specific cases
     * when in legacy mode, particularly for empty element names.
     *
     * @since 0.1.0
     *
     * @param array $Element The element to be processed.
     * @return string|array Processed element or markup.
     */
    protected function element(array $Element)
    {
        if ($this->legacyMode) {
            // If the element's name is empty, return the text attribute
            if (empty($Element['name'])) {
                return $Element['text'] ?? '';
            }
        }

        // Use the original element method from the parent
        return parent::element($Element);
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
        $markup = '';
        $inlineMarkerList = $this->inlineMarkerList;
        $InlineTypes = $this->InlineTypes;
        $nonNestablesSet = $nonNestables ? array_flip($nonNestables) : [];

        while (true) {
            $ExcerptStr = strpbrk((string)$text, $inlineMarkerList);
            if ($ExcerptStr === false) {
                // No more markers, process the rest and break
                $markup .= $this->unmarkedText($text);
                break;
            }

            $marker = $ExcerptStr[0];
            $markerPosition = strpos($text, $marker);

            // Prepare excerpt context
            $before = $markerPosition > 0 ? $text[$markerPosition - 1] : '';
            $Excerpt = [
                'text' => $ExcerptStr,
                'context' => $text,
                'before' => $before,
                'parent' => $this,
            ];

            // Try each inline type for this marker
            foreach ($InlineTypes[$marker] as $inlineType) {
                if (isset($nonNestablesSet[$inlineType])) {
                    continue;
                }

                $handler = 'inline' . $inlineType;
                $Inline = $this->$handler($Excerpt);

                if (!isset($Inline)) {
                    continue;
                }

                if (isset($Inline['position']) && $Inline['position'] > $markerPosition) {
                    continue;
                }

                $Inline['position'] = $Inline['position'] ?? $markerPosition;

                // Only add nonNestables if present
                if ($nonNestables) {
                    foreach ($nonNestables as $non_nestable) {
                        $Inline['element']['nonNestables'][] = $non_nestable;
                    }
                }

                // Add text before the inline element
                if ($Inline['position'] > 0) {
                    $markup .= $this->unmarkedText(substr($text, 0, $Inline['position']));
                }

                // Add the inline element
                $markup .= $Inline['markup'] ?? $this->element($Inline['element']);

                // Remove processed text
                $text = substr($text, $Inline['position'] + $Inline['extent']);

                // Continue with the rest of the text
                continue 2;
            }

            // No inline found, treat marker as plain text
            $markup .= $this->unmarkedText(substr($text, 0, $markerPosition + 1));
            $text = substr($text, $markerPosition + 1);
        }

        return $markup;
    }


    /**
     * Parses a line of text into inline elements.
     *
     * This function processes the given text, identifying markers and breaking it into inline elements.
     * Inline elements include things like bold, italic, links, etc. It recursively handles nesting and respects
     * non-nestable contexts.
     *
     * lineElements() is 1.8 version of line() from 1.7
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
        $Elements = [];
        $inlineMarkerList = $this->inlineMarkerList;
        $InlineTypes = $this->InlineTypes;
        $nonNestablesSet = $nonNestables ? array_flip($nonNestables) : [];

        $textLen = strlen($text);
        $offset = 0;

        while ($offset < $textLen) {
            $ExcerptStr = strpbrk(substr($text, $offset), $inlineMarkerList);
            if ($ExcerptStr === false) {
                // No more markers, process the rest and break
                if ($offset < $textLen) {
                    $InlineText = $this->inlineText(substr($text, $offset));
                    $Elements[] = $InlineText['element'];
                }
                break;
            }

            $marker = $ExcerptStr[0];
            $markerPosition = strpos($text, $marker, $offset);

            $before = $markerPosition > 0 ? $text[$markerPosition - 1] : '';
            $Excerpt = [
                'text' => substr($text, $markerPosition),
                'context' => $text,
                'before' => $before,
            ];

            foreach ($InlineTypes[$marker] as $inlineType) {
                if (isset($nonNestablesSet[$inlineType])) {
                    continue;
                }

                $Inline = $this->{"inline$inlineType"}($Excerpt);

                if (!isset($Inline)) {
                    continue;
                }

                if (isset($Inline['position']) && $Inline['position'] > ($markerPosition - $offset)) {
                    continue;
                }

                $Inline['position'] = $Inline['position'] ?? 0;

                // Only add nonNestables if present
                if ($nonNestablesSet) {
                    $Inline['element']['nonNestables'] = isset($Inline['element']['nonNestables'])
                        ? array_merge($Inline['element']['nonNestables'], array_keys($nonNestablesSet))
                        : array_keys($nonNestablesSet);
                }

                // Add unmarked text before the inline element
                if ($Inline['position'] > 0) {
                    $unmarkedText = substr($text, $offset, $Inline['position']);
                    if ($unmarkedText !== '') {
                        $InlineText = $this->inlineText($unmarkedText);
                        $Elements[] = $InlineText['element'];
                    }
                }

                // Add the inline element
                $Elements[] = $this->extractElement($Inline);

                // Move offset past the processed inline element
                $offset = $markerPosition + $Inline['position'] + $Inline['extent'];
                continue 2;
            }

            // No inline found, treat marker as plain text
            $plainText = substr($text, $offset, $markerPosition - $offset + 1);
            if ($plainText !== '') {
                $InlineText = $this->inlineText($plainText);
                $Elements[] = $InlineText['element'];
            }
            $offset = $markerPosition + 1;
        }

        // Set the `autobreak` property for each element, defaulting to false if not already set
        foreach ($Elements as &$Element) {
            if (!isset($Element['autobreak'])) {
                $Element['autobreak'] = false;
            }
        }

        return $Elements;
    }
}
