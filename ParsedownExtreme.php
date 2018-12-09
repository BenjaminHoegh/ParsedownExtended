<?php

class ParsedownExtreme extends ParsedownExtra
{
    const VERSION = '0.1.3-Alpha';

    public function __construct()
    {
        parent::__construct();


        if (version_compare(parent::version, '0.8.0-beta-1') < 0) {
            throw new Exception('ParsedownExtreme requires a later version of Parsedown');
        }

        $this->BlockTypes['$'][] = 'Katex';
        $this->BlockTypes['%'][] = 'Mermaid';

        $this->InlineTypes['='][] = 'MarkText';
        $this->inlineMarkerList .= '=';

        $this->InlineTypes['+'][] = 'InsertText';
        $this->inlineMarkerList .= '+';

        $this->InlineTypes['^'][] = 'SuperText';
        $this->inlineMarkerList .= '^';

        $this->InlineTypes['~'][] = 'SubText';
        $this->inlineMarkerList .= '~';

        $this->InlineTypes['['][] = 'Embeding';
        $this->inlineMarkerList .= '[';
    }


    #
    # Setters
    #
    protected $katexMode = false;

    public function katex(bool $mode = true)
    {
        $this->katexMode = $mode;

        return $this;
    }

    protected $mermaidMode = false;

    public function mermaid(bool $mode = true)
    {
        $this->mermaidMode = $mode;

        return $this;
    }

    protected $typographyMode = false;

    public function typography(bool $mode = true)
    {
        $this->typographyMode = $mode;

        return $this;
    }

    protected $superMode = false;


    public function superscript(bool $mode = true)
    {
        $this->superMode = $mode;

        return $this;
    }

    protected $markMode = true;

    public function mark(bool $mode = true)
    {
        $this->markMode = $mode;

        return $this;
    }

    protected $insertMode = true;

    public function insert(bool $insertMode = true)
    {
        $this->insertMode = $insertMode;

        return $this;
    }

    protected $embedingMode = true;

    public function embeding(bool $embedingMode = true)
    {
        $this->embedingMode = $embedingMode;

        return $this;
    }

    #
    # Header

    protected function blockHeader($Line)
    {
        $Block = parent::blockHeader($Line);

        if (preg_match('/[ #]*{('.$this->regexAttribute.'+)}[ ]*$/', $Block['element']['handler']['argument'], $matches, PREG_OFFSET_CAPTURE)) {
            $attributeString = $matches[1][0];

            $Block['element']['attributes'] = $this->parseAttributeData($attributeString);

            $Block['element']['handler']['argument'] = substr($Block['element']['handler']['argument'], 0, $matches[0][1]);
        }

        if (!isset($Block['element']['attributes']['id']) && isset($Block['element']['handler']['argument'])) {
            $Block['element']['attributes']['id'] = preg_replace('/\s+/', '-', $this->hyphenize($Block['element']['handler']['argument']));
        }

        $link = "#".$Block['element']['attributes']['id'];

        $Block['element']['handler']['argument'] = $Block['element']['handler']['argument']."<a class='heading-link' href='{$link}'><i class='fal fa-link'></i></a>";

        return $Block;
    }


    #
    # Setext

    protected function blockSetextHeader($Line, array $Block = null)
    {
        $Block = parent::blockSetextHeader($Line, $Block);

        if (preg_match('/[ ]*{('.$this->regexAttribute.'+)}[ ]*$/', $Block['element']['handler']['argument'], $matches, PREG_OFFSET_CAPTURE)) {
            $attributeString = $matches[1][0];

            $Block['element']['attributes'] = $this->parseAttributeData($attributeString);

            $Block['element']['handler']['argument'] = substr($Block['element']['handler']['argument'], 0, $matches[0][1]);
        }

        if (!isset($Block['element']['attributes']['id']) && isset($Block['element']['handler']['argument'])) {
            $Block['element']['attributes']['id'] = preg_replace('/\s+/', '-', $this->hyphenize($Block['element']['handler']['argument']));
        }

        return $Block;
    }


    private function hyphenize($string)
    {
        $dict = array(
            "I'm"      => "I am",
            "thier"    => "their",
            // Add your own replacements here
        );
        return strtolower(
            preg_replace(
              array( '#[\\s-]+#', '#[^A-Za-z0-9\. -]+#' ),
              array( '-', '' ),
              // the full cleanString() can be downloaded from http://www.unexpectedit.com/php/php-clean-string-of-utf8-chars-convert-to-similar-ascii-char
              $this->cleanString(
                  str_replace( // preg_replace can be used to support more complicated replacements
                      array_keys($dict),
                      array_values($dict),
                      urldecode($string)
                  )
              )
            )
        );
    }

    private function cleanString($text)
    {
        $utf8 = array(
            '/[áàâãªä]/u'   =>   'a',
            '/[ÁÀÂÃÄ]/u'    =>   'A',
            '/[ÍÌÎÏ]/u'     =>   'I',
            '/[íìîï]/u'     =>   'i',
            '/[éèêë]/u'     =>   'e',
            '/[ÉÈÊË]/u'     =>   'E',
            '/[óòôõºö]/u'   =>   'o',
            '/[ÓÒÔÕÖ]/u'    =>   'O',
            '/[úùûü]/u'     =>   'u',
            '/[ÚÙÛÜ]/u'     =>   'U',
            '/ç/'           =>   'c',
            '/Ç/'           =>   'C',
            '/ñ/'           =>   'n',
            '/Ñ/'           =>   'N',
            '/–/'           =>   '-', // UTF-8 hyphen to "normal" hyphen
            '/[’‘‹›‚]/u'    =>   ' ', // Literally a single quote
            '/[“”«»„]/u'    =>   ' ', // Double quote
            '/ /'           =>   ' ', // nonbreaking space (equiv. to 0x160)
        );
        return preg_replace(array_keys($utf8), array_values($utf8), $text);
    }


    #
    # Typography Replacer
    # --------------------------------------------------------------------------

    protected function linesElements(array $lines)
    {
        $Elements = array();
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

                $line = $beforeTab.str_repeat(' ', $shortage).substr($line, strlen($beforeTab) + 1);
            }

            $indent = strspn($line, ' ');

            $text = $indent > 0 ? substr($line, $indent) : $line;

            # ~

            $Line = array('body' => $line, 'indent' => $indent, 'text' => $text);

            # ~

            if (isset($CurrentBlock['continuable'])) {
                $methodName = 'block' . $CurrentBlock['type'] . 'Continue';
                $Block = $this->$methodName($Line, $CurrentBlock);

                if (isset($Block)) {
                    $CurrentBlock = $Block;

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
                $Block = $this->{"block$blockType"}($Line, $CurrentBlock);

                if (isset($Block)) {
                    $Block['type'] = $blockType;

                    if (! isset($Block['identified'])) {
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

            # ~
            if (isset($CurrentBlock) and $CurrentBlock['type'] === 'Paragraph') {
                $Block = $this->paragraphContinue($Line, $CurrentBlock);
            }

            if (isset($Block)) {
                $CurrentBlock = $Block;
            } else {
                if (isset($CurrentBlock)) {
                    $Elements[] = $this->extractElement($CurrentBlock);
                }

                if ($this->typographyMode) {
                    $typographicReplace = array(
                        '(c)' => '&copy;',
                        '(C)' => '&copy;',
                        '(r)' => '&reg;',
                        '(R)' => '&reg;',
                        '(tm)' => '&trade;',
                        '(TM)' => '&trade;'
                    );
                    $Line = $this->strReplaceAssoc($typographicReplace, $Line);
                }

                $CurrentBlock = $this->paragraph($Line);

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
            $Elements[] = $this->extractElement($CurrentBlock);
        }

        # ~

        return $Elements;
    }

    #
    # Mark
    # --------------------------------------------------------------------------

    protected function inlineMarkText($excerpt)
    {
        if (!$this->markMode) {
            return;
        }

        if (preg_match('/^(==)([\s\S]*?)(==)/', $excerpt['text'], $matches)) {
            return array(
                // How many characters to advance the Parsedown's
                // cursor after being done processing this tag.
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'mark',
                    'text' => $matches[2]
                ),
            );
        }
    }


    //
    // Embeding
    protected function inlineEmbeding($excerpt)
    {
        if (!$this->embedingMode) {
            return;
        }

        if (preg_match('/\[video.*src="([^"]*)".*\]/', $excerpt['text'], $matches)) {
            $url = $matches[1];
            $type = '';

            $needles = array('youtube','vimeo','dailymotion');
            foreach ($needles as $needle) {
                if (strpos($url, $needle) !== false) {
                    $type = $needle;
                }
            }

            switch ($type) {
                case 'youtube':
                    $element = 'iframe';
                    $attributes = array(
                        'src' => preg_replace('/.*\?v=([^\&\]]*).*/', 'https://www.youtube.com/embed/$1', $url),
                        'frameborder' => '0',
                        'allow' => 'autoplay',
                        'allowfullscreen' => '',
                        'sandbox' => 'allow-same-origin allow-scripts allow-forms'
                    );
                    $regxr = '';
                    break;
                case 'vimeo':
                    $element = 'iframe';
                    $attributes = array(
                        'src' => preg_replace('/(?:https?:\/\/(?:[\w]{3}\.|player\.)*vimeo\.com(?:[\/\w:]*(?:\/videos)?)?\/([0-9]+)[^\s]*)/', 'https://player.vimeo.com/video/$1', $url),
                        'frameborder' => '0',
                        'allow' => 'autoplay',
                        'allowfullscreen' => '',
                        'sandbox' => 'allow-same-origin allow-scripts allow-forms'
                    );
                    $regxr = '';
                    break;
                case 'dailymotion':
                    $element = 'iframe';
                    $attributes = array(
                        'src' => $url,
                        'frameborder' => '0',
                        'allow' => 'autoplay',
                        'allowfullscreen' => '',
                        'sandbox' => 'allow-same-origin allow-scripts allow-forms'
                    );
                    $regxr = '';
                    break;
                default:
                    $element = 'video';
            }

            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => $element,
                    'text' => $matches[1],
                    'attributes' => $attributes
                ),
            );
        }



        // NOTE:
        //
        // [codepen_embed height="179" theme_id="dark" slug_hash="LBPJGV" default_tab="js,result" user="GeorgePark" preview="true" data-preview="true" data-editable="true"]See the Pen <a href='https://codepen.io/GeorgePark/pen/LBPJGV/'>Responsive Video Knockout Text (Pure CSS)</a> by George W. Park (<a href='https://codepen.io/GeorgePark'>@GeorgePark</a>) on <a href='https://codepen.io'>CodePen</a>.[/codepen_embed]
    }

    #
    # Superscript
    # --------------------------------------------------------------------------

    protected function inlineSuperText($excerpt)
    {
        if (!$this->superMode) {
            return;
        }

        if (preg_match('/(?:\^(?!\^)([^\^ ]*)\^(?!\^))/', $excerpt['text'], $matches)) {
            return array(

                // How many characters to advance the Parsedown's
                // cursor after being done processing this tag.
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'sup',
                    'text' => $matches[1],
                    'function' => 'lineElements'
                ),

            );
        }
    }

    #
    # Subscript
    # --------------------------------------------------------------------------

    protected function inlineSubText($excerpt)
    {
        if (!$this->superMode) {
            return;
        }

        if (preg_match('/(?:~(?!~)([^~ ]*)~(?!~))/', $excerpt['text'], $matches)) {
            return array(

                // How many characters to advance the Parsedown's
                // cursor after being done processing this tag.
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'sub',
                    'text' => $matches[1],
                    'function' => 'lineElements'
                ),

            );
        }
    }

    #
    # Insert
    # --------------------------------------------------------------------------

    protected function inlineInsertText($excerpt)
    {
        if (!$this->insertMode) {
            return;
        }

        if (preg_match('/^(\+\+)([\s\S]*?)(\+\+)/', $excerpt['text'], $matches)) {
            return array(

                // How many characters to advance the Parsedown's
                // cursor after being done processing this tag.
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'ins',
                    'text' => $matches[2]
                ),

            );
        }
    }



    #
    # Block Katex
    # --------------------------------------------------------------------------

    protected function blockKatex($Line)
    {
        if (!$this->katexMode) {
            return;
        }

        $marker = $Line['text'][0];

        $openerLength = strspn($Line['text'], $marker);

        if ($openerLength < 2) {
            return;
        }

        $infostring = trim(substr($Line['text'], $openerLength), "\t ");

        if (strpos($infostring, '$') !== false) {
            return;
        }

        $Element = array(
            'text' => ''
        );

        $Block = array(
            'char' => $marker,
            'openerLength' => $openerLength,
            'element' => array(
                'element' => $Element
            )
        );

        return $Block;
    }

    protected function blockKatexContinue($Line, $Block)
    {
        if (!$this->katexMode) {
            return;
        }

        if (isset($Block['complete'])) {
            return;
        }

        // A blank newline has occurred.
        if (isset($block['interrupted'])) {
            $block['element']['text'] .= "\n";
            unset($block['interrupted']);
        }

        if (($len = strspn($Line['text'], $Block['char'])) >= $Block['openerLength'] and chop(substr($Line['text'], $len), ' ') === '') {
            $Block['element']['element']['text'] = "$$" . substr($Block['element']['element']['text'] . "$$", 1);

            $Block['complete'] = true;

            return $Block;
        }

        $Block['element']['element']['text'] .= "\n" . $Line['body'];

        return $Block;
    }

    protected function blockKatexComplete($block)
    {
        return $block;
    }


    #
    # Block Mermaid
    # --------------------------------------------------------------------------
    protected function blockMermaid($Line)
    {
        if (!$this->mermaidMode) {
            return;
        }

        $marker = $Line['text'][0];

        $openerLength = strspn($Line['text'], $marker);

        if ($openerLength < 2) {
            return;
        }

        $this->infostring = strtolower(trim(substr($Line['text'], $openerLength), "\t "));

        if (strpos($this->infostring, '%') !== false) {
            return;
        }


        $Element = array(
            'text' => ''
        );

        $Block = array(
            'char' => $marker,
            'openerLength' => $openerLength,
            'element' => array(
                'element' => $Element,
                'name' => 'div',
                'attributes' => array(
                    'class' => 'mermaid'
                ),
            )
        );

        return $Block;
    }

    protected function blockMermaidContinue($Line, $Block)
    {
        //if ($this->infostring == 'mermaid') {
        if (!$this->mermaidMode) {
            return;
        }

        if (isset($Block['complete'])) {
            return;
        }

        // A blank newline has occurred.
        if (isset($block['interrupted'])) {
            $block['element']['text'] .= "\n";
            unset($block['interrupted']);
        }

        // Check for end of the block.
        if (($len = strspn($Line['text'], $Block['char'])) >= $Block['openerLength'] and chop(substr($Line['text'], $len), ' ') === '') {
            $Block['element']['element']['text'] = substr($Block['element']['element']['text'], 1);

            $Block['complete'] = true;

            return $Block;
        }

        $Block['element']['element']['text'] .= "\n" . $Line['body'];

        return $Block;
    }

    protected function blockMermaidComplete($block)
    {
        return $block;
    }


    #
    # List with support for checkbox
    # --------------------------------------------------------------------------

    protected function blockList($Line, array $CurrentBlock = null)
    {
        list($name, $pattern) = $Line['text'][0] <= '-' ? array('ul', '[*+-]') : array('ol', '[0-9]{1,9}+[.\)]');

        if (preg_match('/^('.$pattern.'([ ]++|$))(.*+)/', $Line['text'], $matches)) {
            $contentIndent = strlen($matches[2]);

            if ($contentIndent >= 5) {
                $contentIndent -= 1;
                $matches[1] = substr($matches[1], 0, -$contentIndent);
                $matches[3] = str_repeat(' ', $contentIndent) . $matches[3];
            } elseif ($contentIndent === 0) {
                $matches[1] .= ' ';
            }

            $markerWithoutWhitespace = strstr($matches[1], ' ', true);

            $Block = array(
                'indent' => $Line['indent'],
                'pattern' => $pattern,
                'data' => array(
                    'type' => $name,
                    'marker' => $matches[1],
                    'markerType' => ($name === 'ul' ? $markerWithoutWhitespace : substr($markerWithoutWhitespace, -1)),
                ),
                'element' => array(
                    'name' => $name,
                    'elements' => array(),
                ),
            );
            $Block['data']['markerTypeRegex'] = preg_quote($Block['data']['markerType'], '/');

            if ($name === 'ol') {
                $listStart = ltrim(strstr($matches[1], $Block['data']['markerType'], true), '0') ?: '0';

                if ($listStart !== '1') {
                    if (
                        isset($CurrentBlock)
                        and $CurrentBlock['type'] === 'Paragraph'
                        and ! isset($CurrentBlock['interrupted'])
                    ) {
                        return;
                    }

                    $Block['element']['attributes'] = array('start' => $listStart);
                }
            }

            $this->checkbox($matches[3], $attributes);

            $Block['li'] = array(
                'name' => 'li',
                'handler' => array(
                    'function' => 'li',
                    'argument' => !empty($matches[3]) ? array($matches[3]) : array(),
                    'destination' => 'elements'
                )
            );

            $attributes && $Block['li']['attributes'] = $attributes;

            $Block['element']['elements'] []= & $Block['li'];

            return $Block;
        }
    }



    protected function blockListContinue($Line, array $Block)
    {
        if (isset($Block['interrupted']) and empty($Block['li']['handler']['argument'])) {
            return null;
        }

        $requiredIndent = ($Block['indent'] + strlen($Block['data']['marker']));

        if ($Line['indent'] < $requiredIndent
            and (
                (
                    $Block['data']['type'] === 'ol'
                    and preg_match('/^[0-9]++'.$Block['data']['markerTypeRegex'].'(?:[ ]++(.*)|$)/', $Line['text'], $matches)
                ) or (
                    $Block['data']['type'] === 'ul'
                    and preg_match('/^'.$Block['data']['markerTypeRegex'].'(?:[ ]++(.*)|$)/', $Line['text'], $matches)
                )
            )
        ) {
            if (isset($Block['interrupted'])) {
                $Block['li']['handler']['argument'] []= '';

                $Block['loose'] = true;

                unset($Block['interrupted']);
            }

            unset($Block['li']);

            $text = isset($matches[1]) ? $matches[1] : '';

            $this->checkbox($text, $attributes);


            $Block['indent'] = $Line['indent'];

            $Block['li'] = array(
                'name' => 'li',
                'handler' => array(
                    'function' => 'li',
                    'argument' => array($text),
                    'destination' => 'elements'
                )
            );
            $attributes && $Block['li']['attributes'] = $attributes;
            $Block['element']['elements'] []= & $Block['li'];

            return $Block;
        } elseif ($Line['indent'] < $requiredIndent and $this->blockList($Line)) {
            return null;
        }

        if ($Line['text'][0] === '[' and $this->blockReference($Line)) {
            return $Block;
        }

        if ($Line['indent'] >= $requiredIndent) {
            if (isset($Block['interrupted'])) {
                $Block['li']['handler']['argument'] []= '';

                $Block['loose'] = true;

                unset($Block['interrupted']);
            }

            $text = substr($Line['body'], $requiredIndent);

            $Block['li']['handler']['argument'] []= $text;

            return $Block;
        }

        if (! isset($Block['interrupted'])) {
            $text = preg_replace('/^[ ]{0,'.$requiredIndent.'}+/', '', $Line['body']);

            $Block['li']['handler']['argument'] []= $text;

            return $Block;
        }
    }

    protected function blockListComplete(array $Block)
    {
        if (isset($Block['loose'])) {
            foreach ($Block['element']['elements'] as &$li) {
                if (end($li['handler']['argument']) !== '') {
                    $li['handler']['argument'] []= '';
                }
            }
        }

        return $Block;
    }





    // -------------------------------------------------------------------------
    // -----------------------      Expermentels      --------------------------
    // -------------------------------------------------------------------------




    // -------------------------------------------------------------------------
    // -------------------------------------------------------------------------
    // -------------------------------------------------------------------------

    // Checkbox
    protected function checkbox(&$text, &$attributes)
    {
        if (strpos($text, '[x]') !== false || strpos($text, '[ ]') !== false) {
            $attributes = array("style" => "list-style: none;");
            $text = str_replace(array('[x]', '[ ]'), array(
                '<input type="checkbox" checked="true" disabled="true">',
                '<input type="checkbox" disabled="true">',
            ), $text);
        }
    }

    // ReplaceAssoc
    protected function strReplaceAssoc(array $replace, $subject)
    {
        return str_replace(array_keys($replace), array_values($replace), $subject);
    }


    // TODO: Make use of user location
    protected function remove_accents($string)
    {
        if (!preg_match('/[\x80-\xff]/', $string)) {
            return $string;
        }

        if ($this->seems_utf8($string)) {
            $chars = array(
            // Decompositions for Latin-1 Supplement
            'ª' => 'a', 'º' => 'o',
            'À' => 'A', 'Á' => 'A',
            'Â' => 'A', 'Ã' => 'A',
            'Ä' => 'Ae', 'Å' => 'Aa',
            'Æ' => 'AE','Ç' => 'C',
            'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E',
            'Ì' => 'I', 'Í' => 'I',
            'Î' => 'I', 'Ï' => 'I',
            'Ð' => 'D', 'Ñ' => 'N',
            'Ò' => 'O', 'Ó' => 'O',
            'Ô' => 'O', 'Õ' => 'O',
            'Ö' => 'Oe', 'Ù' => 'U',
            'Ú' => 'U', 'Û' => 'U',
            'Ü' => 'Ue', 'Ý' => 'Y',
            'Þ' => 'TH','ß' => 'ss',
            'à' => 'a', 'á' => 'a',
            'â' => 'a', 'ã' => 'a',
            'ä' => 'ae', 'å' => 'aa',
            'æ' => 'ae','ç' => 'c',
            'è' => 'e', 'é' => 'e',
            'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i',
            'î' => 'i', 'ï' => 'i',
            'ð' => 'd', 'ñ' => 'n',
            'ò' => 'o', 'ó' => 'o',
            'ô' => 'o', 'õ' => 'o',
            'ö' => 'oe', 'ø' => 'oe',
            'ù' => 'u', 'ú' => 'u',
            'û' => 'u', 'ü' => 'ue',
            'ý' => 'y', 'þ' => 'th',
            'ÿ' => 'y', 'Ø' => 'Oe',
            // Decompositions for Latin Extended-A
            'Ā' => 'A', 'ā' => 'a',
            'Ă' => 'A', 'ă' => 'a',
            'Ą' => 'A', 'ą' => 'a',
            'Ć' => 'C', 'ć' => 'c',
            'Ĉ' => 'C', 'ĉ' => 'c',
            'Ċ' => 'C', 'ċ' => 'c',
            'Č' => 'C', 'č' => 'c',
            'Ď' => 'D', 'ď' => 'd',
            'Đ' => 'DJ', 'đ' => 'dj',
            'Ē' => 'E', 'ē' => 'e',
            'Ĕ' => 'E', 'ĕ' => 'e',
            'Ė' => 'E', 'ė' => 'e',
            'Ę' => 'E', 'ę' => 'e',
            'Ě' => 'E', 'ě' => 'e',
            'Ĝ' => 'G', 'ĝ' => 'g',
            'Ğ' => 'G', 'ğ' => 'g',
            'Ġ' => 'G', 'ġ' => 'g',
            'Ģ' => 'G', 'ģ' => 'g',
            'Ĥ' => 'H', 'ĥ' => 'h',
            'Ħ' => 'H', 'ħ' => 'h',
            'Ĩ' => 'I', 'ĩ' => 'i',
            'Ī' => 'I', 'ī' => 'i',
            'Ĭ' => 'I', 'ĭ' => 'i',
            'Į' => 'I', 'į' => 'i',
            'İ' => 'I', 'ı' => 'i',
            'Ĳ' => 'IJ','ĳ' => 'ij',
            'Ĵ' => 'J', 'ĵ' => 'j',
            'Ķ' => 'K', 'ķ' => 'k',
            'ĸ' => 'k', 'Ĺ' => 'L',
            'ĺ' => 'l', 'Ļ' => 'L',
            'ļ' => 'l', 'Ľ' => 'L',
            'ľ' => 'l', 'Ŀ' => 'L',
            'ŀ' => 'l', 'Ł' => 'L',
            'ł' => 'l', 'Ń' => 'N',
            'ń' => 'n', 'Ņ' => 'N',
            'ņ' => 'n', 'Ň' => 'N',
            'ň' => 'n', 'ŉ' => 'n',
            'Ŋ' => 'N', 'ŋ' => 'n',
            'Ō' => 'O', 'ō' => 'o',
            'Ŏ' => 'O', 'ŏ' => 'o',
            'Ő' => 'O', 'ő' => 'o',
            'Œ' => 'OE','œ' => 'oe',
            'Ŕ' => 'R','ŕ' => 'r',
            'Ŗ' => 'R','ŗ' => 'r',
            'Ř' => 'R','ř' => 'r',
            'Ś' => 'S','ś' => 's',
            'Ŝ' => 'S','ŝ' => 's',
            'Ş' => 'S','ş' => 's',
            'Š' => 'S', 'š' => 's',
            'Ţ' => 'T', 'ţ' => 't',
            'Ť' => 'T', 'ť' => 't',
            'Ŧ' => 'T', 'ŧ' => 't',
            'Ũ' => 'U', 'ũ' => 'u',
            'Ū' => 'U', 'ū' => 'u',
            'Ŭ' => 'U', 'ŭ' => 'u',
            'Ů' => 'U', 'ů' => 'u',
            'Ű' => 'U', 'ű' => 'u',
            'Ų' => 'U', 'ų' => 'u',
            'Ŵ' => 'W', 'ŵ' => 'w',
            'Ŷ' => 'Y', 'ŷ' => 'y',
            'Ÿ' => 'Y', 'Ź' => 'Z',
            'ź' => 'z', 'Ż' => 'Z',
            'ż' => 'z', 'Ž' => 'Z',
            'ž' => 'z', 'ſ' => 's',
            // Decompositions for Latin Extended-B
            'Ș' => 'S', 'ș' => 's',
            'Ț' => 'T', 'ț' => 't',
            // Euro Sign
            '€' => 'E',
            // GBP (Pound) Sign
            '£' => '',
            // Vowels with diacritic (Vietnamese)
            // unmarked
            'Ơ' => 'O', 'ơ' => 'o',
            'Ư' => 'U', 'ư' => 'u',
            // grave accent
            'Ầ' => 'A', 'ầ' => 'a',
            'Ằ' => 'A', 'ằ' => 'a',
            'Ề' => 'E', 'ề' => 'e',
            'Ồ' => 'O', 'ồ' => 'o',
            'Ờ' => 'O', 'ờ' => 'o',
            'Ừ' => 'U', 'ừ' => 'u',
            'Ỳ' => 'Y', 'ỳ' => 'y',
            // hook
            'Ả' => 'A', 'ả' => 'a',
            'Ẩ' => 'A', 'ẩ' => 'a',
            'Ẳ' => 'A', 'ẳ' => 'a',
            'Ẻ' => 'E', 'ẻ' => 'e',
            'Ể' => 'E', 'ể' => 'e',
            'Ỉ' => 'I', 'ỉ' => 'i',
            'Ỏ' => 'O', 'ỏ' => 'o',
            'Ổ' => 'O', 'ổ' => 'o',
            'Ở' => 'O', 'ở' => 'o',
            'Ủ' => 'U', 'ủ' => 'u',
            'Ử' => 'U', 'ử' => 'u',
            'Ỷ' => 'Y', 'ỷ' => 'y',
            // tilde
            'Ẫ' => 'A', 'ẫ' => 'a',
            'Ẵ' => 'A', 'ẵ' => 'a',
            'Ẽ' => 'E', 'ẽ' => 'e',
            'Ễ' => 'E', 'ễ' => 'e',
            'Ỗ' => 'O', 'ỗ' => 'o',
            'Ỡ' => 'O', 'ỡ' => 'o',
            'Ữ' => 'U', 'ữ' => 'u',
            'Ỹ' => 'Y', 'ỹ' => 'y',
            // acute accent
            'Ấ' => 'A', 'ấ' => 'a',
            'Ắ' => 'A', 'ắ' => 'a',
            'Ế' => 'E', 'ế' => 'e',
            'Ố' => 'O', 'ố' => 'o',
            'Ớ' => 'O', 'ớ' => 'o',
            'Ứ' => 'U', 'ứ' => 'u',
            // dot below
            'Ạ' => 'A', 'ạ' => 'a',
            'Ậ' => 'A', 'ậ' => 'a',
            'Ặ' => 'A', 'ặ' => 'a',
            'Ẹ' => 'E', 'ẹ' => 'e',
            'Ệ' => 'E', 'ệ' => 'e',
            'Ị' => 'I', 'ị' => 'i',
            'Ọ' => 'O', 'ọ' => 'o',
            'Ộ' => 'O', 'ộ' => 'o',
            'Ợ' => 'O', 'ợ' => 'o',
            'Ụ' => 'U', 'ụ' => 'u',
            'Ự' => 'U', 'ự' => 'u',
            'Ỵ' => 'Y', 'ỵ' => 'y',
            // Vowels with diacritic (Chinese, Hanyu Pinyin)
            'ɑ' => 'a',
            // macron
            'Ǖ' => 'U', 'ǖ' => 'u',
            // acute accent
            'Ǘ' => 'U', 'ǘ' => 'u',
            // caron
            'Ǎ' => 'A', 'ǎ' => 'a',
            'Ǐ' => 'I', 'ǐ' => 'i',
            'Ǒ' => 'O', 'ǒ' => 'o',
            'Ǔ' => 'U', 'ǔ' => 'u',
            'Ǚ' => 'U', 'ǚ' => 'u',
            // grave accent
            'Ǜ' => 'U', 'ǜ' => 'u',
            );

            $string = strtr($string, $chars);
        } else {
            $chars = array();
            // Assume ISO-8859-1 if not UTF-8
            $chars['in'] = "\x80\x83\x8a\x8e\x9a\x9e"
                ."\x9f\xa2\xa5\xb5\xc0\xc1\xc2"
                ."\xc3\xc4\xc5\xc7\xc8\xc9\xca"
                ."\xcb\xcc\xcd\xce\xcf\xd1\xd2"
                ."\xd3\xd4\xd5\xd6\xd8\xd9\xda"
                ."\xdb\xdc\xdd\xe0\xe1\xe2\xe3"
                ."\xe4\xe5\xe7\xe8\xe9\xea\xeb"
                ."\xec\xed\xee\xef\xf1\xf2\xf3"
                ."\xf4\xf5\xf6\xf8\xf9\xfa\xfb"
                ."\xfc\xfd\xff";

            $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

            $string = strtr($string, $chars['in'], $chars['out']);
            $double_chars = array();
            $double_chars['in'] = array("\x8c", "\x9c", "\xc6", "\xd0", "\xde", "\xdf", "\xe6", "\xf0", "\xfe");
            $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
            $string = str_replace($double_chars['in'], $double_chars['out'], $string);
        }

        return $string;
    }




    // -------------------------------------------------------------------------

    protected function seems_utf8($str)
    {
        $this->mbstring_binary_safe_encoding();
        $length = strlen($str);
        $this->reset_mbstring_encoding();
        for ($i=0; $i < $length; $i++) {
            $c = ord($str[$i]);
            if ($c < 0x80) {
                $n = 0;
            } // 0bbbbbbb
            elseif (($c & 0xE0) == 0xC0) {
                $n=1;
            } // 110bbbbb
            elseif (($c & 0xF0) == 0xE0) {
                $n=2;
            } // 1110bbbb
            elseif (($c & 0xF8) == 0xF0) {
                $n=3;
            } // 11110bbb
            elseif (($c & 0xFC) == 0xF8) {
                $n=4;
            } // 111110bb
            elseif (($c & 0xFE) == 0xFC) {
                $n=5;
            } // 1111110b
            else {
                return false;
            } // Does not match any model
            for ($j=0; $j<$n; $j++) { // n bytes matching 10bbbbbb follow ?
                if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80)) {
                    return false;
                }
            }
        }
        return true;
    }

    protected function reset_mbstring_encoding()
    {
        $this->mbstring_binary_safe_encoding(true);
    }

    protected function mbstring_binary_safe_encoding($reset = false)
    {
        static $encodings = array();
        static $overloaded = null;

        if (is_null($overloaded)) {
            $overloaded = function_exists('mb_internal_encoding') && (ini_get('mbstring.func_overload') & 2);
        }

        if (false === $overloaded) {
            return;
        }

        if (! $reset) {
            $encoding = mb_internal_encoding();
            array_push($encodings, $encoding);
            mb_internal_encoding('ISO-8859-1');
        }

        if ($reset && $encodings) {
            $encoding = array_pop($encodings);
            mb_internal_encoding($encoding);
        }
    }
}
