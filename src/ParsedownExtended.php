<?php

namespace BenjaminHoegh\ParsedownExtended;

/**
 * This code checks if the class 'ParsedownExtra' exists. If it does, it creates an alias for it called 'ParsedownExtendedParentAlias'.
 * If the class 'ParsedownExtra' does not exist, it creates an alias for the class 'Parsedown' called 'ParsedownExtendedParentAlias'.
 */
if (class_exists('ParsedownExtra')) {
    class_alias('ParsedownExtra', 'ParsedownExtendedParentAlias');
} else {
    class_alias('Parsedown', 'ParsedownExtendedParentAlias');
}


class ParsedownExtended extends \ParsedownExtendedParentAlias
{
    public const VERSION = '1.3.0';
    public const VERSION_PARSEDOWN_REQUIRED = '1.7.4';
    public const VERSION_PARSEDOWN_EXTRA_REQUIRED = '0.8.1';
    public const MIN_PHP_VERSION = '7.4';

    private const TOC_TAG_DEFAULT = '[toc]';
    private const TOC_ID_ATTRIBUTE_DEFAULT = 'toc';
    private array $anchorRegister = [];
    private array $contentsListArray = [];
    private int $firstHeadLevel = 0;
    private string $contentsListString = '';
    private string $id_toc = '';
    private string $tag_toc = '';
    private $createAnchorIDCallback = null;
    private bool $legacyMode = false;
    private mixed $config;
    private array $configSchema;


    public function __construct()
    {
        $this->checkVersion('PHP', PHP_VERSION, self::MIN_PHP_VERSION);
        $this->checkVersion('Parsedown', \Parsedown::version, self::VERSION_PARSEDOWN_REQUIRED);

        if (class_exists('ParsedownExtra')) {
            $this->checkVersion('ParsedownExtra', \ParsedownExtra::version, self::VERSION_PARSEDOWN_EXTRA_REQUIRED);
            parent::__construct();
        }

        $this->setLegacyMode();

        // Initialize settings with the provided schema
        $this->configSchema = $this->defineConfigSchema();
        $this->config = $this->initializeConfig($this->configSchema);

        // Add inline types
        $this->addInlineType('=', 'Marking');
        $this->addInlineType('+', 'Insertions');
        $this->addInlineType('[', 'Keystrokes');
        $this->addInlineType(['\\', '$'], 'MathNotation');
        $this->addInlineType('^', 'Superscript');
        $this->addInlineType('~', 'Subscript');
        $this->addInlineType(':', 'Emojis');
        $this->addInlineType(['<', '>', '-', '.', "'", '"', '`'], 'Smartypants');
        $this->addInlineType(['(','.','+','!','?'], 'Typographer');

        // Add block types
        $this->addBlockType(['\\','$'], 'MathNotation');

        /**
         * Move 'SpecialCharacter' to the end of the list if it exists.
         * to the end of the list if it exists. This ensures that 'SpecialCharacter' is always processed last
         * when parsing the markdown content. This is necessary to prevent the parser from interfering with
         * other types.
         */
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

    public function getConfigSchema(): array
    {
        return $this->configSchema;
    }

    private function checkVersion($component, $currentVersion, $requiredVersion)
    {
        if (version_compare($currentVersion, $requiredVersion) < 0) {
            $msg_error  = 'Version Error.' . PHP_EOL;
            $msg_error .= "  ParsedownExtended requires a later version of $component." . PHP_EOL;
            $msg_error .= "  - Current version : $currentVersion" . PHP_EOL;
            $msg_error .= "  - Required version: $requiredVersion and later" . PHP_EOL;
            throw new \Exception($msg_error);
        }
    }

    private function setLegacyMode()
    {
        $parsedownVersion = preg_replace('/-.*$/', '', \Parsedown::version);

        if (version_compare($parsedownVersion, '1.8.0') < 0 && version_compare($parsedownVersion, '1.7.4') >= 0) {
            $this->legacyMode = true;
        }
    }

    // Inline types
    // -------------------------------------------------------------------------

    protected function inlineCode($Excerpt)
    {
        if ($this->config()->get('code') && $this->config()->get('code.inline')) {
            return parent::inlineCode($Excerpt);
        }
    }


    protected function inlineEmailTag($Excerpt)
    {
        if ($this->config()->get('links') && $this->config()->get('links.email_links')) {
            return parent::inlineEmailTag($Excerpt);
        }
    }

    protected function inlineImage($Excerpt)
    {
        if ($this->config()->get('images')) {
            return parent::inlineImage($Excerpt);
        }
    }

    protected function inlineLink($Excerpt)
    {
        if ($this->config()->get('links')) {
            return parent::inlineLink($Excerpt);
        }
    }

    protected function inlineMarkup($Excerpt)
    {
        if ($this->config()->get('markup')) {
            return parent::inlineMarkup($Excerpt);
        }
    }

    protected function inlineStrikethrough($Excerpt)
    {
        if ($this->config()->get('emphasis.strikethroughs') && $this->config()->get('emphasis')) {
            return parent::inlineStrikethrough($Excerpt);
        }
    }

    protected function inlineUrl($Excerpt)
    {
        if ($this->config()->get('links')) {
            return parent::inlineUrl($Excerpt);
        }
    }

    protected function inlineUrlTag($Excerpt)
    {
        if ($this->config()->get('links')) {
            return parent::inlineUrlTag($Excerpt);
        }
    }

    /**
     * Parses inline emphasis in the text.
     *
     * @param array $Excerpt The excerpt containing the text to be parsed.
     * @return array|null The parsed emphasis element or null if no emphasis is found.
     */
    protected function inlineEmphasis($Excerpt)
    {
        if (!$this->config()->get('emphasis') || !isset($Excerpt['text'][1])) {
            return;
        }

        $marker = $Excerpt['text'][0];

        // Check if the emphasis bold is enabled
        if ($this->config()->get('emphasis.bold') and preg_match($this->StrongRegex[$marker], $Excerpt['text'], $matches)) {
            $emphasis = 'strong';
        } elseif ($this->config()->get('emphasis.italic') and preg_match($this->EmRegex[$marker], $Excerpt['text'], $matches)) {
            $emphasis = 'em';
        } else {
            return;
        }

        return [
            'extent' => strlen($matches[0]),
            'element' => [
                'name' => $emphasis,
                'handler' => 'line',
                'text' => $matches[1],
            ],
        ];
    }


    /**
     * Marks inline text with the 'mark' HTML element if emphasis marking is enabled.
     *
     * @param array $Excerpt The excerpt array containing the text to be marked.
     * @return array|null The marked text as an array or null if marking is not enabled.
     */
    protected function inlineMarking(array $Excerpt): ?array
    {
        if (!$this->config()->get('emphasis.marking') || !$this->config()->get('emphasis')) {
            return null;
        }

        if (preg_match('/^==((?:\\\\\=|[^=]|=[^=]*=)+?)==(?!=)/s', $Excerpt['text'], $matches)) {
            return [
                'extent' => strlen($matches[0]),
                'element' => [
                    'name' => 'mark',
                    'text' => $matches[1],
                ],
            ];
        }

        return null;
    }


    /**
     * Checks for inline insertions in the given excerpt.
     *
     * @param array $Excerpt The excerpt to check.
     * @return array|null Returns an array with the extent and element of the insertion if found, otherwise null.
     */
    protected function inlineInsertions(array $Excerpt): ?array
    {
        if (!$this->config()->get('emphasis.insertions') || !$this->config()->get('emphasis')) {
            return null;
        }

        if (preg_match('/^\+\+((?:\\\\\+|[^\+]|\+[^\+]*\+)+?)\+\+(?!\+)/s', $Excerpt['text'], $matches)) {
            return [
                'extent' => strlen($matches[0]),
                'element' => [
                    'name' => 'ins',
                    'text' => $matches[1],
                ],
            ];
        }

        return null;
    }

    /**
     * Parses inline keystrokes in the text and returns an array containing the extent and element information.
     *
     * @param array $Excerpt The excerpt array containing the text to be parsed.
     * @return array|null Returns an array with 'extent' and 'element' information if inline keystrokes are found, otherwise returns null.
     */
    protected function inlineKeystrokes(array $Excerpt): ?array
    {
        if (!$this->config()->get('emphasis.keystrokes') || !$this->config()->get('emphasis')) {
            return null;
        }

        if (preg_match('/^(?<!\[)(?:\[\[([^\[\]]*|[\[\]])\]\])(?!\])/s', $Excerpt['text'], $matches)) {
            return [
                'extent' => strlen($matches[0]),
                'element' => [
                    'name' => 'kbd',
                    'text' => $matches[1],
                ],
            ];
        }

        return null;
    }



    /**
     * Parses inline superscript elements in the text.
     *
     * @param array $Excerpt The excerpt containing the text to parse.
     * @return array|null Returns an array with the extent and element of the superscript, or null if not found.
     */
    protected function inlineSuperscript(array $Excerpt): ?array
    {
        if (!$this->config()->get('emphasis.superscript') || !$this->config()->get('emphasis')) {
            return null;
        }

        if (preg_match('/^[\^]((?:\\\\\\^|[^\^]|[\^][^\^]+?[\^][\^])+?)[\^](?![\^])/s', $Excerpt['text'], $matches)) {
            return [
                'extent' => strlen($matches[0]),
                'element' => [
                    'name' => 'sup',
                    'text' => $matches[1],
                    'function' => 'lineElements',
                ],
            ];
        }

        return null;
    }



    /**
     * Parses inline subscript elements in the text.
     *
     * @param array $Excerpt The excerpt containing the text to parse.
     * @return array|null Returns an array with the extent and element if a subscript element is found, otherwise null.
     */
    protected function inlineSubscript(array $Excerpt): ?array
    {
        if (!$this->config()->get('emphasis.subscript') || !$this->config()->get('emphasis')) {
            return null;
        }

        if (preg_match('/^~((?:\\\\~|[^~]|~~[^~]*~~)+?)~(?!~)/s', $Excerpt['text'], $matches)) {
            return [
                'extent' => strlen($matches[0]),
                'element' => [
                    'name' => 'sub',
                    'text' => $matches[1],
                    'function' => 'lineElements',
                ],
            ];
        }

        return null;
    }



    /**
     * Parses inline math notation in the given excerpt.
     *
     * @param array $Excerpt The excerpt to parse.
     * @return array|null The parsed math notation or null if parsing is disabled.
     */
    protected function inlineMathNotation($Excerpt)
    {
        // Check if parsing of math notation is enabled
        if (!$this->config()->get('math') || !$this->config()->get('math.inline')) {
            return null;
        }

        // Check if the excerpt has enough characters
        if (!isset($Excerpt['text'][1])) {
            return;
        }

        // Check if there is whitespace before the excerpt
        if ($Excerpt['before'] !== '' && preg_match('/\s/', $Excerpt['before']) === 0) {
            return;
        }

        // Iterate through the inline math delimiters
        foreach ($this->config()->get('math.inline.delimiters') as $config) {
            $leftMarker = preg_quote($config['left'], '/');
            $rightMarker = preg_quote($config['right'], '/');

            // Construct the regular expression pattern
            if ($config['left'][0] === '\\' || strlen($config['left']) > 1) {
                $regex = '/^(?<!\S)' . $leftMarker . '(?![\r\n])((?:\\\\' . $rightMarker . '|\\\\' . $leftMarker . '|[^\r\n])+?)' . $rightMarker . '(?![^\s,.])/s';
            } else {
                $regex = '/^(?<!\S)' . $leftMarker . '(?![\r\n])((?:\\\\' . $rightMarker . '|\\\\' . $leftMarker . '|[^' . $rightMarker . '\r\n])+?)' . $rightMarker . '(?![^\s,.])/s';
            }

            // Match the regular expression pattern against the excerpt
            if (preg_match($regex, $Excerpt['text'], $matches)) {
                return [
                    'extent' => strlen($matches[0]),
                    'element' => [
                        'text' => $matches[0],
                    ],
                ];
            }
        }

        return;
    }



    /**
     * Escapes inline escape sequences in the given Excerpt.
     *
     * This method checks if the 'math' feature is enabled and if so, it iterates through the configured inline delimiters for math expressions.
     * It constructs a regular expression pattern based on the left and right markers of each delimiter and checks if the pattern matches the Excerpt's text.
     * If a match is found, the method returns early.
     *
     * If the 'math' feature is not enabled or no match is found, the method checks if the second character of the Excerpt's text is a special character.
     * If it is, the method returns an array with the special character as the 'markup' value and an extent of 2.
     *
     * @param array $Excerpt The Excerpt containing the text to be processed.
     * @return array|null Returns an array with the 'markup' and 'extent' values if a special character is found, otherwise returns null.
     */
    protected function inlineEscapeSequence($Excerpt)
    {
        if ($this->config()->get('math')) {
            foreach ($this->config()->get('math.inline.delimiters') as $config) {

                $leftMarker = preg_quote($config['left'], '/');
                $rightMarker = preg_quote($config['right'], '/');

                if ($config['left'][0] === '\\' || strlen($config['left']) > 1) {
                    $regex = '/^(?<!\S)' . $leftMarker . '(?![\r\n])((?:\\\\' . $rightMarker . '|\\\\' . $leftMarker . '|[^\r\n])+?)' . $rightMarker . '(?![^\s,.])/s';
                } else {
                    $regex = '/^(?<!\S)' . $leftMarker . '(?![\r\n])((?:\\\\' . $rightMarker . '|\\\\' . $leftMarker . '|[^' . $rightMarker . '\r\n])+?)' . $rightMarker . '(?![^\s,.])/s';
                }

                if (preg_match($regex, $Excerpt['text'])) {
                    return;
                }
            }
        }

        if (isset($Excerpt['text'][1]) && in_array($Excerpt['text'][1], $this->specialCharacters)) {
            return [
                'markup' => $Excerpt['text'][1],
                'extent' => 2,
            ];
        }
    }



    /**
     * Applies typographic substitutions to the inline text.
     *
     * This method checks if the typographer feature is enabled. If not, it returns null.
     * If enabled, it applies various typographic substitutions to the text, such as replacing
     * "(c)" with the copyright symbol, "(r)" with the registered trademark symbol, etc.
     * It also handles smart ellipses and replaces consecutive dots with ellipses.
     *
     * @param array $Excerpt The excerpt array containing the inline text.
     * @return array|null The modified excerpt array with typographic substitutions applied, or null if typographer is disabled.
     */
    protected function inlineTypographer(array $Excerpt): ?array
    {
        if (!$this->config()->get('typographer')) {
            return null;
        }

        // Check if smartypants and smart ellipses settings are enabled
        $ellipses = $this->config()->get('smarty') && $this->config()->get('smarty.smart_ellipses') ? html_entity_decode($this->config()->get('smarty.substitutions.ellipses')) : '...';

        $substitutions = [
            '/\(c\)/i' => html_entity_decode('&copy;'),
            '/\(r\)/i' => html_entity_decode('&reg;'),
            '/\(tm\)/i' => html_entity_decode('&trade;'),
            '/\(p\)/i' => html_entity_decode('&para;'),
            '/\+-/i' => html_entity_decode('&plusmn;'),
            '/\!\.{3,}/i' => '!..',
            '/\?\.{3,}/i' => '?..',
            '/\.{4,}/i' => $ellipses,
            '/(?<![\.!?])(\.{2})(?!\.)/i' => $ellipses,

        ];

        if (preg_match('/\+-|\(p\)|\(tm\)|\(r\)|\(c\)|\.{2,}|\!\.{3,}|\?\.{3,}/i', $Excerpt['text'], $matches)) {
            return [
                'extent' => strlen($matches[0]),
                'element' => [
                    'text' => preg_replace(array_keys($substitutions), array_values($substitutions), $matches[0]),
                ],
            ];
        }
        return null;
    }


    /**
     * Applies SmartyPants substitutions to the inline text.
     *
     * @param array $Excerpt The excerpt containing the inline text.
     * @return array|null The modified excerpt with SmartyPants substitutions applied, or null if SmartyPants is not enabled.
     */
    protected function inlineSmartypants($Excerpt)
    {
        if (!$this->config()->get('smarty')) {
            return null;
        }

        // Substitutions
        $backtickDoublequoteOpen = $this->config()->get('smarty.substitutions.left-double-quote');
        $backtickDoublequoteClose = $this->config()->get('smarty.substitutions.right-double-quote');
        $smartDoublequoteOpen = $this->config()->get('smarty.substitutions.left-double-quote');
        $smartDoublequoteClose = $this->config()->get('smarty.substitutions.right-double-quote');
        $smartSinglequoteOpen = $this->config()->get('smarty.substitutions.left-single-quote');
        $smartSinglequoteClose = $this->config()->get('smarty.substitutions.right-single-quote');
        $leftAngleQuote = $this->config()->get('smarty.substitutions.left-angle-quote');
        $rightAngleQuote = $this->config()->get('smarty.substitutions.right-angle-quote');

        if (!isset($Excerpt['before'])) {
            $Excerpt['before'] = '';
        }

        $patterns = [
            'smart_backticks' => [
                'pattern' => '/(``)(?!\s)([^"\'`]{1,})(\'\')/',
                'callback' => function ($matches) use ($backtickDoublequoteOpen, $backtickDoublequoteClose) {
                    return [
                        'extent' => strlen($matches[0]),
                        'element' => [
                            'text' => html_entity_decode($backtickDoublequoteOpen) . $matches[2] . html_entity_decode($backtickDoublequoteClose),
                        ],
                    ];
                },
            ],
            'smart_quotes' => [
                'pattern' => '/(")(?!\s)([^"]+)(")|(?<!\w)(\')(?!\s)([^\']+)(\')/',
                'callback' => function ($matches) use ($smartSinglequoteOpen, $smartSinglequoteClose, $smartDoublequoteOpen, $smartDoublequoteClose, $Excerpt) {
                    if (strlen(trim($Excerpt['before'])) > 0) {
                        return null;
                    }

                    if ("'" === $matches[4]) {
                        return [
                            'extent' => strlen($matches[0]),
                            'element' => [
                                'text' => html_entity_decode($smartSinglequoteOpen) . $matches[5] . html_entity_decode($smartSinglequoteClose),
                            ],
                        ];
                    }

                    if ('"' === $matches[1]) {
                        return [
                            'extent' => strlen($matches[0]),
                            'element' => [
                                'text' => html_entity_decode($smartDoublequoteOpen) . $matches[2] . html_entity_decode($smartDoublequoteClose),
                            ],
                        ];
                    }

                    return null;
                },
        ],
            'smart_angled_quotes' => [
                'pattern' => '/(<<)(?!\s)([^<>]{1,})(>>)/',
                'callback' => function ($matches) use ($leftAngleQuote, $rightAngleQuote, $Excerpt) {
                    if (strlen(trim($Excerpt['before'])) > 0) {
                        return null;
                    }

                    return [
                        'extent' => strlen($matches[0]),
                        'element' => [
                            'text' => html_entity_decode($leftAngleQuote) . $matches[2] . html_entity_decode($rightAngleQuote),
                        ],
                    ];
                },
        ],
            'smart_dashes' => [
                'pattern' => '/(---|--)/',
                'callback' => function ($matches) {
                    if ('---' === $matches[0]) {
                        return [
                            'extent' => strlen($matches[0]),
                            'element' => [
                                'text' => html_entity_decode($this->config()->get('smarty.substitutions.mdash')),
                            ],
                        ];
                    }

                    if ('--' === $matches[0]) {
                        return [
                            'extent' => strlen($matches[0]),
                            'element' => [
                                'text' => html_entity_decode($this->config()->get('smarty.substitutions.ndash')),
                            ],
                        ];
                    }

                    return null;
                },
        ],
            'smart_ellipses' => [
                'pattern' => '/(\.\.\.)/',
                'callback' => function ($matches) {
                    return [
                        'extent' => strlen($matches[0]),
                        'element' => [
                            'text' => html_entity_decode($this->config()->get('smarty.substitutions.ellipses')),
                        ],
                    ];
                },
        ],
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern['pattern'], $Excerpt['text'], $matches)) {
                return $pattern['callback']($matches);
            }
        }

        return null;
    }



    /**
     * Replaces emoji codes with corresponding emoji characters.
     *
     * @param array $Excerpt The excerpt containing the emoji codes.
     * @return array|null The excerpt with emoji codes replaced or null if emojis are disabled.
     */
    protected function inlineEmojis(array $Excerpt): ?array
    {
        if (!$this->config()->get('emojis')) {
            return null;
        }

        $emojiMap = [
            ':smile:' => '😄', ':laughing:' => '😆', ':blush:' => '😊', ':smiley:' => '😃',
            ':relaxed:' => '☺️', ':smirk:' => '😏', ':heart_eyes:' => '😍', ':kissing_heart:' => '😘',
            ':kissing_closed_eyes:' => '😚', ':flushed:' => '😳', ':relieved:' => '😌', ':satisfied:' => '😆',
            ':grin:' => '😁', ':wink:' => '😉', ':stuck_out_tongue_winking_eye:' => '😜', ':stuck_out_tongue_closed_eyes:' => '😝',
            ':grinning:' => '😀', ':kissing:' => '😗', ':kissing_smiling_eyes:' => '😙', ':stuck_out_tongue:' => '😛',
            ':sleeping:' => '😴', ':worried:' => '😟', ':frowning:' => '😦', ':anguished:' => '😧',
            ':open_mouth:' => '😮', ':grimacing:' => '😬', ':confused:' => '😕', ':hushed:' => '😯',
            ':expressionless:' => '😑', ':unamused:' => '😒', ':sweat_smile:' => '😅', ':sweat:' => '😓',
            ':disappointed_relieved:' => '😥', ':weary:' => '😩', ':pensive:' => '😔', ':disappointed:' => '😞',
            ':confounded:' => '😖', ':fearful:' => '😨', ':cold_sweat:' => '😰', ':persevere:' => '😣',
            ':cry:' => '😢', ':sob:' => '😭', ':joy:' => '😂', ':astonished:' => '😲',
            ':scream:' => '😱', ':tired_face:' => '😫', ':angry:' => '😠', ':rage:' => '😡',
            ':triumph:' => '😤', ':sleepy:' => '😪', ':yum:' => '😋', ':mask:' => '😷',
            ':sunglasses:' => '😎', ':dizzy_face:' => '😵', ':imp:' => '👿', ':smiling_imp:' => '😈',
            ':neutral_face:' => '😐', ':no_mouth:' => '😶', ':innocent:' => '😇', ':alien:' => '👽',
            ':yellow_heart:' => '💛', ':blue_heart:' => '💙', ':purple_heart:' => '💜', ':heart:' => '❤️',
            ':green_heart:' => '💚', ':broken_heart:' => '💔', ':heartbeat:' => '💓', ':heartpulse:' => '💗',
            ':two_hearts:' => '💕', ':revolving_hearts:' => '💞', ':cupid:' => '💘', ':sparkling_heart:' => '💖',
            ':sparkles:' => '✨', ':star:' => '⭐️', ':star2:' => '🌟', ':dizzy:' => '💫',
            ':boom:' => '💥', ':collision:' => '💥', ':anger:' => '💢', ':exclamation:' => '❗️',
            ':question:' => '❓', ':grey_exclamation:' => '❕', ':grey_question:' => '❔', ':zzz:' => '💤',
            ':dash:' => '💨', ':sweat_drops:' => '💦', ':notes:' => '🎶', ':musical_note:' => '🎵',
            ':fire:' => '🔥', ':hankey:' => '💩', ':poop:' => '💩', ':shit:' => '💩',
            ':+1:' => '👍', ':thumbsup:' => '👍', ':-1:' => '👎', ':thumbsdown:' => '👎',
            ':ok_hand:' => '👌', ':punch:' => '👊', ':facepunch:' => '👊', ':fist:' => '✊',
            ':v:' => '✌️', ':wave:' => '👋', ':hand:' => '✋', ':raised_hand:' => '✋',
            ':open_hands:' => '👐', ':point_up:' => '☝️', ':point_down:' => '👇', ':point_left:' => '👈',
            ':point_right:' => '👉', ':raised_hands:' => '🙌', ':pray:' => '🙏', ':point_up_2:' => '👆',
            ':clap:' => '👏', ':muscle:' => '💪', ':metal:' => '🤘', ':fu:' => '🖕',
            ':walking:' => '🚶', ':runner:' => '🏃', ':running:' => '🏃', ':couple:' => '👫',
            ':family:' => '👪', ':two_men_holding_hands:' => '👬', ':two_women_holding_hands:' => '👭', ':dancer:' => '💃',
            ':dancers:' => '👯', ':ok_woman:' => '🙆', ':no_good:' => '🙅', ':information_desk_person:' => '💁',
            ':raising_hand:' => '🙋', ':bride_with_veil:' => '👰', ':person_with_pouting_face:' => '🙎', ':person_frowning:' => '🙍',
            ':bow:' => '🙇', ':couple_with_heart:' => '💑', ':massage:' => '💆', ':haircut:' => '💇',
            ':nail_care:' => '💅', ':boy:' => '👦', ':girl:' => '👧', ':woman:' => '👩',
            ':man:' => '👨', ':baby:' => '👶', ':older_woman:' => '👵', ':older_man:' => '👴',
            ':person_with_blond_hair:' => '👱', ':man_with_gua_pi_mao:' => '👲', ':man_with_turban:' => '👳', ':construction_worker:' => '👷',
            ':cop:' => '👮', ':angel:' => '👼', ':princess:' => '👸', ':smiley_cat:' => '😺',
            ':smile_cat:' => '😸', ':heart_eyes_cat:' => '😻', ':kissing_cat:' => '😽', ':smirk_cat:' => '😼',
            ':scream_cat:' => '🙀', ':crying_cat_face:' => '😿', ':joy_cat:' => '😹', ':pouting_cat:' => '😾',
            ':japanese_ogre:' => '👹', ':japanese_goblin:' => '👺', ':see_no_evil:' => '🙈', ':hear_no_evil:' => '🙉',
            ':speak_no_evil:' => '🙊', ':guardsman:' => '💂', ':skull:' => '💀', ':feet:' => '🐾',
            ':lips:' => '👄', ':kiss:' => '💋', ':droplet:' => '💧', ':ear:' => '👂',
            ':eyes:' => '👀', ':nose:' => '👃', ':tongue:' => '👅', ':love_letter:' => '💌',
            ':bust_in_silhouette:' => '👤', ':busts_in_silhouette:' => '👥', ':speech_balloon:' => '💬', ':thought_balloon:' => '💭',
            ':sunny:' => '☀️', ':umbrella:' => '☔️', ':cloud:' => '☁️', ':snowflake:' => '❄️',
            ':snowman:' => '⛄️', ':zap:' => '⚡️', ':cyclone:' => '🌀', ':foggy:' => '🌁',
            ':ocean:' => '🌊', ':cat:' => '🐱', ':dog:' => '🐶', ':mouse:' => '🐭',
            ':hamster:' => '🐹', ':rabbit:' => '🐰', ':wolf:' => '🐺', ':frog:' => '🐸',
            ':tiger:' => '🐯', ':koala:' => '🐨', ':bear:' => '🐻', ':pig:' => '🐷',
            ':pig_nose:' => '🐽', ':cow:' => '🐮', ':boar:' => '🐗', ':monkey_face:' => '🐵',
            ':monkey:' => '🐒', ':horse:' => '🐴', ':racehorse:' => '🐎', ':camel:' => '🐫',
            ':sheep:' => '🐑', ':elephant:' => '🐘', ':panda_face:' => '🐼', ':snake:' => '🐍',
            ':bird:' => '🐦', ':baby_chick:' => '🐤', ':hatched_chick:' => '🐥', ':hatching_chick:' => '🐣',
            ':chicken:' => '🐔', ':penguin:' => '🐧', ':turtle:' => '🐢', ':bug:' => '🐛',
            ':honeybee:' => '🐝', ':ant:' => '🐜', ':beetle:' => '🐞', ':snail:' => '🐌',
            ':octopus:' => '🐙', ':tropical_fish:' => '🐠', ':fish:' => '🐟', ':whale:' => '🐳',
            ':whale2:' => '🐋', ':dolphin:' => '🐬', ':cow2:' => '🐄', ':ram:' => '🐏',
            ':rat:' => '🐀', ':water_buffalo:' => '🐃', ':tiger2:' => '🐅', ':rabbit2:' => '🐇',
            ':dragon:' => '🐉', ':goat:' => '🐐', ':rooster:' => '🐓', ':dog2:' => '🐕',
            ':pig2:' => '🐖', ':mouse2:' => '🐁', ':ox:' => '🐂', ':dragon_face:' => '🐲',
            ':blowfish:' => '🐡', ':crocodile:' => '🐊', ':dromedary_camel:' => '🐪', ':leopard:' => '🐆',
            ':cat2:' => '🐈', ':poodle:' => '🐩', ':crab' => '🦀', ':paw_prints:' => '🐾', ':bouquet:' => '💐',
            ':cherry_blossom:' => '🌸', ':tulip:' => '🌷', ':four_leaf_clover:' => '🍀', ':rose:' => '🌹',
            ':sunflower:' => '🌻', ':hibiscus:' => '🌺', ':maple_leaf:' => '🍁', ':leaves:' => '🍃',
            ':fallen_leaf:' => '🍂', ':herb:' => '🌿', ':mushroom:' => '🍄', ':cactus:' => '🌵',
            ':palm_tree:' => '🌴', ':evergreen_tree:' => '🌲', ':deciduous_tree:' => '🌳', ':chestnut:' => '🌰',
            ':seedling:' => '🌱', ':blossom:' => '🌼', ':ear_of_rice:' => '🌾', ':shell:' => '🐚',
            ':globe_with_meridians:' => '🌐', ':sun_with_face:' => '🌞', ':full_moon_with_face:' => '🌝', ':new_moon_with_face:' => '🌚',
            ':new_moon:' => '🌑', ':waxing_crescent_moon:' => '🌒', ':first_quarter_moon:' => '🌓', ':waxing_gibbous_moon:' => '🌔',
            ':full_moon:' => '🌕', ':waning_gibbous_moon:' => '🌖', ':last_quarter_moon:' => '🌗', ':waning_crescent_moon:' => '🌘',
            ':last_quarter_moon_with_face:' => '🌜', ':first_quarter_moon_with_face:' => '🌛', ':moon:' => '🌔', ':earth_africa:' => '🌍',
            ':earth_americas:' => '🌎', ':earth_asia:' => '🌏', ':volcano:' => '🌋', ':milky_way:' => '🌌',
            ':partly_sunny:' => '⛅️', ':bamboo:' => '🎍', ':gift_heart:' => '💝', ':dolls:' => '🎎',
            ':school_satchel:' => '🎒', ':mortar_board:' => '🎓', ':flags:' => '🎏', ':fireworks:' => '🎆',
            ':sparkler:' => '🎇', ':wind_chime:' => '🎐', ':rice_scene:' => '🎑', ':jack_o_lantern:' => '🎃',
            ':ghost:' => '👻', ':santa:' => '🎅', ':christmas_tree:' => '🎄', ':gift:' => '🎁',
            ':bell:' => '🔔', ':no_bell:' => '🔕', ':tanabata_tree:' => '🎋', ':tada:' => '🎉',
            ':confetti_ball:' => '🎊', ':balloon:' => '🎈', ':crystal_ball:' => '🔮', ':cd:' => '💿',
            ':dvd:' => '📀', ':floppy_disk:' => '💾', ':camera:' => '📷', ':video_camera:' => '📹',
            ':movie_camera:' => '🎥', ':computer:' => '💻', ':tv:' => '📺', ':iphone:' => '📱',
            ':phone:' => '☎️', ':telephone:' => '☎️', ':telephone_receiver:' => '📞', ':pager:' => '📟',
            ':fax:' => '📠', ':minidisc:' => '💽', ':vhs:' => '📼', ':sound:' => '🔉',
            ':speaker:' => '🔈', ':mute:' => '🔇', ':loudspeaker:' => '📢', ':mega:' => '📣',
            ':hourglass:' => '⌛️', ':hourglass_flowing_sand:' => '⏳', ':alarm_clock:' => '⏰', ':watch:' => '⌚️',
            ':radio:' => '📻', ':satellite:' => '📡', ':loop:' => '➿', ':mag:' => '🔍',
            ':mag_right:' => '🔎', ':unlock:' => '🔓', ':lock:' => '🔒', ':lock_with_ink_pen:' => '🔏',
            ':closed_lock_with_key:' => '🔐', ':key:' => '🔑', ':bulb:' => '💡', ':flashlight:' => '🔦',
            ':high_brightness:' => '🔆', ':low_brightness:' => '🔅', ':electric_plug:' => '🔌', ':battery:' => '🔋',
            ':calling:' => '📲', ':email:' => '✉️', ':mailbox:' => '📫', ':postbox:' => '📮',
            ':bath:' => '🛀', ':bathtub:' => '🛁', ':shower:' => '🚿', ':toilet:' => '🚽',
            ':wrench:' => '🔧', ':nut_and_bolt:' => '🔩', ':hammer:' => '🔨', ':seat:' => '💺',
            ':moneybag:' => '💰', ':yen:' => '💴', ':dollar:' => '💵', ':pound:' => '💷',
            ':euro:' => '💶', ':credit_card:' => '💳', ':money_with_wings:' => '💸', ':e-mail:' => '📧',
            ':inbox_tray:' => '📥', ':outbox_tray:' => '📤', ':envelope:' => '✉️', ':incoming_envelope:' => '📨',
            ':postal_horn:' => '📯', ':mailbox_closed:' => '📪', ':mailbox_with_mail:' => '📬', ':mailbox_with_no_mail:' => '📭',
            ':door:' => '🚪', ':smoking:' => '🚬', ':bomb:' => '💣', ':gun:' => '🔫',
            ':hocho:' => '🔪', ':pill:' => '💊', ':syringe:' => '💉', ':page_facing_up:' => '📄',
            ':page_with_curl:' => '📃', ':bookmark_tabs:' => '📑', ':bar_chart:' => '📊', ':chart_with_upwards_trend:' => '📈',
            ':chart_with_downwards_trend:' => '📉', ':scroll:' => '📜', ':clipboard:' => '📋', ':calendar:' => '📆',
            ':date:' => '📅', ':card_index:' => '📇', ':file_folder:' => '📁', ':open_file_folder:' => '📂',
            ':scissors:' => '✂️', ':pushpin:' => '📌', ':paperclip:' => '📎', ':black_nib:' => '✒️',
            ':pencil2:' => '✏️', ':straight_ruler:' => '📏', ':triangular_ruler:' => '📐', ':closed_book:' => '📕',
            ':green_book:' => '📗', ':blue_book:' => '📘', ':orange_book:' => '📙', ':notebook:' => '📓',
            ':notebook_with_decorative_cover:' => '📔', ':ledger:' => '📒', ':books:' => '📚', ':bookmark:' => '🔖',
            ':name_badge:' => '📛', ':microscope:' => '🔬', ':telescope:' => '🔭', ':newspaper:' => '📰',
            ':football:' => '🏈', ':basketball:' => '🏀', ':soccer:' => '⚽️', ':baseball:' => '⚾️',
            ':tennis:' => '🎾', ':8ball:' => '🎱', ':rugby_football:' => '🏉', ':bowling:' => '🎳',
            ':golf:' => '⛳️', ':mountain_bicyclist:' => '🚵', ':bicyclist:' => '🚴', ':horse_racing:' => '🏇',
            ':snowboarder:' => '🏂', ':swimmer:' => '🏊', ':surfer:' => '🏄', ':ski:' => '🎿',
            ':spades:' => '♠️', ':hearts:' => '♥️', ':clubs:' => '♣️', ':diamonds:' => '♦️',
            ':gem:' => '💎', ':ring:' => '💍', ':trophy:' => '🏆', ':musical_score:' => '🎼',
            ':musical_keyboard:' => '🎹', ':violin:' => '🎻', ':space_invader:' => '👾', ':video_game:' => '🎮',
            ':black_joker:' => '🃏', ':flower_playing_cards:' => '🎴', ':game_die:' => '🎲', ':dart:' => '🎯',
            ':mahjong:' => '🀄️', ':clapper:' => '🎬', ':memo:' => '📝', ':pencil:' => '📝',
            ':book:' => '📖', ':art:' => '🎨', ':microphone:' => '🎤', ':headphones:' => '🎧',
            ':trumpet:' => '🎺', ':saxophone:' => '🎷', ':guitar:' => '🎸', ':shoe:' => '👞',
            ':sandal:' => '👡', ':high_heel:' => '👠', ':lipstick:' => '💄', ':boot:' => '👢',
            ':shirt:' => '👕', ':tshirt:' => '👕', ':necktie:' => '👔', ':womans_clothes:' => '👚',
            ':dress:' => '👗', ':running_shirt_with_sash:' => '🎽', ':jeans:' => '👖', ':kimono:' => '👘',
            ':bikini:' => '👙', ':ribbon:' => '🎀', ':tophat:' => '🎩', ':crown:' => '👑',
            ':womans_hat:' => '👒', ':mans_shoe:' => '👞', ':closed_umbrella:' => '🌂', ':briefcase:' => '💼',
            ':handbag:' => '👜', ':pouch:' => '👝', ':purse:' => '👛', ':eyeglasses:' => '👓',
            ':fishing_pole_and_fish:' => '🎣', ':coffee:' => '☕️', ':tea:' => '🍵', ':sake:' => '🍶',
            ':baby_bottle:' => '🍼', ':beer:' => '🍺', ':beers:' => '🍻', ':cocktail:' => '🍸',
            ':tropical_drink:' => '🍹', ':wine_glass:' => '🍷', ':fork_and_knife:' => '🍴', ':pizza:' => '🍕',
            ':hamburger:' => '🍔', ':fries:' => '🍟', ':poultry_leg:' => '🍗', ':meat_on_bone:' => '🍖',
            ':spaghetti:' => '🍝', ':curry:' => '🍛', ':fried_shrimp:' => '🍤', ':bento:' => '🍱',
            ':sushi:' => '🍣', ':fish_cake:' => '🍥', ':rice_ball:' => '🍙', ':rice_cracker:' => '🍘',
            ':rice:' => '🍚', ':ramen:' => '🍜', ':stew:' => '🍲', ':oden:' => '🍢',
            ':dango:' => '🍡', ':egg:' => '🥚', ':bread:' => '🍞', ':doughnut:' => '🍩',
            ':custard:' => '🍮', ':icecream:' => '🍦', ':ice_cream:' => '🍨', ':shaved_ice:' => '🍧',
            ':birthday:' => '🎂', ':cake:' => '🍰', ':cookie:' => '🍪', ':chocolate_bar:' => '🍫',
            ':candy:' => '🍬', ':lollipop:' => '🍭', ':honey_pot:' => '🍯', ':apple:' => '🍎',
            ':green_apple:' => '🍏', ':tangerine:' => '🍊', ':lemon:' => '🍋', ':cherries:' => '🍒',
            ':grapes:' => '🍇', ':watermelon:' => '🍉', ':strawberry:' => '🍓', ':peach:' => '🍑',
            ':melon:' => '🍈', ':banana:' => '🍌', ':pear:' => '🍐', ':pineapple:' => '🍍',
            ':sweet_potato:' => '🍠', ':eggplant:' => '🍆', ':tomato:' => '🍅', ':corn:' => '🌽',
            ':house:' => '🏠', ':house_with_garden:' => '🏡', ':school:' => '🏫', ':office:' => '🏢',
            ':post_office:' => '🏣', ':hospital:' => '🏥', ':bank:' => '🏦', ':convenience_store:' => '🏪',
            ':love_hotel:' => '🏩', ':hotel:' => '🏨', ':wedding:' => '💒', ':church:' => '⛪️',
            ':department_store:' => '🏬', ':european_post_office:' => '🏤', ':city_sunrise:' => '🌇', ':city_sunset:' => '🌆',
            ':japanese_castle:' => '🏯', ':european_castle:' => '🏰', ':tent:' => '⛺️', ':factory:' => '🏭',
            ':tokyo_tower:' => '🗼', ':japan:' => '🗾', ':mount_fuji:' => '🗻', ':sunrise_over_mountains:' => '🌄',
            ':sunrise:' => '🌅', ':stars:' => '🌠', ':statue_of_liberty:' => '🗽', ':bridge_at_night:' => '🌉',
            ':carousel_horse:' => '🎠', ':rainbow:' => '🌈', ':ferris_wheel:' => '🎡', ':fountain:' => '⛲️',
            ':roller_coaster:' => '🎢', ':ship:' => '🚢', ':speedboat:' => '🚤', ':boat:' => '⛵️',
            ':sailboat:' => '⛵️', ':rowboat:' => '🚣', ':anchor:' => '⚓️', ':rocket:' => '🚀',
            ':airplane:' => '✈️', ':helicopter:' => '🚁', ':steam_locomotive:' => '🚂', ':tram:' => '🚊',
            ':mountain_railway:' => '🚞', ':bike:' => '🚲', ':aerial_tramway:' => '🚡', ':suspension_railway:' => '🚟',
            ':mountain_cableway:' => '🚠', ':tractor:' => '🚜', ':blue_car:' => '🚙', ':oncoming_automobile:' => '🚘',
            ':car:' => '🚗', ':red_car:' => '🚗', ':taxi:' => '🚕', ':oncoming_taxi:' => '🚖',
            ':articulated_lorry:' => '🚛', ':bus:' => '🚌', ':oncoming_bus:' => '🚍', ':rotating_light:' => '🚨',
            ':police_car:' => '🚓', ':oncoming_police_car:' => '🚔', ':fire_engine:' => '🚒', ':ambulance:' => '🚑',
            ':minibus:' => '🚐', ':truck:' => '🚚', ':train:' => '🚋', ':station:' => '🚉',
            ':train2:' => '🚆', ':bullettrain_front:' => '🚅', ':bullettrain_side:' => '🚄', ':light_rail:' => '🚈',
            ':monorail:' => '🚝', ':railway_car:' => '🚃', ':trolleybus:' => '🚎', ':ticket:' => '🎫',
            ':fuelpump:' => '⛽️', ':vertical_traffic_light:' => '🚦', ':traffic_light:' => '🚥', ':warning:' => '⚠️',
            ':construction:' => '🚧', ':beginner:' => '🔰', ':atm:' => '🏧', ':slot_machine:' => '🎰',
            ':busstop:' => '🚏', ':barber:' => '💈', ':hotsprings:' => '♨️', ':checkered_flag:' => '🏁',
            ':crossed_flags:' => '🎌', ':izakaya_lantern:' => '🏮', ':moyai:' => '🗿', ':circus_tent:' => '🎪',
            ':performing_arts:' => '🎭', ':round_pushpin:' => '📍', ':triangular_flag_on_post:' => '🚩', ':jp:' => '🇯🇵',
            ':kr:' => '🇰🇷', ':cn:' => '🇨🇳', ':us:' => '🇺🇸', ':fr:' => '🇫🇷',
            ':es:' => '🇪🇸', ':it:' => '🇮🇹', ':ru:' => '🇷🇺', ':gb:' => '🇬🇧',
            ':uk:' => '🇬🇧', ':de:' => '🇩🇪', ':one:' => '1️⃣', ':two:' => '2️⃣',
            ':three:' => '3️⃣', ':four:' => '4️⃣', ':five:' => '5️⃣', ':six:' => '6️⃣',
            ':seven:' => '7️⃣', ':eight:' => '8️⃣', ':nine:' => '9️⃣', ':keycap_ten:' => '🔟',
            ':1234:' => '🔢', ':zero:' => '0️⃣', ':hash:' => '#️⃣', ':symbols:' => '🔣',
            ':arrow_backward:' => '◀️', ':arrow_down:' => '⬇️', ':arrow_forward:' => '▶️', ':arrow_left:' => '⬅️',
            ':capital_abcd:' => '🔠', ':abcd:' => '🔡', ':abc:' => '🔤', ':arrow_lower_left:' => '↙️',
            ':arrow_lower_right:' => '↘️', ':arrow_right:' => '➡️', ':arrow_up:' => '⬆️', ':arrow_upper_left:' => '↖️',
            ':arrow_upper_right:' => '↗️', ':arrow_double_down:' => '⏬', ':arrow_double_up:' => '⏫', ':arrow_down_small:' => '🔽',
            ':arrow_heading_down:' => '⤵️', ':arrow_heading_up:' => '⤴️', ':leftwards_arrow_with_hook:' => '↩️', ':arrow_right_hook:' => '↪️',
            ':left_right_arrow:' => '↔️', ':arrow_up_down:' => '↕️', ':arrow_up_small:' => '🔼', ':arrows_clockwise:' => '🔃',
            ':arrows_counterclockwise:' => '🔄', ':rewind:' => '⏪', ':fast_forward:' => '⏩', ':information_source:' => 'ℹ️',
            ':ok:' => '🆗', ':twisted_rightwards_arrows:' => '🔀', ':repeat:' => '🔁', ':repeat_one:' => '🔂',
            ':new:' => '🆕', ':top:' => '🔝', ':up:' => '🆙', ':cool:' => '🆒',
            ':free:' => '🆓', ':ng:' => '🆖', ':cinema:' => '🎦', ':koko:' => '🈁',
            ':signal_strength:' => '📶', ':u5272:' => '🈹', ':u5408:' => '🈴', ':u55b6:' => '🈺',
            ':u6307:' => '🈯️', ':u6708:' => '🈷️', ':u6709:' => '🈶', ':u6e80:' => '🈵',
            ':u7121:' => '🈚️', ':u7533:' => '🈸', ':u7a7a:' => '🈳', ':u7981:' => '🈲',
            ':sa:' => '🈂️', ':restroom:' => '🚻', ':mens:' => '🚹', ':womens:' => '🚺',
            ':baby_symbol:' => '🚼', ':no_smoking:' => '🚭', ':parking:' => '🅿️', ':wheelchair:' => '♿️',
            ':metro:' => '🚇', ':baggage_claim:' => '🛄', ':accept:' => '🉑', ':wc:' => '🚾',
            ':potable_water:' => '🚰', ':put_litter_in_its_place:' => '🚮', ':secret:' => '㊙️', ':congratulations:' => '㊗️',
            ':m:' => 'Ⓜ️', ':passport_control:' => '🛂', ':left_luggage:' => '🛅', ':customs:' => '🛃',
            ':ideograph_advantage:' => '🉐', ':cl:' => '🆑', ':sos:' => '🆘', ':id:' => '🆔',
            ':no_entry_sign:' => '🚫', ':underage:' => '🔞', ':no_mobile_phones:' => '📵', ':do_not_litter:' => '🚯',
            ':non-potable_water:' => '🚱', ':no_bicycles:' => '🚳', ':no_pedestrians:' => '🚷', ':children_crossing:' => '🚸',
            ':no_entry:' => '⛔️', ':eight_spoked_asterisk:' => '✳️', ':eight_pointed_black_star:' => '✴️', ':heart_decoration:' => '💟',
            ':vs:' => '🆚', ':vibration_mode:' => '📳', ':mobile_phone_off:' => '📴', ':chart:' => '💹',
            ':currency_exchange:' => '💱', ':aries:' => '♈️', ':taurus:' => '♉️', ':gemini:' => '♊️',
            ':cancer:' => '♋️', ':leo:' => '♌️', ':virgo:' => '♍️', ':libra:' => '♎️',
            ':scorpius:' => '♏️', ':sagittarius:' => '♐️', ':capricorn:' => '♑️', ':aquarius:' => '♒️',
            ':pisces:' => '♓️', ':ophiuchus:' => '⛎', ':six_pointed_star:' => '🔯', ':negative_squared_cross_mark:' => '❎',
            ':a:' => '🅰️', ':b:' => '🅱️', ':ab:' => '🆎', ':o2:' => '🅾️',
            ':diamond_shape_with_a_dot_inside:' => '💠', ':recycle:' => '♻️', ':end:' => '🔚', ':on:' => '🔛',
            ':soon:' => '🔜', ':clock1:' => '🕐', ':clock130:' => '🕜', ':clock10:' => '🕙',
            ':clock1030:' => '🕥', ':clock11:' => '🕚', ':clock1130:' => '🕦', ':clock12:' => '🕛',
            ':clock1230:' => '🕧', ':clock2:' => '🕑', ':clock230:' => '🕝', ':clock3:' => '🕒',
            ':clock330:' => '🕞', ':clock4:' => '🕓', ':clock430:' => '🕟', ':clock5:' => '🕔',
            ':clock530:' => '🕠', ':clock6:' => '🕕', ':clock630:' => '🕡', ':clock7:' => '🕖',
            ':clock730:' => '🕢', ':clock8:' => '🕗', ':clock830:' => '🕣', ':clock9:' => '🕘',
            ':clock930:' => '🕤', ':heavy_dollar_sign:' => '💲', ':copyright:' => '©️', ':registered:' => '®️',
            ':tm:' => '™️', ':x:' => '❌', ':heavy_exclamation_mark:' => '❗️', ':bangbang:' => '‼️',
            ':interrobang:' => '⁉️', ':o:' => '⭕️', ':heavy_multiplication_x:' => '✖️', ':heavy_plus_sign:' => '➕',
            ':heavy_minus_sign:' => '➖', ':heavy_division_sign:' => '➗', ':white_flower:' => '💮', ':100:' => '💯',
            ':heavy_check_mark:' => '✔️', ':ballot_box_with_check:' => '☑️', ':radio_button:' => '🔘', ':link:' => '🔗',
            ':curly_loop:' => '➰', ':wavy_dash:' => '〰️', ':part_alternation_mark:' => '〽️', ':trident:' => '🔱',
            ':white_check_mark:' => '✅', ':black_square_button:' => '🔲', ':white_square_button:' => '🔳', ':black_circle:' => '⚫️',
            ':white_circle:' => '⚪️', ':red_circle:' => '🔴', ':large_blue_circle:' => '🔵', ':large_blue_diamond:' => '🔷',
            ':large_orange_diamond:' => '🔶', ':small_blue_diamond:' => '🔹', ':small_orange_diamond:' => '🔸', ':small_red_triangle:' => '🔺',
            ':small_red_triangle_down:' => '🔻', ':black_small_square:' => '▪️', ':black_medium_small_square:' => '◾', ':black_medium_square:' => '◼️',
            ':black_large_square:' => '⬛', ':white_small_square:' => '▫️', ':white_medium_small_square:' => '◽', ':white_medium_square:' => '◻️',
            ':white_large_square:' => '⬜',
        ];

        // Check there is no character before the emoji marker
        if (!preg_match('/^(\s|)$/', $Excerpt['before'])) {
            return null;
        }

        if (preg_match('/^(:)([a-zA-Z0-9_]+)(:)/', $Excerpt['text'], $matches)) {
            return [
                'extent' => strlen($matches[0]),
                'element' => [
                    'text' => str_replace(array_keys($emojiMap), $emojiMap, $matches[0]),
                ],
            ];
        }

        return null;
    }

    // Block types
    // -------------------------------------------------------------------------

    protected function parseAttributeData($attributeString)
    {
        if($this->config()->get('special_attributes')) {
            return parent::parseAttributeData($attributeString);
        }

        return [];
    }

    protected function blockFootnote($Line)
    {
        if ($this->config()->get('footnotes')) {
            return parent::blockFootnote($Line);
        }
    }

    protected function blockDefinitionList($Line, $Block)
    {
        if ($this->config()->get('definition_lists')) {
            return parent::blockDefinitionList($Line, $Block);
        }
    }

    protected function blockCode($Line, $Block = null)
    {
        if ($this->config()->get('code') && $this->config()->get('code.blocks')) {
            return parent::blockCode($Line, $Block);
        }
    }

    protected function blockComment($Line)
    {
        if ($this->config()->get('comments')) {
            return parent::blockComment($Line);
        }
    }

    protected function blockList($Line, array $CurrentBlock = null)
    {
        if ($this->config()->get('lists')) {
            return parent::blockList($Line, $CurrentBlock);
        }
    }

    protected function blockQuote($Line)
    {
        if ($this->config()->get('quotes')) {
            return parent::blockQuote($Line);
        }
    }

    protected function blockRule($Line)
    {
        if ($this->config()->get('thematic_breaks')) {
            return parent::blockRule($Line);
        }
    }

    protected function blockMarkup($Line)
    {
        if ($this->config()->get('markup')) {
            return parent::blockMarkup($Line);
        }
    }

    protected function blockReference($Line)
    {
        if ($this->config()->get('references')) {
            return parent::blockReference($Line);
        }
    }

    protected function blockTable($Line, $Block = null)
    {
        if ($this->config()->get('tables')) {
            return parent::blockTable($Line, $Block);
        }
    }


    protected function blockMathNotation($Line)
    {
        if (!$this->config()->get('math') || !$this->config()->get('math.block')) {
            return null;
        }

        foreach ($this->config()->get('math.block.delimiters') as $config) {

            $leftMarker = preg_quote($config['left'], '/');
            $rightMarker = preg_quote($config['right'], '/');
            $regex = '/^(?<!\\\\)('. $leftMarker . ')(.*?)(?=(?<!\\\\)' . $rightMarker . '|$)/';

            if (preg_match($regex, $Line['text'], $matches)) {
                return [
                    'element' => [
                        'text' => $matches[2],
                    ],
                    'start' => $config['left'], // Store the start marker
                    'end' => $config['right'], // Store the end marker
                ];
            }
        }

        return;
    }


    protected function blockMathNotationContinue($Line, $Block)
    {
        if (isset($Block['complete'])) {
            return;
        }

        if (isset($Block['interrupted'])) {
            $Block['element']['text'] .= str_repeat("\n", $Block['interrupted']);
            unset($Block['interrupted']);
        }

        // Double escape the backslashes for regex pattern
        $rightMarker = preg_quote($Block['end'], '/');
        $regex = '/^(?<!\\\\)(' . $rightMarker . ')(.*)/';

        if (preg_match($regex, $Line['text'], $matches)) {
            $Block['complete'] = true;
            $Block['math'] = true;
            $Block['element']['text'] = $Block['start'] . $Block['element']['text'] . $Block['end'] . $matches[2];


            return $Block;
        }

        $Block['element']['text'] .= "\n" . $Line['body'];

        return $Block;
    }


    protected function blockMathNotationComplete($Block)
    {
        return $Block;
    }



    protected function blockFencedCode($Line)
    {
        if (!$this->config()->get('code') or !$this->config()->get('code.blocks')) {
            return;
        }

        $Block = parent::blockFencedCode($Line);
        $marker = $Line['text'][0];
        $openerLength = strspn($Line['text'], $marker);

        // Extract language from the line
        $parts = explode(' ', trim(substr($Line['text'], $openerLength)), 2);
        $language = strtolower($parts[0]);

        // Check if diagrams are enabled
        if (!$this->config()->get('diagrams')) {
            return $Block;
        }

        $extensions = [
            'mermaid' => ['div', 'mermaid'],
            'chart' => ['canvas', 'chartjs'],
            // Add more languages here as needed
        ];

        if (isset($extensions[$language])) {
            [$elementName, $class] = $extensions[$language];

            if(!$this->legacyMode) {
                // 1.8
                return [
                    'char' => $marker,
                    'openerLength' => $openerLength,
                    'element' => [
                        'name' => $elementName,
                        'element' => [
                            'text' => '',
                        ],
                        'attributes' => [
                            'class' => $class,
                        ],
                    ],
                ];
            } else {
                // 1.7
                return [
                    "char" => $marker,
                    'openerLength' => $openerLength,
                    "element" => [
                        "name" => $elementName,
                        "handler" => "element",
                        "text" => [
                            "text" => "",
                        ],
                        "attributes" => [
                            "class" => $class,
                        ],
                    ],
                ];
            }
        }

        return $Block;
    }


    protected function li($lines)
    {
        if (!$this->config()->get('lists.tasks')) {
            return parent::li($lines);
        }

        if ($this->legacyMode) {
            $markup = $this->lines($lines);

            // Get first 4 charhacters of the markup
            $firstFourChars = substr($markup, 4, 4);
            // if it is a checkbox
            if (preg_match('/^\[[x ]\]/i', $firstFourChars, $matches)) {
                // check if it is checked
                if (strtolower($matches[0]) === '[x]') {
                    // replace from the 4th character and 4 characters after with a checkbox
                    $markup = substr_replace($markup, '<input type="checkbox" disabled="disabled" checked="checked" />', 4, 4);
                } else {
                    // replace from the 4th character and 4 characters after with a checkbox
                    $markup = substr_replace($markup, '<input type="checkbox" disabled="disabled" />', 4, 4);
                }
            }

            $trimmedMarkup = trim($markup);

            if (! in_array('', $lines) and substr($trimmedMarkup, 0, 3) === '<p>') {
                $markup = $trimmedMarkup;
                $markup = substr($markup, 3);

                $position = strpos($markup, "</p>");

                $markup = substr_replace($markup, '', $position, 4);
            }

            return $markup;
        } else {
            /** @psalm-suppress UndefinedMethod */
            $Elements = $this->linesElements($lines);

            $text = $Elements[0]['handler']['argument'];
            $firstFourChars = substr($text, 0, 4);
            if (preg_match('/^\[[x ]\]/i', $firstFourChars, $matches)) {
                $Elements[0]['handler']['argument'] = substr_replace($text, '', 0, 4);
                if (strtolower($matches[0]) === '[x]') {
                    $Elements[0]['attributes'] = [
                        'checked' => 'checked',
                        'type' => 'checkbox',
                        'disabled' => 'disabled',
                    ];
                } else {
                    $Elements[0]['attributes'] = [
                        'type' => 'checkbox',
                        'disabled' => 'disabled',
                    ];
                }
                $Elements[0]['name'] = 'input';
            }


            if (! in_array('', $lines)
                and isset($Elements[0]) and isset($Elements[0]['name'])
                and $Elements[0]['name'] === 'p'
            ) {
                unset($Elements[0]['name']);
            }

            return $Elements;
        }
    }



    protected function blockHeader($Line)
    {
        if (!$this->config()->get('headings')) {
            return;
        }

        $Block = parent::blockHeader($Line);

        if (! empty($Block)) {
            $text = $Block['element']['text'] ?? $Block['element']['handler']['argument'] ?? '';
            $level = $Block['element']['name'];

            // check if level is allowed
            if (!in_array($level, $this->config()->get('headings.allowed'))) {
                return;
            }

            // Prepare value for id generation by checking if the id attribute is set else use the text
            $id = $Block['element']['attributes']['id'] ?? $text;
            $id = $this->createAnchorID($id);

            $Block['element']['attributes'] = ['id' => $id];

            // Check if heading level is in the selectors
            if (!in_array($level, $this->config()->get('toc.headings'))) {
                return $Block;
            }

            $this->setContentsList(['text' => $text, 'id' => $id, 'level' => $level]);

            return $Block;
        }
    }

    protected function blockSetextHeader($Line, $Block = null)
    {
        if (!$this->config()->get('headings')) {
            return;
        }

        $Block = parent::blockSetextHeader($Line, $Block);

        if (! empty($Block)) {
            $text = $Block['element']['text'] ?? $Block['element']['handler']['argument'] ?? '';
            $level = $Block['element']['name'];

            // check if level is allowed
            if (!in_array($level, $this->config()->get('headings.allowed'))) {
                return;
            }

            // Prepare value for id generation by checking if the id attribute is set else use the text
            $id = $Block['element']['attributes']['id'] ?? $text;
            $id = $this->createAnchorID($id);

            $Block['element']['attributes'] = ['id' => $id];

            // Check if heading level is in the selectors
            if (!in_array($level, $this->config()->get('toc.headings'))) {
                return $Block;
            }

            $this->setContentsList(['text' => $text, 'id' => $id, 'level' => $level]);

            return $Block;
        }
    }


    protected function blockAbbreviation($Line)
    {
        if ($this->config()->get('abbreviations')) {
            foreach ($this->config()->get('abbreviations.predefine') as $abbreviations => $description) {
                $this->DefinitionData['Abbreviation'][$abbreviations] = $description;
            }

            if ($this->config()->get('abbreviations.allow_custom_abbr')) {
                return parent::blockAbbreviation($Line);
            }

            return;
        }
    }



    /**
     * Tablespan
     * Modifyed version of Tablespan by @KENNYSOFT
     */
    protected function blockTableComplete(array $block): array
    {
        if (!$this->config()->get('tables.tablespan')) {
            return $block;
        }

        if ($this->legacyMode === true) {
            // 1.7
            $headerElements = & $block['element']['text'][0]['text'][0]['text'];
        } else {
            // 1.8
            $headerElements = & $block['element']['elements'][0]['elements'][0]['elements'];
        }

        for ($index = count($headerElements) - 1; $index >= 0; --$index) {
            $colspan = 1;
            $headerElement = & $headerElements[$index];

            if ($this->legacyMode === true) {
                // 1.7
                while ($index && $headerElements[$index - 1]['text'] === '>') {
                    $colspan++;
                    /** @psalm-suppress UnsupportedReferenceUsage */
                    $PreviousHeaderElement = & $headerElements[--$index];
                    $PreviousHeaderElement['merged'] = true;
                    if (isset($PreviousHeaderElement['attributes'])) {
                        $headerElement['attributes'] = $PreviousHeaderElement['attributes'];
                    }
                }
            } else {
                // 1.8
                while ($index && '>' === $headerElements[$index - 1]['handler']['argument']) {
                    $colspan++;
                    /** @psalm-suppress UnsupportedReferenceUsage */
                    $PreviousHeaderElement = & $headerElements[--$index];
                    $PreviousHeaderElement['merged'] = true;
                    if (isset($PreviousHeaderElement['attributes'])) {
                        $headerElement['attributes'] = $PreviousHeaderElement['attributes'];
                    }
                }
            }

            if ($colspan > 1) {
                if (! isset($headerElement['attributes'])) {
                    $headerElement['attributes'] = [];
                }
                $headerElement['attributes']['colspan'] = $colspan;
            }
        }

        for ($index = count($headerElements) - 1; $index >= 0; --$index) {
            if (isset($headerElements[$index]['merged'])) {
                array_splice($headerElements, $index, 1);
            }
        }

        if ($this->legacyMode === true) {
            // 1.7
            $rows = & $block['element']['text'][1]['text'];
        } else {
            // 1.8
            $rows = & $block['element']['elements'][1]['elements'];
        }

        // Colspan
        foreach ($rows as $rowNo => &$row) {
            if ($this->legacyMode === true) {
                // 1.7
                $elements = & $row['text'];
            } else {
                // 1.8
                $elements = & $row['elements'];
            }

            for ($index = count($elements) - 1; $index >= 0; --$index) {
                $colspan = 1;
                $element = & $elements[$index];

                if ($this->legacyMode === true) {
                    // 1.7
                    while ($index && $elements[$index - 1]['text'] === '>') {
                        $colspan++;
                        /** @psalm-suppress UnsupportedReferenceUsage */
                        $PreviousElement = & $elements[--$index];
                        $PreviousElement['merged'] = true;
                        if (isset($PreviousElement['attributes'])) {
                            $element['attributes'] = $PreviousElement['attributes'];
                        }
                    }
                } else {
                    // 1.8
                    while ($index && '>' === $elements[$index - 1]['handler']['argument']) {
                        ++$colspan;
                        /** @psalm-suppress UnsupportedReferenceUsage */
                        $PreviousElement = &$elements[--$index];
                        $PreviousElement['merged'] = true;
                        if (isset($PreviousElement['attributes'])) {
                            $element['attributes'] = $PreviousElement['attributes'];
                        }
                    }
                }

                if ($colspan > 1) {
                    if (! isset($element['attributes'])) {
                        $element['attributes'] = [];
                    }
                    $element['attributes']['colspan'] = $colspan;
                }
            }
        }

        // Rowspan
        foreach ($rows as $rowNo => &$row) {

            if ($this->legacyMode === true) {
                // 1.7
                $elements = & $row['text'];
            } else {
                // 1.8
                $elements = &$row['elements'];
            }

            foreach ($elements as $index => &$element) {
                $rowspan = 1;

                if (isset($element['merged'])) {
                    continue;
                }

                if ($this->legacyMode === true) {
                    // 1.7
                    while ($rowNo + $rowspan < count($rows) && $index < count($rows[$rowNo + $rowspan]['text']) && $rows[$rowNo + $rowspan]['text'][$index]['text'] === '^' && (@$element['attributes']['colspan'] ?: null) === (@$rows[$rowNo + $rowspan]['text'][$index]['attributes']['colspan'] ?: null)) {
                        $rows[$rowNo + $rowspan]['text'][$index]['merged'] = true;
                        $rowspan++;
                    }
                } else {
                    // 1.8
                    while ($rowNo + $rowspan < count($rows) && $index < count($rows[$rowNo + $rowspan]['elements']) && '>' === $rows[$rowNo + $rowspan]['elements'][$index]['handler']['argument'] && (@$element['attributes']['colspan'] ?: null) === (@$rows[$rowNo + $rowspan]['elements'][$index]['attributes']['colspan'] ?: null)) {
                        $rows[$rowNo + $rowspan]['elements'][$index]['merged'] = true;
                        $rowspan++;
                    }
                }

                if ($rowspan > 1) {
                    if (! isset($element['attributes'])) {
                        $element['attributes'] = [];
                    }
                    $element['attributes']['rowspan'] = $rowspan;
                }
            }
        }

        foreach ($rows as $rowNo => &$row) {

            if ($this->legacyMode === true) {
                // 1.7
                $elements = & $row['text'];
            } else {
                // 1.8
                $elements = & $row['elements'];
            }

            for ($index = count($elements) - 1; $index >= 0; --$index) {
                if (isset($elements[$index]['merged'])) {
                    array_splice($elements, $index, 1);
                }
            }
        }

        return $block;
    }





    // Functions related to Table of Contents
    // Modified version of ToC by @KEINOS
    // -------------------------------------------------------------------------


    public function body(string $text): string
    {
        $text = $this->encodeTag($text); // Escapes ToC tag temporarily
        $html = parent::text($text);     // Parses the markdown text
        return $this->decodeTag($html);  // Unescapes the ToC tag
    }


    public function contentsList(string $type_return = 'string'): string
    {
        switch (strtolower($type_return)) {
            case 'string':
                return $this->contentsListString ? $this->body($this->contentsListString) : '';
            case 'json':
                return json_encode($this->contentsListArray);
            default:
                $backtrace = debug_backtrace();
                $caller = $backtrace[0];
                $errorMessage = "Unknown return type '{$type_return}' given while parsing ToC. Called in " . ($caller['file'] ?? 'unknown') . " on line " . ($caller['line'] ?? 'unknown');
                throw new \InvalidArgumentException($errorMessage);
        }
    }



    public function setCreateAnchorIDCallback(callable $callback): void
    {
        $this->createAnchorIDCallback = $callback;
    }


    protected function createAnchorID(string $text): ?string
    {
        // Check settings
        if (!$this->config()->get('headings.auto_anchors')) {
            return null;
        }

        // Use user-defined logic if a callback is provided
        if (is_callable($this->createAnchorIDCallback)) {
            return call_user_func($this->createAnchorIDCallback, $text, $this->config()());
        }

        // Default logic

        if ($this->config()->get('headings.auto_anchors.lowercase')) {
            if (extension_loaded('mbstring')) {
                $text = mb_strtolower($text);
            } else {
                $text = strtolower($text);
            }
        }

        // Note we don't use isEnabled here
        if($this->config()->get('headings.auto_anchors.replacements')) {
            $text = preg_replace(array_keys($this->config()->get('headings.auto_anchors.replacements')), $this->config()->get('headings.auto_anchors.replacements'), $text);
        }

        $text = $this->normalizeString($text);

        if ($this->config()->get('headings.auto_anchors.transliterate')) {
            $text = $this->transliterate($text);
        }

        $text = $this->sanitizeAnchor($text);

        return $this->uniquifyAnchorID($text);
    }


    protected function normalizeString(string $text)
    {
        if (extension_loaded('mbstring')) {
            return mb_convert_encoding($text, 'UTF-8', mb_list_encodings());
        } else {
            return $text; // Return raw as there is no good alternative for mb_convert_encoding
        }
    }


    protected function transliterate(string $text): string
    {
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

        return strtr($text, $characterMap);
    }


    protected function sanitizeAnchor(string $text): string
    {
        $delimiter = $this->config()->get('headings.auto_anchors.delimiter');
        // Replace non-alphanumeric characters with our delimiter
        $text = preg_replace('/[^\p{L}\p{Nd}]+/u', $delimiter, $text);
        // Remove consecutive delimiters
        $text = preg_replace('/(' . preg_quote($delimiter, '/') . '){2,}/', '$1', $text);
        // Remove leading and trailing delimiters
        $text = trim($text, $delimiter);
        return $text;
    }


    protected function uniquifyAnchorID(string $text): string
    {
        $blacklist = $this->config()->get('headings.auto_anchors.blacklist');
        $originalText = $text; // Keep the original text for reference

        // Initialize the count for this text if not already set
        if (!isset($this->anchorRegister[$text])) {
            $this->anchorRegister[$text] = 0;
        } else {
            // If already set, increment to check for the next possible suffix
            $this->anchorRegister[$text]++;
        }

        // Adjust the count based on the blacklist, ensuring we skip blacklisted numbers
        while (true) {
            $potentialId = $originalText . ($this->anchorRegister[$text] > 0 ? '-' . $this->anchorRegister[$text] : '');
            if (!in_array($potentialId, $blacklist)) {
                break; // Found a non-blacklisted ID, stop adjusting the count
            }
            $this->anchorRegister[$text]++; // Increment the count and check the next potential ID
        }

        // If the adjusted count is 0, it means the original text is not blacklisted and has not appeared before
        if ($this->anchorRegister[$text] === 0) {
            return $originalText; // Return the original text as is
        }

        // Return the text appended with the adjusted count, skipping any blacklisted numbers
        return $originalText . '-' . $this->anchorRegister[$text];
    }



    protected function decodeTag(string $text): string
    {
        $salt = $this->getSalt();
        $tag_origin = $this->getTagToc();
        $tag_hashed = hash('sha256', $salt . $tag_origin);

        if (strpos($text, $tag_hashed) === false) {
            return $text;
        }

        return str_replace($tag_hashed, $tag_origin, $text);
    }


    protected function encodeTag(string $text): string
    {
        $salt = $this->getSalt();
        $tag_origin = $this->getTagToc();

        if (strpos($text, $tag_origin) === false) {
            return $text;
        }

        $tag_hashed = hash('sha256', $salt . $tag_origin);

        return str_replace($tag_origin, $tag_hashed, $text);
    }


    protected function fetchText($text): string
    {
        return trim(strip_tags($this->line($text)));
    }


    protected function getIdAttributeToc(): string
    {
        if (!empty($this->id_toc)) {
            return $this->id_toc;
        }

        return self::TOC_ID_ATTRIBUTE_DEFAULT;
    }


    protected function getSalt(): string
    {
        static $salt;
        if (isset($salt)) {
            return $salt;
        }

        $salt = hash('md5', (string) time());
        return $salt;
    }


    protected function getTagToc(): string
    {
        if (!empty($this->tag_toc)) {
            return $this->tag_toc;
        }

        return self::TOC_TAG_DEFAULT;
    }


    protected function setContentsList(array $Content): void
    {
        // Stores as an array
        $this->setContentsListAsArray($Content);
        // Stores as string in markdown list format.
        $this->setContentsListAsString($Content);
    }


    protected function setContentsListAsArray(array $Content): void
    {
        $this->contentsListArray[] = $Content;
    }


    protected function setContentsListAsString(array $Content): void
    {
        $text = $this->fetchText($Content['text']);
        $id = $Content['id'];
        $level = (int) trim($Content['level'], 'h');
        $link = "[{$text}](#{$id})";

        if ($this->firstHeadLevel === 0) {
            $this->firstHeadLevel = $level;
        }
        $indentLevel = max(1, $level - ($this->firstHeadLevel - 1));
        $indent = str_repeat('  ', $indentLevel);

        $this->contentsListString .= "{$indent}- {$link}" . PHP_EOL;
    }


    public function setTagToc($tag): void
    {
        $tag = trim($tag);
        if (self::escape($tag) === $tag) {
            // Set ToC tag if it's safe
            $this->tag_toc = $tag;
        } else {
            $backtrace = debug_backtrace();
            $caller = $backtrace[0];
            $errorMessage = "Malformed ToC user tag given: {$tag}. Called in " . ($caller['file'] ?? 'unknown') . " on line " . ($caller['line'] ?? 'unknown');
            throw new \InvalidArgumentException($errorMessage);
        }
    }


    public function text($text): string
    {
        $html = $this->body($text);

        if (!$this->config()->get('toc')) {
            return $html;
        }

        $tag_origin = $this->getTagToc();
        if (strpos($text, $tag_origin) === false) {
            return $html;
        }

        $toc_data = $this->contentsList();
        $toc_id = $this->getIdAttributeToc();
        return str_replace("<p>{$tag_origin}</p>", "<div id=\"{$toc_id}\">{$toc_data}</div>", $html);
    }


    // Helper functions
    // -------------------------------------------------------------------------


    private function addInlineType($markers, string $funcName): void
    {
        // Ensure $markers is an array, even if it's a single marker
        $markers = (array) $markers;

        foreach ($markers as $marker) {
            if (!isset($this->InlineTypes[$marker])) {
                $this->InlineTypes[$marker] = [];
            }

            // add to specialcharecters array
            if (!in_array($marker, $this->specialCharacters)) {
                $this->specialCharacters[] = $marker;
            }

            // add to the beginning of the array so it has priority
            $this->InlineTypes[$marker][] = $funcName;
            $this->inlineMarkerList .= $marker;
        }
    }



    private function addBlockType(array $markers, string $funcName): void
    {
        foreach ($markers as $marker) {
            if (!isset($this->BlockTypes[$marker])) {
                $this->BlockTypes[$marker] = [];
            }

            // add to specialcharecters array
            if (!in_array($marker, $this->specialCharacters)) {
                $this->specialCharacters[] = $marker;
            }

            // add to the beginning of the array so it has priority
            $this->BlockTypes[$marker][] = $funcName;
        }
    }


    protected function element(array $Element)
    {
        if ($this->legacyMode) {
            // Check if the name is empty
            if (empty($Element['name'])) {
                return $Element['text'] ?? '';
            }
        }

        // Use the parent
        return parent::element($Element);
    }


    /**
     * Overwrite line from Parsedown to allow for more precise control over inline elements
     * line() is 1.7 version of lineElements() from 1.8, so we overwrite it too, it will not be called
     * when using 1.8 version of parsedown
     */
    public function line($text, $nonNestables = [])
    {
        $markup = '';

        // $Excerpt is based on the first occurrence of a marker

        while ($Excerpt = strpbrk($text, $this->inlineMarkerList)) {
            $marker = $Excerpt[0];

            $markerPosition = strpos($text, $marker);

            // Get the charecter before the marker
            $before = $markerPosition > 0 ? $text[$markerPosition - 1] : '';

            $Excerpt = [
                'text' => $Excerpt,
                'context' => $text,
                'before' => $before,
                'parent' => $this,
                // 'inlineTypes' => isset($this->InlineTypes[$marker]) ? $this->InlineTypes[$marker] : [] // Not apresent in original Parsedown
            ];

            foreach ($this->InlineTypes[$marker] as $inlineType) {
                // check to see if the current inline type is nestable in the current context

                if (! empty($nonNestables) and in_array($inlineType, $nonNestables)) {
                    continue;
                }

                $Inline = $this->{'inline'.$inlineType}($Excerpt);

                if (! isset($Inline)) {
                    continue;
                }


                // makes sure that the inline belongs to "our" marker

                if (isset($Inline['position']) and $Inline['position'] > $markerPosition) {
                    continue;
                }

                // sets a default inline position

                if (! isset($Inline['position'])) {
                    $Inline['position'] = $markerPosition;
                }

                // cause the new element to 'inherit' our non nestables

                foreach ($nonNestables as $non_nestable) {
                    $Inline['element']['nonNestables'][] = $non_nestable;
                }

                // the text that comes before the inline
                $unmarkedText = substr($text, 0, $Inline['position']);

                // compile the unmarked text
                $markup .= $this->unmarkedText($unmarkedText);

                // compile the inline
                $markup .= $Inline['markup'] ?? $this->element($Inline['element']);

                // remove the examined text
                $text = substr($text, $Inline['position'] + $Inline['extent']);

                continue 2;
            }

            // the marker does not belong to an inline

            $unmarkedText = substr($text, 0, $markerPosition + 1);

            $markup .= $this->unmarkedText($unmarkedText);

            $text = substr($text, $markerPosition + 1);
        }

        $markup .= $this->unmarkedText($text);

        return $markup;
    }

    /**
     * Overwrite lineElements from Parsedown to allow for more precise control over inline elements
     * lineElements() is 1.8 version of line() from 1.7, so we overwrite it too, it will not be called
     * when using 1.7 version of parsedown
     */
    protected function lineElements($text, $nonNestables = []): array
    {

        $Elements = [];

        $nonNestables = (
            empty($nonNestables)
            ? []
            : array_combine($nonNestables, $nonNestables)
        );

        // $Excerpt is based on the first occurrence of a marker

        while ($Excerpt = strpbrk($text, $this->inlineMarkerList)) {
            $marker = $Excerpt[0];

            $markerPosition = strlen($text) - strlen($Excerpt);

            // Get the charecter before the marker
            $before = $markerPosition > 0 ? $text[$markerPosition - 1] : '';

            $Excerpt = ['text' => $Excerpt, 'context' => $text, 'before' => $before];

            foreach ($this->InlineTypes[$marker] as $inlineType) {
                // check to see if the current inline type is nestable in the current context

                if (isset($nonNestables[$inlineType])) {
                    continue;
                }

                $Inline = $this->{"inline$inlineType"}($Excerpt);

                if (! isset($Inline)) {
                    continue;
                }

                // makes sure that the inline belongs to "our" marker

                if (isset($Inline['position']) and $Inline['position'] > $markerPosition) {
                    continue;
                }

                // sets a default inline position

                if (! isset($Inline['position'])) {
                    $Inline['position'] = $markerPosition;
                }

                // cause the new element to 'inherit' our non nestables


                $Inline['element']['nonNestables'] = isset($Inline['element']['nonNestables'])
                    ? array_merge($Inline['element']['nonNestables'], $nonNestables)
                    : $nonNestables
                ;

                // the text that comes before the inline
                $unmarkedText = substr($text, 0, $Inline['position']);

                // compile the unmarked text
                /** @psalm-suppress UndefinedMethod */
                $InlineText = $this->inlineText($unmarkedText);
                $Elements[] = $InlineText['element'];

                // compile the inline
                /** @psalm-suppress UndefinedMethod */
                $Elements[] = $this->extractElement($Inline);

                // remove the examined text
                $text = substr($text, $Inline['position'] + $Inline['extent']);

                continue 2;
            }

            // the marker does not belong to an inline

            $unmarkedText = substr($text, 0, $markerPosition + 1);

            /** @psalm-suppress UndefinedMethod */
            $InlineText = $this->inlineText($unmarkedText);
            $Elements[] = $InlineText['element'];

            $text = substr($text, $markerPosition + 1);
        }

        /** @psalm-suppress UndefinedMethod */
        $InlineText = $this->inlineText($text);
        $Elements[] = $InlineText['element'];

        foreach ($Elements as &$Element) {
            if (! isset($Element['autobreak'])) {
                $Element['autobreak'] = false;
            }
        }

        return $Elements;
    }


    // Configurations Handler
    // -------------------------------------------------------------------------

    protected function defineConfigSchema(): array
    {
        return [
            'abbreviations' => [
                'enabled' => ['type' => 'boolean', 'default' => true],
                'allow_custom_abbr' => ['type' => 'boolean', 'default' => true],
                'predefine' => [
                    'type' => 'array',
                    'default' => [],
                    'itemSchema' => ['type' => 'array', 'keys' => ['abbr' => 'string', 'expansion' => 'string']]
                ],
            ],
            'code' => [
                'enabled' => ['type' => 'boolean', 'default' => true],
                'blocks' => ['type' => 'boolean', 'default' => true],
                'inline' => ['type' => 'boolean', 'default' => true],
            ],
            'comments' => ['type' => 'boolean', 'default' => true],
            'definition_lists' => ['type' => 'boolean', 'default' => true],
            'diagrams' => [
                'enabled' => ['type' => 'boolean', 'default' => false],
                'chartjs' => ['type' => 'boolean', 'default' => true],
                'mermaid' => ['type' => 'boolean', 'default' => true],
            ],
            'emojis' => ['type' => 'boolean', 'default' => true],
            'emphasis' => [
                'enabled' => ['type' => 'boolean', 'default' => true],
                'bold' => ['type' => 'boolean', 'default' => true],
                'italic' => ['type' => 'boolean', 'default' => true],
                'strikethroughs' => ['type' => 'boolean', 'default' => true],
                'insertions' => ['type' => 'boolean', 'default' => true],
                'subscript' => ['type' => 'boolean', 'default' => false],
                'superscript' => ['type' => 'boolean', 'default' => false],
                'keystrokes' => ['type' => 'boolean', 'default' => true],
                'marking' => ['type' => 'boolean', 'default' => true],
            ],
            'footnotes' => ['type' => 'boolean', 'default' => true],
            'headings' => [
                'enabled' => ['type' => 'boolean', 'default' => true],
                'allowed' => ['type' => 'array', 'default' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']],
                'auto_anchors' => [
                    'enabled' => ['type' => 'boolean', 'default' => true],
                    'delimiter' => ['type' => 'string', 'default' => '-'],
                    'lowercase' => ['type' => 'boolean', 'default' => true],
                    'replacements' => ['type' => 'array', 'default' => []],
                    'transliterate' => ['type' => 'boolean', 'default' => false],
                    'blacklist' => ['type' => 'array', 'default' => []],
                ],
            ],
            'images' => ['type' => 'boolean', 'default' => true],
            'links' => [
                'enabled' => ['type' => 'boolean', 'default' => true],
                'email_links' => ['type' => 'boolean', 'default' => true],
            ],
            'lists' => [
                'enabled' => ['type' => 'boolean', 'default' => true],
                'tasks' => ['type' => 'boolean', 'default' => true],
            ],
            'markup' => ['type' => 'boolean', 'default' => true],
            'math' => [
                'enabled' => ['type' => 'boolean', 'default' => false],
                'inline' => [
                    'enabled' => ['type' => 'boolean', 'default' => true],
                    'delimiters' => [
                        'type' => 'array',
                        'default' => [['left' => '\\(', 'right' => '\\)']],
                        'itemSchema' => ['type' => 'array', 'keys' => ['left' => 'string', 'right' => 'string']]
                    ],
                ],
                'block' => [
                    'enabled' => ['type' => 'boolean', 'default' => true],
                    'delimiters' => [
                        'type' => 'array',
                        'default' => [
                            ['left' => '$$', 'right' => '$$'],
                            ['left' => '\\begin{equation}', 'right' => '\\end{equation}'],
                            ['left' => '\\begin{align}', 'right' => '\\end{align}'],
                            ['left' => '\\begin{alignat}', 'right' => '\\end{alignat}'],
                            ['left' => '\\begin{gather}', 'right' => '\\end{gather}'],
                            ['left' => '\\begin{CD}', 'right' => '\\end{CD}'],
                            ['left' => '\\[', 'right' => '\\]'],
                        ],
                        'itemSchema' => ['type' => 'array', 'keys' => ['left' => 'string', 'right' => 'string']]
                    ],
                ],
            ],
            'quotes' => ['type' => 'boolean', 'default' => true],
            'references' => ['type' => 'boolean', 'default' => true],
            'smarty' => [
                'enabled' => ['type' => 'boolean', 'default' => false],
                'smart_angled_quotes' => ['type' => 'boolean', 'default' => true],
                'smart_backticks' => ['type' => 'boolean', 'default' => true],
                'smart_dashes' => ['type' => 'boolean', 'default' => true],
                'smart_ellipses' => ['type' => 'boolean', 'default' => true],
                'smart_quotes' => ['type' => 'boolean', 'default' => true],
                'substitutions' => [
                    'type' => 'array',
                    'default' => [
                        'ellipses' => ['type' => 'string', 'default' => '&hellip;'],
                        'left-angle-quote' => ['type' => 'string', 'default' => '&laquo;'],
                        'left-double-quote' => ['type' => 'string', 'default' => '&ldquo;'],
                        'left-single-quote' => ['type' => 'string', 'default' => '&lsquo;'],
                        'mdash' => ['type' => 'string', 'default' => '&mdash;'],
                        'ndash' => ['type' => 'string', 'default' => '&ndash;'],
                        'right-angle-quote' => ['type' => 'string', 'default' => '&raquo;'],
                        'right-double-quote' => ['type' => 'string', 'default' => '&rdquo;'],
                        'right-single-quote' => ['type' => 'string', 'default' => '&rsquo;'],
                    ],
                ],
            ],
            'special_attributes' => ['type' => 'boolean', 'default' => true],
            'tables' => [
                'enabled' => ['type' => 'boolean', 'default' => true],
                'tablespan' => ['type' => 'boolean', 'default' => true],
            ],
            'thematic_breaks' => ['type' => 'boolean', 'default' => true],
            'toc' => [
                'enabled' => ['type' => 'boolean', 'default' => true],
                'headings' => ['type' => 'array', 'default' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']],
                'toc_tag' => ['type' => 'string', 'default' => '[toc]'],
            ],
            'typographer' => ['type' => 'boolean', 'default' => true],
        ];
    }

    // Initialize configuration based on the provided schema
    protected function initializeConfig(array $schema)
    {
        $config = [];
        foreach ($schema as $key => $definition) {
            if (isset($definition['type'])) {
                if ($definition['type'] === 'array' && is_array($definition['default'])) {
                    // Handle array types with nested defaults
                    $config[$key] = $this->initializeConfig($definition['default']);
                } else {
                    $config[$key] = $definition['default'];
                }
            } else {
                if (is_array($definition)) {
                    // Recursively initialize nested configurations
                    $config[$key] = $this->initializeConfig($definition);
                } else {
                    // If the definition is not an array, assign it directly
                    $config[$key] = $definition;
                }
            }
        }
        return $config;
    }

    // Return a configuration manager instance
    public function config()
    {
        return new class ($this->configSchema, $this->config) {
            protected $schema;
            protected $config;

            public function __construct($schema, &$config)
            {
                $this->schema = $schema;
                $this->config = &$config;
            }

            public function get(string $keyPath)
            {
                // Split the key path into an array
                $keys = explode('.', $keyPath);
                $value = $this->config;

                foreach ($keys as $key) {
                    if (!array_key_exists($key, $value)) {
                        throw new \InvalidArgumentException("Invalid key path: \"$keyPath\"");
                    }
                    $value = $value[$key];
                }

                if (is_array($value) && isset($value['enabled'])) {
                    return $value['enabled'];
                }

                return $value;
            }

            // Set a configuration value based on a key path
            public function set($keyPath, $value = null): self
            {
                if (is_array($keyPath)) {
                    // Set multiple values if an associative array is provided
                    foreach ($keyPath as $key => $val) {
                        $this->set($key, $val);
                    }
                    return $this;
                }

                $keys = explode('.', $keyPath);
                $lastKey = array_pop($keys);
                $current = &$this->config;
                $currentSchema = $this->schema;

                // Navigate to the desired configuration section
                foreach ($keys as $key) {
                    if (!isset($current[$key])) {
                        throw new \InvalidArgumentException("Invalid key path: " . implode('.', $keys));
                    }
                    $current = &$current[$key];
                    if (!isset($currentSchema[$key])) {
                        throw new \InvalidArgumentException("Invalid schema path: " . implode('.', $keys));
                    }
                    $currentSchema = $currentSchema[$key];
                }

                // Validate and set the value for the specified key
                if (isset($currentSchema['default'][$lastKey])) {
                    $expectedType = $currentSchema['default'][$lastKey]['type'];
                    $this->validateType($value, $expectedType, $currentSchema['default'][$lastKey]);
                    $current[$lastKey] = $value;
                } else {
                    if (!isset($currentSchema[$lastKey])) {
                        throw new \InvalidArgumentException("Invalid key path: $keyPath");
                    }
                    $expectedType = $currentSchema[$lastKey]['type'] ?? null;
                    if ($expectedType) {
                        $this->validateType($value, $expectedType, $currentSchema[$lastKey]);
                    }
                    // Update to handle 'enabled' field specifically
                    if (isset($current[$lastKey]) && is_array($current[$lastKey]) && isset($current[$lastKey]['enabled'])) {
                        $current[$lastKey]['enabled'] = $value;
                    } else {
                        $current[$lastKey] = $value;
                    }
                }

                return $this;
            }


            // Validate the type of a value against the expected type
            protected function validateType($value, $expectedType, $schema = null)
            {
                $type = gettype($value);
                if ($expectedType === 'array' && $type === 'array') {
                    // Additional checks for array types
                    if (isset($schema['itemSchema'])) {
                        foreach ($value as $item) {
                            foreach ($schema['itemSchema']['keys'] as $key => $itemType) {
                                if (!isset($item[$key]) || gettype($item[$key]) !== $itemType) {
                                    throw new \InvalidArgumentException("Array items must have '$key' of type '$itemType'");
                                }
                            }
                        }
                    }
                    return;
                }
                if ($type !== $expectedType) {
                    throw new \InvalidArgumentException("Expected type $expectedType, got $type");
                }
            }
        };
    }
}
