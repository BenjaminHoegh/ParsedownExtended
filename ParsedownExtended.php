<?php

if (class_exists('ParsedownExtra')) {
    class DynamicParent extends \ParsedownExtra
    {
        public function __construct()
        {
            parent::__construct();
        }
    }
} else {
    class DynamicParent extends \Parsedown
    {
        public function __construct()
        {
            //
        }
    }
}

class ParsedownExtended extends DynamicParent
{
    /**
     * ------------------------------------------------------------------------
     *  Constants.
     * ------------------------------------------------------------------------
     */
    public const VERSION = '1.0';
    public const VERSION_PARSEDOWN_REQUIRED = '1.7';
    public const TAG_TOC_DEFAULT = '[toc]';
    public const ID_ATTRIBUTE_DEFAULT = 'toc';

    /**
     * Version requirement check.
     */
    public function __construct(array $params = null)
    {
        if (version_compare(\Parsedown::version, self::VERSION_PARSEDOWN_REQUIRED) < 0) {
            $msg_error = 'Version Error.' . PHP_EOL;
            $msg_error .= '  ParsedownToc requires a later version of Parsedown.' . PHP_EOL;
            $msg_error .= '  - Current version : ' . \Parsedown::version . PHP_EOL;
            $msg_error .= '  - Required version: ' . self::VERSION_PARSEDOWN_REQUIRED .' and later' .PHP_EOL;
            throw new Exception($msg_error);
        }

        parent::__construct();

        if (!empty($params)) {
            $this->options = $params;
        }

        /**
        * ------------------------------------------------------------------------
        * Inline
        * ------------------------------------------------------------------------
        */

        // Marks
        $state = isset($this->options['marks']) ? $this->options['marks'] : true;
        if ($state !== false) {
            $this->InlineTypes['='][] = 'marks';
            $this->inlineMarkerList .= '=';
        }

        // Keystrokes
        $state = isset($this->options['keystrokes']) ? $this->options['keystrokes'] : true;
        if ($state !== false) {
            $this->InlineTypes['['][] = 'Keystrokes';
            $this->inlineMarkerList .= '[';
        }

        // Inline Math
        $state = isset($this->options['math']) ? $this->options['math'] : false;
        if ($state !== false) {
            $this->InlineTypes['\\'][] = 'Math';
            $this->inlineMarkerList .= '\\';
            $this->InlineTypes['$'][] = 'Math';
            $this->inlineMarkerList .= '$';
        }

        // Superscript
        $state = isset($this->options['superscripts']) ? $this->options['superscripts'] : true;
        if ($state !== false) {
            $this->InlineTypes['^'][] = 'Superscript';
            $this->inlineMarkerList .= '^';
        }

        // Subscript
        $state = isset($this->options['subscripts']) ? $this->options['subscripts'] : true;
        if ($state !== false) {
            $this->InlineTypes['~'][] = 'Subscript';
        }

        // Emojis
        $state = isset($this->options['emojis']) ? $this->options['emojis'] : true;
        if ($state !== false) {
            $this->InlineTypes[':'][] = 'Emojis';
            $this->inlineMarkerList .= ':';
        }

        /**
        * ------------------------------------------------------------------------
        * Blocks
        * ------------------------------------------------------------------------
        */

        // Block Math
        $this->BlockTypes['\\'][] = 'Math';
        $this->BlockTypes['$'][] = 'Math';

        // Task
        $state = isset($this->options['lists']['task_list']) ? $this->options['lists']['task_list'] : true;
        if ($state !== false) {
            $this->BlockTypes['['][] = 'Checkbox';
        }
    }

    /**
     * ------------------------------------------------------------------------
     * Blocks
     * ------------------------------------------------------------------------
     */

    protected function blockCode($line, $block = null)
    {
        $state = isset($this->options['code_blocks']) ? $this->options['code_blocks'] : true;
        if ($state) {
            return DynamicParent::blockCode($line, $block);
        }
    }

    protected function blockComment($line)
    {
        $state = isset($this->options['comments']) ? $this->options['comments'] : true;
        if ($state) {
            return DynamicParent::blockComment($line);
        }
    }

    protected function blockHeader($line)
    {
        $state = isset($this->options['headings']) ? $this->options['headings'] : true;
        if (!$state) {
            return;
        }

        $block = DynamicParent::blockHeader($line);
        if (!empty($block)) {
            // Get the text of the heading
            if (isset($block['element']['handler']['argument'])) {
                $text = $block['element']['handler']['argument'];
            }

            // Get the heading level. Levels are h1, h2, ..., h6
            $level = $block['element']['name'];

            $headersAllowed = $this->options['headings']['allowed'] ?? ["h1", "h2", "h3", "h4", "h5", "h6"];
            if (!in_array($level, $headersAllowed)) {
                return;
            }

            // Checks if auto generated anchors is allowed
            $autoAnchors = isset($this->options['headings']['auto_anchors']) ? $this->options['headings']['auto_anchors'] : true;

            if ($autoAnchors) {
                // Get the anchor of the heading to link from the ToC list
                $id = isset($block['element']['attributes']['id']) ? $block['element']['attributes']['id'] : $this->createAnchorID($text);
            } else {
                // Get the anchor of the heading to link from the ToC list
                $id = isset($block['element']['attributes']['id']) ? $block['element']['attributes']['id'] : null;
            }

            // Set attributes to head tags
            $block['element']['attributes']['id'] = $id;

            $tocHeaders = $this->options['toc']['headings'] ?? ["h1", "h2", "h3", "h4", "h5", "h6"];
            // Check if level are defined as a heading
            if (in_array($level, $tocHeaders)) {

                // Add/stores the heading element info to the ToC list
                $this->setContentsList(array(
                    'text'  => $text,
                    'id'    => $id,
                    'level' => $level
                ));
            }

            return $block;
        }
    }

    protected function blockList($line, array $CurrentBlock = null)
    {
        $state = isset($this->options['lists']) ? $this->options['lists'] : true;
        if ($state) {
            return DynamicParent::blockList($line, $CurrentBlock);
        }
    }

    protected function blockQuote($line)
    {
        $state = isset($this->options['blockqoutes']) ? $this->options['blockqoutes'] : true;
        if ($state) {
            return DynamicParent::blockQuote($line);
        }
    }

    protected function blockRule($line)
    {
        $state = isset($this->options['thematic_breaks']) ? $this->options['thematic_breaks'] : true;
        if ($state) {
            return DynamicParent::blockRule($line);
        }
    }

    protected function blockSetextHeader($line, $block = null)
    {
        $state = isset($this->options['headings']) ? $this->options['headings'] : true;
        if (!$state) {
            return;
        }
        $block = DynamicParent::blockSetextHeader($line, $block);
        if (!empty($block)) {
            // Get the text of the heading
            if (isset($block['element']['handler']['argument'])) {
                $text = $block['element']['handler']['argument'];
            }

            // Get the heading level. Levels are h1, h2, ..., h6
            $level = $block['element']['name'];

            $headersAllowed = $this->options['headings']['allowed'] ?? ["h1", "h2", "h3", "h4", "h5", "h6"];
            if (!in_array($level, $headersAllowed)) {
                return;
            }

            // Checks if auto generated anchors is allowed
            $autoAnchors = isset($this->options['headings']['auto_anchors']) ? $this->options['headings']['auto_anchors'] : true;

            if ($autoAnchors) {
                // Get the anchor of the heading to link from the ToC list
                $id = isset($block['element']['attributes']['id']) ? $block['element']['attributes']['id'] : $this->createAnchorID($text);
            } else {
                // Get the anchor of the heading to link from the ToC list
                $id = isset($block['element']['attributes']['id']) ? $block['element']['attributes']['id'] : null;
            }


            // Set attributes to head tags
            $block['element']['attributes']['id'] = $id;

            $headersAllowed = $this->options['headings']['allowed'] ?? ["h1", "h2", "h3", "h4", "h5", "h6"];

            // Check if level are defined as a heading
            if (in_array($level, $headersAllowed)) {

                // Add/stores the heading element info to the ToC list
                $this->setContentsList(array(
                    'text'  => $text,
                    'id'    => $id,
                    'level' => $level
                ));
            }
            return $block;
        }
    }

    protected function blockMarkup($line)
    {
        $state = isset($this->options['markup']) ? $this->options['markup'] : true;
        if ($state) {
            return DynamicParent::blockMarkup($line);
        }
    }

    protected function blockReference($line)
    {
        $state = isset($this->options['references']) ? $this->options['references'] : true;
        if ($state) {
            return DynamicParent::blockReference($line);
        }
    }

    protected function blockTable($line, $block = null)
    {
        $state = isset($this->options['tables']) ? $this->options['tables'] : true;
        if ($state) {
            return DynamicParent::blockTable($line, $block);
        }
    }


    protected function blockAbbreviation($line)
    {
        $allowCustomAbbr = isset($this->options['abbreviations']['allow_custom_abbr']) ? $this->options['abbreviations']['allow_custom_abbr'] : true;

        $state = isset($this->options['abbreviations']) ? $this->options['abbreviations'] : true;
        if ($state) {
            if (isset($this->options['abbreviations']['predefine'])) {
                foreach ($this->options['abbreviations']['predefine'] as $abbreviations => $description) {
                    $this->DefinitionData['Abbreviation'][$abbreviations] = $description;
                }
            }

            if ($allowCustomAbbr == true) {
                return DynamicParent::blockAbbreviation($line);
            } else {
                return;
            }
        }
    }

    protected function inlineText($text)
    {
        $Inline = array(
            'extent' => strlen($text),
            'element' => array(),
        );

        $Inline['element']['elements'] = self::pregReplaceElements(
            $this->breaksEnabled ? '/[ ]*+\n/' : '/(?:[ ]*+\\\\|[ ]{2,}+)\n/',
            array(
                array('name' => 'br'),
                array('text' => "\n"),
            ),
            $text
        );

        $Inline = DynamicParent::inlineText($text);

        return $Inline;
    }

    protected function blockFootnote($line)
    {
        $state = isset($this->options['footnotes']) ? $this->options['footnotes'] : true;
        if ($state) {
            return DynamicParent::blockFootnote($line);
        }
    }



    protected function blockDefinitionList($line, $block)
    {
        $state = isset($this->options['definition_lists']) ? $this->options['definition_lists'] : true;
        if ($state) {
            return DynamicParent::blockDefinitionList($line, $block);
        }
    }

    /**
     * ------------------------------------------------------------------------
     * Inline
     * ------------------------------------------------------------------------
     */

    // inlineCode
    protected function inlineCode($excerpt)
    {
        $state = isset($this->options['inline_code']) ? $this->options['inline_code'] : true;
        if ($state) {
            return DynamicParent::inlineCode($excerpt);
        }
    }

    protected function inlineEmailTag($excerpt)
    {
        $state = isset($this->options['auto_mark_emails']) ? $this->options['auto_mark_emails'] : true;
        if ($state) {
            return DynamicParent::inlineEmailTag($excerpt);
        }
    }

    protected function inlineEmphasis($excerpt)
    {
        $state = isset($this->options['emphasis']) ? $this->options['emphasis'] : true;
        if ($state) {
            return DynamicParent::inlineEmphasis($excerpt);
        }
    }

    protected function inlineImage($excerpt)
    {
        $state = isset($this->options['images']) ? $this->options['images'] : true;
        if ($state) {
            return DynamicParent::inlineImage($excerpt);
        }
    }

    protected function inlineLink($excerpt)
    {
        $state = isset($this->options['links']) ? $this->options['links'] : true;
        if ($state) {
            return DynamicParent::inlineLink($excerpt);
        }
    }

    protected function inlineMarkup($excerpt)
    {
        $state = isset($this->options['markup']) ? $this->options['markup'] : true;
        if ($state) {
            return DynamicParent::inlineMarkup($excerpt);
        }
    }

    protected function inlineStrikethrough($excerpt)
    {
        $state = isset($this->options['strikethroughs']) ? $this->options['strikethroughs'] : true;
        if ($state) {
            return DynamicParent::inlineStrikethrough($excerpt);
        }
    }

    protected function inlineUrl($excerpt)
    {
        $state = isset($this->options['links']) ? $this->options['links'] : true;
        if ($state) {
            return DynamicParent::inlineUrl($excerpt);
        }
    }

    protected function inlineUrlTag($excerpt)
    {
        $state = isset($this->options['links']) ? $this->options['links'] : true;
        if ($state) {
            return DynamicParent::inlineUrlTag($excerpt);
        }
    }

    /**
     * ------------------------------------------------------------------------
     * ParsedownExtended
     * ------------------------------------------------------------------------
     */

    protected function inlineEmojis($excerpt)
    {
        $emoji_map = [
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
            ':small_red_triangle_down:' =>  '🔻', ':black_small_square:' => '▪️', ':black_medium_small_square:' => '◾',':black_medium_square:' => '◼️',
            ':black_large_square:' => '⬛', ':white_small_square:' => '▫️', ':white_medium_small_square:' => '◽', ':white_medium_square:' => '◻️',
            ':white_large_square:' => '⬜'
        ];

        if (preg_match('/^(:)([^:]*?)(:)/', $excerpt['text'], $matches)) {
            return [
               'extent' => strlen($matches[0]),
               'element' => [
                   // Transliterate characters to ASCII
                   'text' => str_replace(array_keys($emoji_map), $emoji_map, $matches[0])
               ],
            ];
        }
    }

    /*
     * Inline Marks
     * -------------------------------------------------------------------------
     */

    protected function inlineMarks($excerpt)
    {
        if (preg_match('/^(==)([^=]*?)(==)/', $excerpt['text'], $matches)) {
            return [
               'extent' => strlen($matches[0]),
               'element' => [
                   'name' => 'mark',
                   'text' => $matches[2]
               ]
            ];
        }
    }

    /*
     * Inline Keystrokes
     * -------------------------------------------------------------------------
     */

    protected function inlineKeystrokes($excerpt)
    {
        if (preg_match('/^(?<!\[)(?:\[\[([^\[\]]*|[\[\]])\]\])(?!\])/s', $excerpt['text'], $matches)) {
            return [
               'extent' => strlen($matches[0]),
               'element' => [
                   'name' => 'kbd',
                   'text' => $matches[1],
               ]
            ];
        }
    }

    /*
     * Inline Superscript
     * -------------------------------------------------------------------------
     */

    protected function inlineSuperscript($excerpt)
    {
        if (preg_match('/(?:\^(?!\^)([^\^ ]*)\^(?!\^))/', $excerpt['text'], $matches)) {
            return [
             'extent' => strlen($matches[0]),
             'element' => [
                 'name' => 'sup',
                 'text' => $matches[1],
                 'function' => 'lineElements',
             ],
          ];
        }
    }

    /*
     * Inline Subscript
     * -------------------------------------------------------------------------
     */

    protected function inlineSubscript($excerpt)
    {
        if (preg_match('/(?:~(?!~)([^~ ]*)~(?!~))/', $excerpt['text'], $matches)) {
            return [
             'extent' => strlen($matches[0]),
             'element' => [
                 'name' => 'sub',
                 'text' => $matches[1],
                 'function' => 'lineElements',
             ],
          ];
        }
    }

    /*
     * Inline Smartypants
     * -------------------------------------------------------------------------
     */

    protected function inlineSmartypants($text)
    {
        $state = isset($this->options['smartypants']) ? $this->options['smartypants'] : false;
        if (!$state) {
            return $text;
        }

        $typographicReplace = array(
            '/(?<!\\\\)\(c\)/i' => '&copy;',
            '/(?<!\\\\)\(r\)/i' => '&reg;',
            '/(?<!\\\\)\(tm\)/i' => '&trade;',
            '/(?<!\\\\)(?<!\.)\.{3}(?!\.)/' => '&hellip;',
            '/(?<!\\\\)(?<!-)-{3}(?!-)/' => '&mdash;',
            '/(?<!\\\\)(?<!-)--\s(?!-)/' => '&ndash;',
            '/(?<!\\\\)(?<!<)<<(?!<)/' => '&laquo;',
            '/(?<!\\\\)(?<!>)>>(?!>)/' => '&raquo;',
        );
        return $this->pregReplaceAssoc($typographicReplace, $text);
    }

    /*
     * Inline Math
     * -------------------------------------------------------------------------
     */

    protected function inlineMath($excerpt)
    {
        $matchSingleDollar = $this->options['math']['single_dollar'] ?? false;
        // Inline Matches
        if ($matchSingleDollar) {
            // Match single dollar - experimental
            if (preg_match('/^(?<!\\\\)((?<!\$)\$(?!\$)(.*?)(?<!\$)\$(?!\$)|(?<!\\\\\()\\\\\((.*?)(?<!\\\\\()\\\\\)(?!\\\\\)))/s', $excerpt['text'], $matches)) {
                $mathMatch = $matches[0];
            }
        } else {
            if (preg_match('/^(?<!\\\\\()\\\\\((.*?)(?<!\\\\\()\\\\\)(?!\\\\\))/s', $excerpt['text'], $matches)) {
                $mathMatch = $matches[0];
            }
        }

        if (isset($mathMatch)) {
            return array(
               'extent' => strlen($mathMatch),
               'element' => array(
                   'text' => $mathMatch,
               ),
            );
        }
    }

    protected function inlineEscapeSequence($excerpt)
    {
        $element = array(
            'element' => array(
               'rawHtml' => $excerpt['text'][1],
            ),
            'extent' => 2,
        );

        $state = isset($this->options['math']) ? $this->options['math'] : false;

        if ($state) {
            if (isset($excerpt['text'][1]) && in_array($excerpt['text'][1], $this->specialCharacters) && !preg_match('/^(?<!\\\\)(?<!\\\\\()\\\\\((.{2,}?)(?<!\\\\\()\\\\\)(?!\\\\\))/s', $excerpt['text'])) {
                return $element;
            }
        } else {
            if (isset($excerpt['text'][1]) && in_array($excerpt['text'][1], $this->specialCharacters)) {
                return $element;
            }
        }
    }

    /**
     * ------------------------------------------------------------------------
     *  Blocks.
     * ------------------------------------------------------------------------
     */

    /*
     * Block Math
     * -------------------------------------------------------------------------
     */

    protected function blockMath($line)
    {
        $block = [
          'element' => [
             'text' => '',
          ],
      ];

        if (preg_match('/^(?<!\\\\)(\\\\\[)(?!.)$/', $line['text'])) {
            $block['end'] = '\]';
            return $block;
        } elseif (preg_match('/^(?<!\\\\)(\$\$)(?!.)$/', $line['text'])) {
            $block['end'] = '$$';
            return $block;
        }
    }

    // ~

    protected function blockMathContinue($line, $block)
    {
        if (isset($block['complete'])) {
            return;
        }

        if (isset($block['interrupted'])) {
            $block['element']['text'] .= str_repeat(
                "\n",
                $block['interrupted']
            );

            unset($block['interrupted']);
        }

        if (
          preg_match('/^(?<!\\\\)(\\\\\])$/', $line['text']) &&
          $block['end'] === '\]'
      ) {
            $block['complete'] = true;
            $block['math'] = true;
            $block['element']['text'] =
             "\\[" . $block['element']['text'] . "\\]";
            return $block;
        } elseif (
          preg_match('/^(?<!\\\\)(\$\$)$/', $line['text']) &&
          $block['end'] === '$$'
      ) {
            $block['complete'] = true;
            $block['math'] = true;
            $block['element']['text'] = "$$" . $block['element']['text'] . "$$";
            return $block;
        }

        $block['element']['text'] .= "\n" . $line['body'];

        // ~

        return $block;
    }

    // ~

    protected function blockMathComplete($block)
    {
        return $block;
    }

    /*
     * Block Fenced Code
     * -------------------------------------------------------------------------
     */

    protected function blockFencedCode($line)
    {
        $state = isset($this->options['code_blocks']) ? $this->options['code_blocks'] : true;
        if ($state === false) {
            return;
        }
        $block = DynamicParent::blockFencedCode($line);

        $marker = $line['text'][0];
        $openerLength = strspn($line['text'], $marker);
        $language = trim(
            preg_replace('/^`{3}([^\s]+)(.+)?/s', '$1', $line['text'])
        );


        $state = isset($this->options['diagrams']) ? $this->options['diagrams'] : true;
        if ($state) {

            // Mermaid.js https://mermaidjs.github.io
            if (strtolower($language) == 'mermaid') {
                $element = [
                   'text' => '',
                ];

                $block = [
                   'char' => $marker,
                   'openerLength' => $openerLength,
                   'element' => [
                       'element' => $element,
                       'name' => 'div',
                       'attributes' => [
                           'class' => 'mermaid',
                       ],
                   ],
                ];

                return $block;
            }

            // Chart.js https://www.chartjs.org/
            if (strtolower($language) == 'chart') {
                $element = [
                   'text' => '',
                ];

                $block = [
                   'char' => $marker,
                   'openerLength' => $openerLength,
                   'element' => [
                       'element' => $element,
                       'name' => 'canvas',
                       'attributes' => [
                           'class' => 'chartjs',
                       ],
                   ],
                ];

                return $block;
            }
        }

        return $block;
    }

    /*
    * Checkbox
    * -------------------------------------------------------------------------
    */
    protected function blockCheckbox($line)
    {
        $text = trim($line['text']);
        $begin_line = substr($text, 0, 4);
        if ('[ ] ' === $begin_line) {
            return [
               'handler' => 'checkboxUnchecked',
               'text' => substr(trim($text), 4),
            ];
        }

        if ('[x] ' === $begin_line) {
            return [
               'handler' => 'checkboxChecked',
               'text' => substr(trim($text), 4),
            ];
        }
    }
    protected function blockCheckboxContinue(array $block)
    {
        // This is here because Parsedown require it.
    }

    protected function blockCheckboxComplete(array $block)
    {
        $block['element'] = [
            'rawHtml' => $this->{$block['handler']}($block['text']),
            'allowRawHtmlInSafeMode' => true,
        ];

        return $block;
    }

    protected function checkboxUnchecked($text)
    {
        if ($this->markupEscaped || $this->safeMode) {
            $text = self::escape($text);
        }

        return '<input type="checkbox" disabled /> ' . $this->format($text);
    }

    protected function checkboxChecked($text)
    {
        if ($this->markupEscaped || $this->safeMode) {
            $text = self::escape($text);
        }

        return '<input type="checkbox" checked disabled /> ' . $this->format($text);
    }

    /**
     * Formats the checkbox label without double escaping.
     * @param string $text the string to format
     * @return string the formatted text
     */
    protected function format($text)
    {
        // backup settings
        $markup_escaped = $this->markupEscaped;
        $safe_mode = $this->safeMode;

        // disable rules to prevent double escaping.
        $this->setMarkupEscaped(false);
        $this->setSafeMode(false);

        // format line
        $text = $this->line($text);

        // reset old values
        $this->setMarkupEscaped($markup_escaped);
        $this->setSafeMode($safe_mode);

        return $text;
    }
    /**
    * ------------------------------------------------------------------------
    *  Helpers.
    * ------------------------------------------------------------------------
    */


    protected function linesElements(array $lines)
    {
        $elements = array();
        $CurrentBlock = null;

        foreach ($lines as $line) {
            if (chop($line) === '') {
                if (isset($CurrentBlock)) {
                    $CurrentBlock['interrupted'] = (
                        isset($CurrentBlock['interrupted'])
                        ? $CurrentBlock['interrupted'] + 1 : 1
                    );
                }

                continue;
            }

            while (($beforeTab = strstr($line, "\t", true)) !== false) {
                $shortage = 4 - mb_strlen($beforeTab, 'utf-8') % 4;

                $line = $beforeTab
                    . str_repeat(' ', $shortage)
                    . substr($line, strlen($beforeTab) + 1)
                ;
            }

            $indent = strspn($line, ' ');

            $text = $indent > 0 ? substr($line, $indent) : $line;

            # ~

            $line = array('body' => $line, 'indent' => $indent, 'text' => $text);

            # ~

            if (isset($CurrentBlock['continuable'])) {
                $methodName = 'block' . $CurrentBlock['type'] . 'Continue';
                $block = $this->$methodName($line, $CurrentBlock);

                if (isset($block)) {
                    $CurrentBlock = $block;

                    continue;
                } else {
                    if ($this->isBlockCompletable($CurrentBlock['type'])) {
                        $methodName = 'block' . $CurrentBlock['type'] . 'Complete';
                        $CurrentBlock = $this->$methodName($CurrentBlock);
                    }
                }
            }

            # ~

            $marker = $text[0];

            # ~

            $blockTypes = $this->unmarkedBlockTypes;

            if (isset($this->BlockTypes[$marker])) {
                foreach ($this->BlockTypes[$marker] as $blockType) {
                    $blockTypes []= $blockType;
                }
            }

            #
            # ~

            foreach ($blockTypes as $blockType) {
                $block = $this->{"block$blockType"}($line, $CurrentBlock);

                if (isset($block)) {
                    $block['type'] = $blockType;

                    if (! isset($block['identified'])) {
                        if (isset($CurrentBlock)) {
                            $elements[] = $this->extractElement($CurrentBlock);
                        }

                        $block['identified'] = true;
                    }

                    if ($this->isBlockContinuable($blockType)) {
                        $block['continuable'] = true;
                    }

                    $CurrentBlock = $block;

                    continue 2;
                }
            }

            # ~

            if (isset($CurrentBlock) and $CurrentBlock['type'] === 'Paragraph') {
                $block = $this->paragraphContinue($line, $CurrentBlock);
            }

            if (isset($block)) {
                $CurrentBlock = $block;
            } else {
                if (isset($CurrentBlock)) {
                    $elements[] = $this->extractElement($CurrentBlock);
                }

                if (!isset($block['math'])) {
                    $line = $this->inlineSmartypants($line);
                }

                $CurrentBlock = $this->paragraph($line);

                $CurrentBlock['identified'] = true;
            }
        }

        # ~

        if (isset($CurrentBlock['continuable']) and $this->isBlockCompletable($CurrentBlock['type'])) {
            $methodName = 'block' . $CurrentBlock['type'] . 'Complete';
            $CurrentBlock = $this->$methodName($CurrentBlock);
        }

        # ~

        if (isset($CurrentBlock)) {
            $elements[] = $this->extractElement($CurrentBlock);
        }

        # ~

        return $elements;
    }


    protected function blockTableContinue($Line, array $Block)
    {
        if (isset($Block['interrupted'])) {
            return;
        }

        if (count($Block['alignments']) === 1 or $Line['text'][0] === '|' or strpos($Line['text'], '|')) {
            $Elements = array();

            $row = $Line['text'];

            $row = trim($row);
            $row = trim($row, '|');

            preg_match_all('/(?:(\\\\[|])|[^|`]|`[^`]++`|`)++/', $row, $matches);

            $cells = array_slice($matches[0], 0, count($Block['alignments']));

            foreach ($cells as $index => $cell) {
                $cell = trim($cell);

                $cell = $this->inlineSmartypants($cell);

                $Element = array(
                    'name' => 'td',
                    'handler' => array(
                        'function' => 'lineElements',
                        'argument' => $cell,
                        'destination' => 'elements',
                    )
                );

                if (isset($Block['alignments'][$index])) {
                    $Element['attributes'] = array(
                        'style' => 'text-align: ' . $Block['alignments'][$index] . ';',
                    );
                }

                $Elements []= $Element;
            }

            $Element = array(
                'name' => 'tr',
                'elements' => $Elements,
            );

            $Block['element']['elements'][1]['elements'] []= $Element;

            return $Block;
        }
    }


    private function pregReplaceAssoc(array $replace, $subject)
    {
        return preg_replace(array_keys($replace), array_values($replace), $subject);
    }


    protected function parseAttributeData($attributeString)
    {
        $state = isset($this->options['special_attributes']) ? $this->options['special_attributes'] : true;
        if ($state) {
            return DynamicParent::parseAttributeData($attributeString);
        }

        return array();
    }


    /**
     * Parses the given markdown string to an HTML string but it leaves the ToC
     * tag as is. It's an alias of the parent method "\DynamicParent::text()".
     *
     * @param  string $text  Markdown string to be parsed.
     * @return string        Parsed HTML string.
     */
    public function body($text): string
    {
        $text = $this->encodeTagToHash($text);   // Escapes ToC tag temporary
        $html = DynamicParent::text($text);      // Parses the markdown text
        $html = $this->decodeTagFromHash($html); // Unescape the ToC tag
        return $html;
    }

    /**
     * Parses markdown string to HTML and also the "[toc]" tag as well.
     * It overrides the parent method: \Parsedown::text().
     *
     * @param  string $text
     * @return void
     */
    public function text($text)
    {
        // Parses the markdown text except the ToC tag. This also searches
        // the list of contents and available to get from "contentsList()"
        // method.
        $html = $this->body($text);

        if (isset($this->options['toc']) && $this->options['toc'] == false) {
            return $html;
        }

        $tag_origin  = $this->getTagToC();

        if (strpos($text, $tag_origin) === false) {
            return $html;
        }

        $toc_data = $this->contentsList();
        $toc_id   = $this->getIdAttributeToC();
        $needle  = '<p>' . $tag_origin . '</p>';
        $replace = "<div id=\"${toc_id}\">${toc_data}</div>";

        return str_replace($needle, $replace, $html);
    }

    /**
     * Sets the user defined ToC markdown tag.
     *
     * @param  string $tag
     * @return void
     */
    public function setTagToc($tag)
    {
        $tag = trim($tag);
        if (self::escape($tag) === $tag) {
            // Set ToC tag if it's safe
            $this->tag_toc = $tag;
        } else {
            // Do nothing but log
            error_log(
                'Malformed ToC user tag given.'
                . ' At: ' . __FUNCTION__ . '() '
                . ' in Line:' . __LINE__ . ' (Using default ToC tag)'
            );
        }
    }
    protected $tag_toc = '';

    /**
     * Encodes the ToC tag to a hashed tag and replace.
     *
     * This is used to avoid parsing user defined ToC tag which includes "_" in
     * their tag such as "[[_toc_]]". Unless it will be parsed as:
     *   "<p>[[<em>TOC</em>]]</p>"
     *
     * @param  string $text
     * @return string
     */
    protected function encodeTagToHash($text)
    {
        $salt = $this->getSalt();
        $tag_origin = $this->getTagToC();

        if (strpos($text, $tag_origin) === false) {
            return $text;
        }

        $tag_hashed = hash('sha256', $salt . $tag_origin);

        return str_replace($tag_origin, $tag_hashed, $text);
    }

    /**
     * Decodes the hashed ToC tag to an original tag and replaces.
     *
     * This is used to avoid parsing user defined ToC tag which includes "_" in
     * their tag such as "[[_toc_]]". Unless it will be parsed as:
     *   "<p>[[<em>TOC</em>]]</p>"
     *
     * @param  string $text
     * @return string
     */
    protected function decodeTagFromHash($text)
    {
        $salt = $this->getSalt();
        $tag_origin = $this->getTagToC();
        $tag_hashed = hash('sha256', $salt . $tag_origin);

        if (strpos($text, $tag_hashed) === false) {
            return $text;
        }

        return str_replace($tag_hashed, $tag_origin, $text);
    }

    /**
     * Unique string to use as a salt value.
     *
     * @return string
     */
    protected function getSalt()
    {
        static $salt;
        if (isset($salt)) {
            return $salt;
        }

        $salt = hash('md5', time());
        return $salt;
    }

    /**
     * Gets the markdown tag for ToC.
     *
     * @return string
     */
    protected function getTagToC()
    {
        if (isset($this->tag_toc) && ! empty($this->tag_toc)) {
            return $this->tag_toc;
        }

        return self::TAG_TOC_DEFAULT;
    }

    /**
     * Returns the parsed ToC.
     *
     * @param  string $type_return  Type of the return format. "html" or "json".
     * @return string               HTML/JSON string of ToC.
     */
    public function contentsList($type_return = 'html')
    {
        if ('html' === strtolower($type_return)) {
            $result = '';
            if (! empty($this->contentsListString)) {
                // Parses the ToC list in markdown to HTML
                $result = $this->body($this->contentsListString);
            }
            return $result;
        }

        if ('json' === strtolower($type_return)) {
            return json_encode($this->contentsListArray);
        }

        // Forces to return ToC as "html"
        error_log(
            'Unknown return type given while parsing ToC.'
            . ' At: ' . __FUNCTION__ . '() '
            . ' in Line:' . __LINE__ . ' (Using default type)'
        );
        return $this->contentsList('html');
    }

    /**
     * Gets the ID attribute of the ToC for HTML tags.
     *
     * @return string
     */
    protected function getIdAttributeToC()
    {
        if (isset($this->id_toc) && ! empty($this->id_toc)) {
            return $this->id_toc;
        }

        return self::ID_ATTRIBUTE_DEFAULT;
    }

    /**
     * Generates an anchor text that are link-able even if the heading is not in
     * ASCII.
     *
     * @param  string $text
     * @return string
     */
    protected function createAnchorID($str): string
    {
        // Make sure string is in UTF-8 and strip invalid UTF-8 characters
        $str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());

        $optionUrlEncode = isset($this->options['toc']['urlencode']) ? $this->options['toc']['urlencode'] : false;
        if ($optionUrlEncode) {
            // Check AnchorID is unique
            $str = $this->incrementAnchorId($str);

            return urlencode($str);
        }

        $char_map = array(
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
            '©' => '(c)','®' => '(r)','™' => '(tm)',

            // Greek
            'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
            'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
            'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
            'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
            'Ϋ' => 'Y',
            'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
            'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
            'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
            'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
            'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',

            // Turkish
            'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
            'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g',

            // Russian
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
            'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
            'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
            'Я' => 'Ya',
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
            'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
            'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
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
            'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z',
            'Ż' => 'Z',
            'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
            'ż' => 'z',

            // Latvian
            'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N',
            'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
            'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
            'š' => 's', 'ū' => 'u', 'ž' => 'z'
        );

        // Transliterate characters to ASCII
        $optionTransliterate = isset($this->options['toc']['transliterate']) ? $this->options['toc']['transliterate'] : false;
        if ($optionTransliterate) {
            $str = str_replace(array_keys($char_map), $char_map, $str);
        }

        // Replace non-alphanumeric characters with our delimiter
        $optionDelimiter = $this->options['toc']['delimiter'] ?? '-';
        $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $optionDelimiter, $str);

        // Remove duplicate delimiters
        $str = preg_replace('/(' . preg_quote($optionDelimiter, '/') . '){2,}/', '$1', $str);

        // Truncate slug to max. characters
        $optionLimit = $this->options['toc']['limit'] ?? mb_strlen($str, 'UTF-8');
        $str = mb_substr($str, 0, $optionLimit, 'UTF-8');

        // Remove delimiter from ends
        $str = trim($str, $optionDelimiter);

        $urlLowercase = $this->options['toc']['lowercase'] ?? true;
        $str = $urlLowercase ? mb_strtolower($str, 'UTF-8') : $str;

        $str = $this->incrementAnchorId($str);

        return $str;
    }

    /**
     * Get only the text from a markdown string.
     * It parses to HTML once then trims the tags to get the text.
     *
     * @param  string $text  Markdown text.
     * @return string
     */
    protected function fetchText($text)
    {
        return trim(strip_tags($this->line($text)));
    }

    /**
     * Set/stores the heading block to ToC list in a string and array format.
     *
     * @param  array $Content   Heading info such as "level","id" and "text".
     * @return void
     */
    protected function setContentsList(array $Content)
    {
        // Stores as an array
        $this->setContentsListAsArray($Content);
        // Stores as string in markdown list format.
        $this->setContentsListAsString($Content);
    }

    /**
     * Sets/stores the heading block info as an array.
     *
     * @param  array $Content
     * @return void
     */
    protected function setContentsListAsArray(array $Content)
    {
        $this->contentsListArray[] = $Content;
    }

    protected $contentsListArray = array();

    /**
     * Sets/stores the heading block info as a list in markdown format.
     *
     * @param  array $Content  Heading info such as "level","id" and "text".
     * @return void
     */
    protected function setContentsListAsString(array $Content)
    {
        $text  = $this->fetchText($Content['text']);
        $id    = $Content['id'];
        $level = (int) trim($Content['level'], 'h');
        $link  = "[${text}](#${id})";

        if ($this->firstHeadLevel === 0) {
            $this->firstHeadLevel = $level;
        }
        $cutIndent = $this->firstHeadLevel - 1;
        if ($cutIndent > $level) {
            $level = 1;
        } else {
            $level = $level - $cutIndent;
        }

        $indent = str_repeat('  ', $level);

        // Stores in markdown list format as below:
        // - [Header1](#Header1)
        //   - [Header2-1](#Header2-1)
        //     - [Header3](#Header3)
        //   - [Header2-2](#Header2-2)
        // ...
        $this->contentsListString .= "${indent}- ${link}" . PHP_EOL;
    }
    protected $contentsListString = '';
    protected $firstHeadLevel = 0;

    /**
     * Collect and count anchors in use to prevent duplicated ids. Return string
     * with incremental, numeric suffix. Also init optional blacklist of ids.
     *
     * @param  string $str
     * @return string
     */
    protected function incrementAnchorId($str)
    {

        // add blacklist to list of used anchors
        if (!$this->isBlacklistInitialized) {
            $this->initBlacklist();
        }

        $this->anchorDuplicates[$str] = !isset($this->anchorDuplicates[$str]) ? 0 : ++$this->anchorDuplicates[$str];

        $newStr = $str;

        if ($count = $this->anchorDuplicates[$str]) {
            $newStr .= "-{$count}";

            // increment until conversion doesn't produce new duplicates anymore
            if (isset($this->anchorDuplicates[$newStr])) {
                $newStr = $this->incrementAnchorId($str);
            } else {
                $this->anchorDuplicates[$newStr] = 0;
            }
        }

        return $newStr;
    }

    protected $isBlacklistInitialized = false;
    protected $anchorDuplicates = [];

    /**
     * Add blacklisted ids to anchor list
     */
    protected function initBlacklist()
    {
        if ($this->isBlacklistInitialized) {
            return;
        }

        if (!empty($this->options['headings']['blacklist']) && is_array($this->options['headings']['blacklist'])) {
            foreach ($this->options['headings']['blacklist'] as $v) {
                if (is_string($v)) {
                    $this->anchorDuplicates[$v] = 0;
                }
            }
        }

        $this->isBlacklistInitialized = true;
    }
}
