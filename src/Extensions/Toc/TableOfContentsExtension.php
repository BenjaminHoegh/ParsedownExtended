<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Toc;

trait TableOfContentsExtension
{
    /** @var array $contentsListArray List of contents generated during parsing */
    private array $contentsListArray = [];

    /** @var int $firstHeadLevel The level of the first header parsed */
    private int $firstHeadLevel = 0;

    /** @var string $contentsListString String representation of the table of contents */
    private string $contentsListString = '';

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
        $this->initializePredefinedAbbreviations();

        $text = $this->encodeTag($text); // Escapes ToC tag temporarily
        $html = parent::text($text);     // Parses the markdown text
        return $this->decodeTag($html);  // Unescapes the ToC tag
    }

    /**
     * Parses markdown input into block elements while preloading predefined abbreviations.
     *
     * Parsedown clears definition data at the start of each parse, so predefined abbreviations
     * must be re-applied immediately after that reset and before block parsing begins.
     *
     * @param string $text Markdown source.
     * @return array Parsed element tree.
     */
    protected function textElements($text)
    {
        // Ensure definitions are reset per parse, then re-apply predefined abbreviations.
        $this->DefinitionData = [];
        $this->predefinedAbbreviationsAdded = false;
        $this->initializePredefinedAbbreviations();

        // Standardize and normalize input before delegating to block parsing.
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = trim($text, "\n");
        $lines = explode("\n", $text);

        return $this->linesElements($lines);
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
                $json = json_encode($this->contentsListArray);
                return is_string($json) ? $json : '[]';
            default:
                $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
                $caller = $backtrace[1] ?? $backtrace[0];
                $errorMessage = "Unknown return type '{$type_return}' given while parsing ToC. Called in " . ($caller['file'] ?? 'unknown') . " on line " . ($caller['line'] ?? 'unknown');
                throw new \InvalidArgumentException($errorMessage);
        }
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
}
