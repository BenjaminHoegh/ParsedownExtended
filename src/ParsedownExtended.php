<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended;

use Erusev\Parsedown\Parsedown;
use Erusev\ParsedownExtra\ParsedownExtra;

/**
 * Class ParsedownExtended
 *
 * Extended version of Parsedown for customized Markdown parsing.
 * Provides extended parsing capabilities, version checking, and custom configuration options.
 * 
 * This version is designed exclusively for Parsedown 2.x using composition pattern.
 */
class ParsedownExtended
{
    public const VERSION = '2.0.0';
    public const VERSION_PARSEDOWN_REQUIRED = '2.0.0';
    public const VERSION_PARSEDOWN_EXTRA_REQUIRED = '2.0.0';
    public const MIN_PHP_VERSION = '7.4';

    /** @var Parsedown The underlying Parsedown instance */
    private Parsedown $parsedown;

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

    public function __construct()
    {
        $this->initializeParsedownExtended();
        
        // Create StateBearer with ParsedownExtra
        $stateBearer = new ParsedownExtra();
        
        $this->parsedown = new Parsedown($stateBearer);
    }

    /**
     * Initialize ParsedownExtended functionality.
     */
    protected function initializeParsedownExtended(): void
    {
        // Check if the current PHP version meets the minimum requirement
        $this->checkVersion('PHP', PHP_VERSION, self::MIN_PHP_VERSION);

        // Check Parsedown version
        $parsedownVersion = $this->getParsedownVersion();
        $this->checkVersion('Parsedown', $parsedownVersion, self::VERSION_PARSEDOWN_REQUIRED);

        // Check ParsedownExtra version
        $parsedownExtraVersion = $this->getParsedownExtraVersion();
        if ($parsedownExtraVersion) {
            $this->checkVersion('ParsedownExtra', $parsedownExtraVersion, self::VERSION_PARSEDOWN_EXTRA_REQUIRED);
        }

        // Initialize settings with the provided schema
        $this->configSchema = $this->defineConfigSchema();
        $this->config = $this->initializeConfig($this->configSchema);
    }

    /**
     * Main text parsing method for ParsedownExtended
     * 
     * @param string $text The Markdown text to parse
     * @return string The parsed HTML
     */
    public function text(string $text): string
    {
        return $this->processText($text);
    }

    /**
     * Process text through ParsedownExtended functionality including TOC
     * 
     * @param string $text The Markdown text to parse
     * @return string The processed HTML
     */
    protected function processText(string $text): string
    {
        // Reset parsing state
        $this->anchorRegister = [];
        $this->contentsListArray = [];
        $this->firstHeadLevel = 0;
        $this->contentsListString = '';

        // Process TOC if enabled
        if ($this->config()->get('toc')) {
            $text = $this->processTOC($text);
        }

        // Use the underlying Parsedown 2.x to convert to HTML
        $html = $this->parsedown->toHtml($text);

        return $html;
    }

    /**
     * Process Table of Contents functionality
     * 
     * @param string $text The Markdown text
     * @return string The text with TOC processed
     */
    protected function processTOC(string $text): string
    {
        $tocTag = $this->config()->get('toc.tag', true);
        $tocId = $this->config()->get('toc.id', true);
        $tocLevels = $this->config()->get('toc.levels', true);

        // If TOC tag is not found, return original text
        if (strpos($text, $tocTag) === false) {
            return $text;
        }

        // First pass: collect headings and generate anchors
        $lines = explode("\n", $text);
        $processedLines = [];
        
        foreach ($lines as $line) {
            if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $matches)) {
                $level = strlen($matches[1]);
                $headingText = trim($matches[2]);
                
                // Check if this heading level should be included in TOC
                $headingTag = 'h' . $level;
                if (in_array($headingTag, $tocLevels)) {
                    $anchor = $this->createAnchorID($headingText);
                    $this->contentsListArray[] = [
                        'level' => $level,
                        'text' => $headingText,
                        'anchor' => $anchor
                    ];
                    
                    if ($this->firstHeadLevel === 0) {
                        $this->firstHeadLevel = $level;
                    }
                    
                    // Add anchor to heading
                    $line = $matches[1] . ' ' . $headingText . ' {#' . $anchor . '}';
                }
            }
            $processedLines[] = $line;
        }

        // Generate TOC HTML
        if (!empty($this->contentsListArray)) {
            $this->generateTOC($tocId);
            
            // Replace TOC tag with generated TOC
            $text = str_replace($tocTag, $this->contentsListString, implode("\n", $processedLines));
        } else {
            $text = implode("\n", $processedLines);
        }

        return $text;
    }

    /**
     * Generate Table of Contents HTML
     * 
     * @param string $tocId The ID for the TOC container
     */
    protected function generateTOC(string $tocId): void
    {
        if (empty($this->contentsListArray)) {
            return;
        }

        $toc = '<div id="' . $tocId . '">' . "\n";
        $toc .= '<ul>' . "\n";
        
        $currentLevel = $this->firstHeadLevel;
        
        foreach ($this->contentsListArray as $item) {
            $level = $item['level'];
            $text = htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8');
            $anchor = $item['anchor'];
            
            if ($level > $currentLevel) {
                // Open nested lists
                for ($i = $currentLevel; $i < $level; $i++) {
                    $toc .= '<li><ul>' . "\n";
                }
            } elseif ($level < $currentLevel) {
                // Close nested lists
                for ($i = $currentLevel; $i > $level; $i--) {
                    $toc .= '</ul></li>' . "\n";
                }
            }
            
            $toc .= '<li><a href="#' . $anchor . '">' . $text . '</a></li>' . "\n";
            $currentLevel = $level;
        }
        
        // Close remaining open lists
        for ($i = $currentLevel; $i > $this->firstHeadLevel; $i--) {
            $toc .= '</ul></li>' . "\n";
        }
        
        $toc .= '</ul>' . "\n";
        $toc .= '</div>' . "\n";
        
        $this->contentsListString = $toc;
    }

    /**
     * Create an anchor ID from heading text
     * 
     * @param string $text The heading text
     * @return string The generated anchor ID
     */
    protected function createAnchorID(string $text): string
    {
        if ($this->createAnchorIDCallback && is_callable($this->createAnchorIDCallback)) {
            return call_user_func($this->createAnchorIDCallback, $text);
        }

        $config = $this->config();
        $delimiter = $config->get('headings.auto_anchors.delimiter', true);
        $lowercase = $config->get('headings.auto_anchors.lowercase', true);
        $replacements = $config->get('headings.auto_anchors.replacements', true);
        $transliterate = $config->get('headings.auto_anchors.transliterate', true);
        $blacklist = $config->get('headings.auto_anchors.blacklist', true);

        // Remove HTML tags
        $anchor = strip_tags($text);
        
        // Apply custom replacements
        if (!empty($replacements)) {
            foreach ($replacements as $search => $replace) {
                $anchor = str_replace($search, $replace, $anchor);
            }
        }
        
        // Transliterate if enabled
        if ($transliterate && function_exists('transliterator_transliterate')) {
            $anchor = transliterator_transliterate('Any-Latin; Latin-ASCII', $anchor);
        }
        
        // Convert to lowercase if enabled
        if ($lowercase) {
            $anchor = strtolower($anchor);
        }
        
        // Replace spaces and special characters with delimiter
        $anchor = preg_replace('/[^\w\-_]/', $delimiter, $anchor);
        $anchor = preg_replace('/[' . preg_quote($delimiter) . ']+/', $delimiter, $anchor);
        $anchor = trim($anchor, $delimiter);
        
        // Check against blacklist
        if (in_array($anchor, $blacklist)) {
            $anchor .= $delimiter . '1';
        }
        
        // Ensure uniqueness
        $originalAnchor = $anchor;
        $counter = 1;
        while (in_array($anchor, $this->anchorRegister)) {
            $anchor = $originalAnchor . $delimiter . $counter;
            $counter++;
        }
        
        $this->anchorRegister[] = $anchor;
        return $anchor;
    }

    /**
     * Get the Parsedown version (2.x only)
     *
     * @return string
     */
    private function getParsedownVersion(): string
    {
        return Parsedown::version;
    }

    /**
     * Get the ParsedownExtra version (2.x only)
     *
     * @return string|null
     */
    private function getParsedownExtraVersion(): ?string
    {
        // ParsedownExtra 2.x doesn't have a version constant yet, assume 2.0.0
        return '2.0.0';
    }

    /**
     * Check version compatibility for a specific component.
     *
     * @param string $component The name of the component being checked
     * @param string $currentVersion The current version of the component installed
     * @param string $requiredVersion The minimum required version of the component
     * @throws \Exception If the current version is lower than the required version
     */
    private function checkVersion(string $component, string $currentVersion, string $requiredVersion): void
    {
        if (version_compare($currentVersion, $requiredVersion) < 0) {
            $msg_error  = 'Version Error.' . PHP_EOL;
            $msg_error .= "  ParsedownExtended requires a later version of $component." . PHP_EOL;
            $msg_error .= "  - Current version : $currentVersion" . PHP_EOL;
            $msg_error .= "  - Required version: $requiredVersion and later" . PHP_EOL;

            throw new \Exception($msg_error);
        }
    }

    /**
     * Magic method to delegate unknown methods to the underlying Parsedown instance
     */
    public function __call(string $name, array $arguments)
    {
        if (method_exists($this->parsedown, $name)) {
            return $this->parsedown->$name(...$arguments);
        }
        
        throw new \BadMethodCallException("Method '$name' not found");
    }

    // Configuration methods
    // -------------------------------------------------------------------------

    /**
     * Initialize configuration using a given schema.
     *
     * @param array $schema The configuration schema to use for initialization.
     * @return array The initialized configuration based on the given schema.
     */
    private function initializeConfig(array $schema): array
    {
        $config = [];
        foreach ($schema as $key => $definition) {
            if (isset($definition['type'])) {
                if ($definition['type'] === 'array' && is_array($definition['default'])) {
                    $config[$key] = $this->initializeConfig($definition['default']);
                } else {
                    $config[$key] = $definition['default'];
                }
            } else {
                if (is_array($definition)) {
                    $config[$key] = $this->initializeConfig($definition);
                } else {
                    $config[$key] = $definition;
                }
            }
        }
        return $config;
    }

    /**
     * Define the configuration schema.
     *
     * @return array The defined configuration schema.
     */
    private function defineConfigSchema(): array
    {
        return [
            'toc' => [
                'enabled' => ['type' => 'boolean', 'default' => true],
                'levels' => [
                    'type' => 'array',
                    'default' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
                    'item_schema' => ['type' => 'string'],
                ],
                'tag' => ['type' => 'string', 'default' => '[TOC]'],
                'id' => ['type' => 'string', 'default' => 'toc'],
            ],
            'headings' => [
                'enabled' => ['type' => 'boolean', 'default' => true],
                'allowed_levels' => ['type' => 'array', 'default' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']],
                'auto_anchors' => [
                    'enabled' => ['type' => 'boolean', 'default' => true],
                    'delimiter' => ['type' => 'string', 'default' => '-'],
                    'lowercase' => ['type' => 'boolean', 'default' => true],
                    'replacements' => ['type' => 'array', 'default' => []],
                    'transliterate' => ['type' => 'boolean', 'default' => false],
                    'blacklist' => ['type' => 'array', 'default' => []],
                ],
                'special_attributes' => ['type' => 'boolean', 'default' => true],
            ],
            // Add other configuration options as needed
        ];
    }

    /**
     * Retrieve the configuration schema.
     *
     * @return array The configuration schema as an associative array.
     */
    public function getConfigSchema(): array
    {
        return $this->configSchema;
    }

    /**
     * Return a new instance of an anonymous configuration class.
     *
     * @return object Anonymous configuration object with get and set methods.
     */
    public function config()
    {
        return new class ($this->configSchema, $this->config) {
            private array $schema;
            private $config;

            public function __construct(array $schema, &$config)
            {
                $this->schema = $schema;
                $this->config = &$config;
            }

            /**
             * Retrieves a value from a nested array using a dot-separated key path.
             *
             * @param string $keyPath Dot-separated key path indicating the config to get.
             * @param bool $raw Whether to return the raw value without any processing.
             * @return mixed The value of the configuration setting.
             * @throws \InvalidArgumentException If the key path is invalid.
             */
            public function get(string $keyPath, bool $raw = false)
            {
                $keys = explode('.', $keyPath);
                $value = $this->config;

                foreach ($keys as $key) {
                    if (!array_key_exists($key, $value)) {
                        throw new \InvalidArgumentException("Invalid key path '{$keyPath}' given.");
                    }
                    $value = $value[$key];
                }

                if ($raw) {
                    return $value;
                }

                return is_array($value) && isset($value['enabled']) ? $value['enabled'] : $value;
            }

            /**
             * Set the configuration value for the provided key path.
             *
             * @param string|array $keyPath Dot-separated key path or array of key paths and values.
             * @param mixed $value The value to set.
             * @return self Returns the instance for method chaining.
             */
            public function set($keyPath, $value = null): self
            {
                if (is_array($keyPath)) {
                    foreach ($keyPath as $key => $val) {
                        $this->set($key, $val);
                    }
                    return $this;
                }

                $keys = explode('.', $keyPath);
                $lastKey = array_pop($keys);

                $current = &$this->config;

                foreach ($keys as $key) {
                    if (!isset($current[$key])) {
                        throw new \InvalidArgumentException("Invalid key path '{$keyPath}' given.");
                    }
                    $current = &$current[$key];
                }

                if (isset($current[$lastKey]['enabled']) && is_array($current[$lastKey])) {
                    if (is_array($value)) {
                        foreach ($value as $subKey => $subValue) {
                            $this->set($keyPath . '.' . $subKey, $subValue);
                        }
                    } else {
                        $current[$lastKey]['enabled'] = $value;
                    }
                } else {
                    $current[$lastKey] = $value;
                }

                return $this;
            }
        };
    }
}