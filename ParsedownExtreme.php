<?php
asdasdasdasdsadasd
class ParsedownExtreme extends ParsedownExtra
{
    const VERSION = '0.1.0-Alpha';

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
    }

    #
    # Setters
    #
    protected $enableKatex = false;

    public function enableKatex(bool $enableKatex = true)
    {
        $this->enableKatex = (bool) $enableKatex;

        return $this;
    }

    protected $enableMermaid = false;

    public function enableMermaid(bool $enableMermaid = true)
    {
        $this->enableMermaid = (bool) $enableMermaid;

        return $this;
    }

    protected $enableTypography = false;

    public function typography(bool $enableTypography = true)
    {
        $this->enableTypography = (bool) $enableTypography;

        return $this;
    }

    protected $superMode = false;
    protected $subMode = false;


    public function superscript(bool $mode = true)
    {
        $this->superMode = (bool) $mode;
        $this->subMode = (bool) $mode;

        return $this;
    }

    protected $markMode = true;

    public function setMarkMode($markMode)
    {
        $this->markMode = (bool) $markMode;

        return $this;
    }

    protected $insertMode = true;

    public function setInsertMode($insertMode)
    {
        $this->insertMode = (bool) $insertMode;

        return $this;
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

                if ($this->enableTypography) {
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

    #
    # Superscript
    # --------------------------------------------------------------------------

    protected function inlineSuperText($excerpt)
    {
        if (!$this->superMode) {
            return;
        }

        if (preg_match('/(\^(?!\^)([^\^ ]*)\^(?!\^))/', $excerpt['text'], $matches)) {
            return array(

                // How many characters to advance the Parsedown's
                // cursor after being done processing this tag.
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'sup',
                    'text' => $matches[1]
                ),

            );
        }
    }

    #
    # Subscript
    # --------------------------------------------------------------------------

    protected function inlineSubText($excerpt)
    {
        if (!$this->subMode) {
            return;
        }

        if (preg_match('/(~(?!~)([^~ ]*)~(?!~))/', $excerpt['text'], $matches)) {
            return array(

                // How many characters to advance the Parsedown's
                // cursor after being done processing this tag.
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'sub',
                    'text' => $matches[1]
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
        if (!$this->enableKatex) {
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
        if (!$this->enableKatex) {
            return;
        }

        if (isset($Block['complete'])) {
            return;
        }

        if (isset($Block['interrupted'])) {
            $Block['element']['element']['text'] .= str_repeat("\n", $Block['interrupted']);

            unset($Block['interrupted']);
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
        if (!$this->enableMermaid) {
            return;
        }

        $marker = $Line['text'][0];

        $openerLength = strspn($Line['text'], $marker);

        if ($openerLength < 2) {
            return;
        }

        $infostring = trim(substr($Line['text'], $openerLength), "\t ");

        if (strpos($infostring, '%') !== false) {
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
        if (!$this->enableKatex) {
            return;
        }

        if (isset($Block['complete'])) {
            return;
        }

        if (isset($Block['interrupted'])) {
            $Block['element']['element']['text'] .= str_repeat("\n", $Block['interrupted']);

            unset($Block['interrupted']);
        }

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
    // -------------------------------------------------------------------------
    // -------------------------------------------------------------------------

    #
    # - [x] AND - [ ]
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

    #
    # ReplaceAssoc
    protected function strReplaceAssoc(array $replace, $subject)
    {
        return str_replace(array_keys($replace), array_values($replace), $subject);
    }
}
