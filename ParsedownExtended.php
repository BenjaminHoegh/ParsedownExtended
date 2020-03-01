<?php

if (class_exists('ParsedownExtra')) {
    class DynamicParent extends ParsedownExtra
    {
        public function __construct()
        {
            if (version_compare(parent::version, '0.8.0-beta-1') < 0) {
                throw new Exception('ParsedownExtended requires a later version of ParsedownExtra');
            }
            parent::__construct();
        }
    }
} else {
    class DynamicParent extends Parsedown
    {
        public function __construct()
        {
            if (version_compare(parent::version, '1.8.0-beta-6') < 0) {
                throw new Exception('ParsedownExtended requires a later version of Parsedown');
            }
        }
    }
}


class ParsedownExtended extends DynamicParent
{
    const VERSION = '1.0-beta-4';

    public function __construct(array $configurations = null)
    {
        $this->configurationsHandler($configurations);

        parent::__construct();

        // Blocks
        $this->BlockTypes['\\'][] = 'Math';
        $this->BlockTypes['$'][] = 'Math';
        $this->BlockTypes['['][] = 'Toc';

        // Inline

        $this->InlineTypes['\\'][] = 'Math';
        $this->inlineMarkerList .= '\\';

        $this->InlineTypes['='][] = 'MarkText';
        $this->inlineMarkerList .= '=';

        $this->InlineTypes['+'][] = 'InsertText';
        $this->inlineMarkerList .= '+';

        $this->InlineTypes['^'][] = 'SuperText';
        $this->inlineMarkerList .= '^';

        $this->InlineTypes['~'][] = 'SubText';

        $this->InlineTypes['['][] = 'Kbd';
        $this->inlineMarkerList .= '[';
    }

    // Default Settings
    private $config = [
        "math" => false,
        "diagrams" => false,
        "kbd" => false,
        "mark" => false,
        "insert" => false,
        "task" => false,
        "scripts" => false,
        "smarttypography" => false,
        "toc" => [
            "enable" => false,
        ]
    ];


    private function configurationsHandler($configurations)
    {
        if (empty($configurations)) {
            return;
        }

        if (is_array($configurations)) {
            $configurations = array_change_key_case($configurations, CASE_LOWER);

            // Math
            if (isset($configurations['math'])) {
                if (is_array($configurations['math'])) {
                    throw new Exception("Selector must be a boolean");
                }
                $this->config['math'] = $configurations['math'];
            }

            // Diagrams
            if (isset($configurations['diagrams'])) {
                if (is_array($configurations['diagrams'])) {
                    throw new Exception("Selector must be a boolean");
                }
                $this->config['diagrams'] = $configurations['diagrams'];
            }

            // kbd
            if (isset($configurations['kbd'])) {
                if (is_array($configurations['kbd'])) {
                    throw new Exception("Selector must be a boolean");
                }
                $this->config['kbd'] = $configurations['kbd'];
            }

            // Mark
            if (isset($configurations['mark'])) {
                if (is_array($configurations['mark'])) {
                    throw new Exception("Selector must be a boolean");
                }
                $this->config['mark'] = $configurations['mark'];
            }

            // insert
            if (isset($configurations['insert'])) {
                if (is_array($configurations['insert'])) {
                    throw new Exception("Selector must be a boolean");
                }
                $this->config['insert'] = $configurations['insert'];
            }

            // superscript and suberscript
            if (isset($configurations['scripts'])) {
                if (is_array($configurations['scripts'])) {
                    throw new Exception("Selector must be a boolean");
                }
                $this->config['scripts'] = $configurations['scripts'];
            }

            // smartTypography
            if (isset($configurations['smarttypography'])) {
                if (is_array($configurations['smarttypography'])) {
                    throw new Exception("Selector must be a boolean");
                }
                $this->config['smarttypography'] = $configurations['smarttypography'];
            }

            // Task
            if (isset($configurations['task'])) {
                if (is_array($configurations['task'])) {
                    throw new Exception("Selector must be a boolean");
                }
                $this->config['task'] = $configurations['task'];
            }

            // TOC
            if (isset($configurations['toc'])) {
                if (!is_array($configurations['toc'])) {
                    throw new Exception("Selector must be a array");
                }
                $this->config['toc'] = $configurations['toc'];
            }
        }

        // echo "<pre>";
        // print_r($this->config);
        // echo "</pre>";
    }

    // -------------------------------------------------------------------------
    // -----------------------    Need to be first    --------------------------
    // -------------------------------------------------------------------------


    private $fullDocument;

    protected function textElements($text)
    {
        // make sure no definitions are set
        $this->DefinitionData = array();

        // standardize line breaks
        $text = str_replace(array("\r\n", "\r"), "\n", $text);

        // remove surrounding line breaks
        $text = trim($text, "\n");

        // Save a copy of the document
        $this->fullDocument = $text;

        $cleanDoc = preg_replace('/(?>!\`)<!--(.|\s)*?-->/', '', $text);

        // split text into lines
        $lines = explode("\n", $cleanDoc);
        // iterate through lines to identify blocks
        return $this->linesElements($lines);
    }



    // -------------------------------------------------------------------------
    // -----------------------         Inline         --------------------------
    // -------------------------------------------------------------------------

    //
    // Typography Replacer
    // -------------------------------------------------------------------------

    protected function linesElements(array $Lines)
    {
        $Elements = array();
        $CurrentBlock = null;

        foreach ($Lines as $Line) {
            if (chop($Line) === '') {
                if (isset($CurrentBlock)) {
                    $CurrentBlock['interrupted'] = (
                        isset($CurrentBlock['interrupted'])
                        ? $CurrentBlock['interrupted'] + 1 : 1
                    );
                }

                continue;
            }

            while (($beforeTab = strstr($Line, "\t", true)) !== false) {
                $shortage = 4 - mb_strlen($beforeTab, 'utf-8') % 4;

                $Line = $beforeTab.str_repeat(' ', $shortage).substr($Line, strlen($beforeTab) + 1);
            }

            $indent = strspn($Line, ' ');

            $text = $indent > 0 ? substr($Line, $indent) : $Line;

            // ~

            $Line = array('body' => $Line, 'indent' => $indent, 'text' => $text);

            // ~

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

            // ~

            $marker = $text[0];

            // ~

            $BlockTypes = $this->unmarkedBlockTypes;

            if (isset($this->BlockTypes[$marker])) {
                foreach ($this->BlockTypes[$marker] as $BlockType) {
                    $BlockTypes []= $BlockType;
                }
            }

            // ~

            foreach ($BlockTypes as $BlockType) {
                $Block = $this->{"block$BlockType"}($Line, $CurrentBlock);

                if (isset($Block)) {
                    $Block['type'] = $BlockType;

                    if (! isset($Block['identified'])) {
                        if (isset($CurrentBlock)) {
                            $Elements[] = $this->extractElement($CurrentBlock);
                        }

                        $Block['identified'] = true;
                    }

                    if ($this->isBlockContinuable($BlockType)) {
                        $Block['continuable'] = true;
                    }

                    $CurrentBlock = $Block;

                    continue 2;
                }
            }

            // ~
            if (isset($CurrentBlock) && $CurrentBlock['type'] === 'Paragraph') {
                $Block = $this->paragraphContinue($Line, $CurrentBlock);
            }

            if (isset($Block)) {
                $CurrentBlock = $Block;
            } else {
                if (isset($CurrentBlock)) {
                    $Elements[] = $this->extractElement($CurrentBlock);
                }

                if ($this->config['smarttypography'] && $Block['math'] != true) {
                    $Line = $this->smartTypographyReplace($Line);
                }

                $CurrentBlock = $this->paragraph($Line);

                $CurrentBlock['identified'] = true;
            }
        }

        // ~

        if (isset($CurrentBlock['continuable']) && $this->isBlockCompletable($CurrentBlock['type'])) {
            $methodName = 'block' . $CurrentBlock['type'] . 'Complete';
            $CurrentBlock = $this->$methodName($CurrentBlock);
        }

        // ~

        if (isset($CurrentBlock)) {
            $Elements[] = $this->extractElement($CurrentBlock);
        }

        // ~

        return $Elements;
    }


    //
    // Inline Mark
    // -------------------------------------------------------------------------

    protected function inlineMarkText($Excerpt)
    {
        if (!$this->config['mark']) {
            return;
        }

        if (preg_match('/^(==)([^=]*?)(==)/', $Excerpt['text'], $matches)) {
            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'mark',
                    'text' => $matches[2]
                ),
            );
        }
    }


    //
    // Inline Math
    // -------------------------------------------------------------------------

    protected function inlineMath($Excerpt)
    {
        if (!$this->config['math']) {
            return;
        }

        if (preg_match('/^(?<!\\\\)(?<!\\\\\()\\\\\((.*?)(?<!\\\\\()\\\\\)(?!\\\\\))/s', $Excerpt['text'], $matches)) {
            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'text' =>  $matches[0]
                ),
            );
        }
    }

    protected $specialCharacters = array(
        '\\', '`', '*', '_', '{', '}', '[', ']', '(', ')', '<', '>', '#', '+', '-', '.', '!', '|', '~', '^', '='
    );


    //
    // Inline Escape
    // -------------------------------------------------------------------------

    protected function inlineEscapeSequence($Excerpt)
    {
        $Element = array(
            'element' => array(
                'rawHtml' => $Excerpt['text'][1],
            ),
            'extent' => 2,
        );

        if ($this->config['math']) {
            if (isset($Excerpt['text'][1]) && in_array($Excerpt['text'][1], $this->specialCharacters) && !preg_match('/(?<!\\\\)((?<!\\\\\()\\\\\((?!\\\\\())(.*?)(?<!\\\\)(?<!\\\\\()((?<!\\\\\))\\\\\)(?!\\\\\)))(?!\\\\\()/s', $Excerpt['text'])) {
                return $Element;
            }
        } else {
            if (isset($Excerpt['text'][1]) && in_array($Excerpt['text'][1], $this->specialCharacters)) {
                return $Element;
            }
        }
    }



    //
    // Inline Superscript
    // -------------------------------------------------------------------------

    protected function inlineSuperText($Excerpt)
    {
        if (!$this->config['scripts']) {
            return;
        }

        if (preg_match('/(?:\^(?!\^)([^\^ ]*)\^(?!\^))/', $Excerpt['text'], $matches)) {
            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'sup',
                    'text' => $matches[1],
                    'function' => 'lineElements'
                ),

            );
        }
    }


    //
    // Inline Subscript
    // -------------------------------------------------------------------------

    protected function inlineSubText($Excerpt)
    {
        if (!$this->config['scripts']) {
            return;
        }

        if (preg_match('/(?:~(?!~)([^~ ]*)~(?!~))/', $Excerpt['text'], $matches)) {
            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'sub',
                    'text' => $matches[1],
                    'function' => 'lineElements'
                ),

            );
        }
    }


    //
    // Inline Strikethrough
    // -------------------------------------------------------------------------

    protected function inlineStrikethrough($Excerpt)
    {
        if (!isset($Excerpt['text'][1])) {
            return;
        }

        if ($Excerpt['text'][1] === '~' && preg_match('/^~~(?=\S)(.+?)(?<=\S)~~/', $Excerpt['text'], $matches)) {
            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 's',
                    'handler' => array(
                        'function' => 'lineElements',
                        'argument' => $matches[1],
                        'destination' => 'elements',
                    )
                ),
            );
        }
    }


    //
    // Inline Insert
    // -------------------------------------------------------------------------

    protected function inlineInsertText($Excerpt)
    {
        if (!$this->config['insert']) {
            return;
        }

        if (preg_match('/^(\+\+)([^+]*?)(\+\+)/', $Excerpt['text'], $matches)) {
            return array(

                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'ins',
                    'text' => $matches[2]
                ),
            );
        }
    }


    //
    // Inline KBD
    // -------------------------------------------------------------------------

    protected function inlineKbd($Excerpt)
    {
        if (!$this->config['kbd']) {
            return;
        }

        if (preg_match('/^(?<!\[)(?:\[\[([^\[\]]*|[\[\]])\]\])(?!\])/s', $Excerpt['text'], $matches)) {
            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'kbd',
                    'text' => $matches[1]
                ),

            );
        }
    }





    // -------------------------------------------------------------------------
    // -----------------------         Blocks         --------------------------
    // -------------------------------------------------------------------------



    //
    // Header
    // -------------------------------------------------------------------------

    protected function blockHeader($Line)
    {
        $Block = parent::blockHeader($Line);
        if (preg_match('/[ #]*{('.$this->regexAttribute.'+)}[ ]*$/', $Block['element']['handler']['argument'], $matches, PREG_OFFSET_CAPTURE)) {
            $attributeString = $matches[1][0];
            $Block['element']['attributes'] = $this->parseAttributeData($attributeString);
            $Block['element']['handler']['argument'] = substr($Block['element']['handler']['argument'], 0, $matches[0][1]);
        }

        // createAnchorID
        if (!isset($Block['element']['attributes']['id']) && isset($Block['element']['handler']['argument'])) {
            $Block['element']['attributes']['id'] = $this->createAnchorID($Block['element']['handler']['argument'], ['transliterate' => false]);
        }

        $link = "#".$Block['element']['attributes']['id'];

        $Block['element']['handler']['argument'] = $Block['element']['handler']['argument']."<a class='heading-link' href='{$link}'> <i class='fas fa-link'></i></a>";

        // ~

        return $Block;
    }

    //
    // Setext
    protected function blockSetextHeader($Line, array $Block = null)
    {
        $Block = parent::blockSetextHeader($Line, $Block);

        if (preg_match('/[ ]*{('.$this->regexAttribute.'+)}[ ]*$/', $Block['element']['handler']['argument'], $matches, PREG_OFFSET_CAPTURE)) {
            $attributeString = $matches[1][0];
            $Block['element']['attributes'] = $this->parseAttributeData($attributeString);
            $Block['element']['handler']['argument'] = substr($Block['element']['handler']['argument'], 0, $matches[0][1]);
        }

        // createAnchorID
        if (!isset($Block['element']['attributes']['id']) && isset($Block['element']['handler']['argument'])) {
            $Block['element']['attributes']['id'] = $this->createAnchorID($Block['element']['handler']['argument'], ['transliterate' => false]);
        }

        if ($Block['type'] == 'Paragraph') {
            $link = "#".$Block['element']['attributes']['id'];
            $Block['element']['handler']['argument'] = $Block['element']['handler']['argument']."<a class='heading-link' href='{$link}'> <i class='fas fa-link'></i></a>";
        }


        // ~

        return $Block;
    }


    //
    // Toc
    // -------------------------------------------------------------------------

    public function toc($input = null)
    {
        if (!$this->config['toc']['enable']) {
            return;
        }

        $Line['text'] = '[toc]';
        $Line['toc']['type'] = 'string';

        if (is_string($input)) {
            $this->fullDocument = $input;
        } else {
            throw new Exception("Unexpected parameter type");
        }

        return $this->blockToc($Line, null, false);
    }

    // ~

    protected $contentsListString;
    protected $contentsListArray = array();
    protected $firstHeadLevel = 0;

    // ~

    protected function blockToc(array $Line, array $Block = null, $isInline = true)
    {
        if (!$this->config['toc']['enable']) {
            return;
        }

        if ($Line['text'] == '[toc]') {
            if (isset($this->config['toc']['inline']) && $this->config['toc']['inline'] == false && $isInline == true) {
                return;
            }

            $selectorList = isset($this->config['toc']['selectors']) ? $this->config['toc']['selectors'] : ['h1','h2','h3','h4','h5','h6'];

            // Check if $Line[toc][type] already is defined
            if (!isset($Line['toc']['type'])) {
                $Line['toc']['type'] = 'array';
            }

            foreach ($selectorList as $selector) {
                $selectors[] = (integer) trim($selector, 'h');
            }

            $cleanDoc = preg_replace('/<!--(.|\s)*?-->/', '', $this->fullDocument);
            $headerLines = array();
            $prevLine = '';

            // split text into lines
            $lines = explode("\n", $cleanDoc);

            foreach ($lines as $headerLine) {
                if (strspn($headerLine, '#') > 0 || strspn($headerLine, '=') >= 3 || strspn($headerLine, '-') >= 3) {
                    $level = strspn($headerLine, '#');

                    // Setext headers
                    if (strspn($headerLine, '=') >= 3 && $prevLine !== '') {
                        $level = 1;
                        $headerLine = $prevLine;
                    } elseif (strspn($headerLine, '-') >= 3 && $prevLine !== '') {
                        $level = 2;
                        $headerLine = $prevLine;
                    }

                    if (in_array($level, $selectors) && $level > 0 && $level <= 6) {
                        $text = preg_replace('/[ #]*{('.$this->regexAttribute.'+)}[ ]*$/', '', $headerLine);
                        $text = trim(trim($text, '#'));

                        // createAnchorID
                        $id = $this->createAnchorID($text, ['transliterate' => false]);

                        if (preg_match('/{('.$this->regexAttribute.'+)}$/', $headerLine, $matches)) {
                            if (strspn($matches[1], '#') > 0) {
                                $id = trim($matches[1], '#');
                            }
                        }

                        // ~

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

                        // ~

                        if ($Line['toc']['type'] == 'string') {
                            $this->contentsListString .= "$indent- [${text}](#${id})\n";
                        } else {
                            $this->contentsListArray[] = "$indent- [${text}](#${id})\n";
                        }
                    }
                }
                $prevLine = $headerLine;
            }

            if ($Line['toc']['type'] == 'string') {
                return $this->text($this->contentsListString);
            }

            // ~

            $Block = array(

                'element' => array(
                    'name' => 'nav',
                    'attributes' => array(
                        'id'   => 'table-of-contents',
                    ),
                    'elements' => array(
                        '1' => array(
                            "handler" => array(
                                "function" => "li",
                                "argument" => $this->contentsListArray,
                                "destination" => "elements",
                            ),
                        ),
                    ),
                ),
            );

            // ~

            return $Block;
        }
    }


    //
    // Tables
    // -------------------------------------------------------------------------

    protected function blockTableContinue($Line, array $Block)
    {
        if (isset($Block['interrupted'])) {
            return;
        }

        if (count($Block['alignments']) === 1 || $Line['text'][0] === '|' || strpos($Line['text'], '|')) {
            $Elements = array();

            $row = $Line['text'];

            $row = trim($row);
            $row = trim($row, '|');


            preg_match_all('/(?:(\\\\[|])|[^|`]|`[^`]++`|`)++/', $row, $matches);

            $cells = array_slice($matches[0], 0, count($Block['alignments']));

            foreach ($cells as $index => $cell) {
                $cell = trim($cell);

                if ($this->config['smarttypography']) {
                    $cellContent = $this->smartTypographyReplace($cell);
                }

                $Element = array(
                    'name' => 'td',
                    'handler' => array(
                        'function' => 'lineElements',
                        'argument' => $cellContent ?? $cell,
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

            // ~

            return $Block;
        }
    }


    //
    // Quote

    protected function blockQuote($Line)
    {
        if (preg_match('/^>(?!>)[ ]?+(.*+)/', $Line['text'], $matches)) {
            $Block = array(
                'element' => array(
                    'name' => 'blockquote',
                    'handler' => array(
                        'function' => 'linesElements',
                        'argument' => (array) $matches[1],
                        'destination' => 'elements',
                    )
                ),
            );

            // ~

            return $Block;
        }
    }

    // ~

    protected function blockQuoteContinue($Line, array $Block)
    {
        if (isset($Block['interrupted'])) {
            return;
        }

        // ~

        if ($Line['text'][0] === '>' && preg_match('/^>(?!>)[ ]?+(.*+)/', $Line['text'], $matches)) {
            $Block['element']['handler']['argument'] []= $matches[1];

            return $Block;
        }

        // ~

        if (! isset($Block['interrupted'])) {
            $Block['element']['handler']['argument'] []= $Line['text'];

            return $Block;
        }
    }

    //
    // Block Fenced Code
    // --------------------------------------------------------------------------

    protected function blockFencedCode($Line)
    {
        $marker = $Line['text'][0];

        $openerLength = strspn($Line['text'], $marker);

        if ($openerLength < 3) {
            return;
        }

        $language = trim(preg_replace('/^`{3}([^\s]+)(.+)?/s', '$1', $Line['text']));
        $infostring = trim(preg_replace('/^`{3}([^\s]+)(.+)?/s', '$2', $Line['text']));

        if (strpos($infostring, '`') !== false) {
            return;
        }

        if ($this->config['diagrams']) {

            // Mermaid.js https://mermaidjs.github.io
            if (strtolower($language) == 'mermaid') {
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



            // Chart.js https://www.chartjs.org/
            if (strtolower($language) == 'chart') {
                $Element = array(
                    'text' => ''
                );

                $Block = array(
                    'char' => $marker,
                    'openerLength' => $openerLength,
                    'element' => array(
                        'element' => $Element,
                        'name' => 'canvas',
                        'attributes' => array(
                            'class' => 'chartjs'
                        ),
                    )
                );

                return $Block;
            }
        }

        $Element = array(
            'name' => 'code',
            'text' => '',
        );

        if ($language !== '') {
            $Element['attributes'] = array('class' => "language-$language");
        }

        $Block = array(
            'char' => $marker,
            'openerLength' => $openerLength,
            'element' => array(
                'name' => 'pre',
                'element' => $Element,
            ),
        );

        $attr = trim($infostring, '{}');
        $Block['element']['attributes'] = $this->parseAttributeData($attr);

        // ~

        return $Block;
    }


    //
    // Block Math
    // --------------------------------------------------------------------------

    protected function blockMath($Line)
    {
        if (!$this->config['math']) {
            return;
        }

        $Block = array(
            'element' => array(
                'text' => '',
            ),
        );

        if (preg_match('/^(?<!\\\\)(\\\\\[)(?!.)$/', $Line['text'])) {
            $Block['end'] = '\]';
            return $Block;
        } elseif (preg_match('/^(?<!\\\\)(\$\$)(?!.)$/', $Line['text'])) {
            $Block['end'] = '$$';
            return $Block;
        }
    }

    // ~

    protected function blockMathContinue($Line, $Block)
    {
        if (isset($Block['complete'])) {
            return;
        }

        if (isset($Block['interrupted'])) {
            $Block['element']['text'] .= str_repeat("\n", $Block['interrupted']);

            unset($Block['interrupted']);
        }

        if (preg_match('/^(?<!\\\\)(\\\\\])$/', $Line['text']) && $Block['end'] === '\]') {
            $Block['complete'] = true;
            $Block['math'] = true;
            $Block['element']['text'] = "\\[".$Block['element']['text']."\\]";
            return $Block;
        } elseif (preg_match('/^(?<!\\\\)(\$\$)$/', $Line['text']) && $Block['end'] === '$$') {
            $Block['complete'] = true;
            $Block['math'] = true;
            $Block['element']['text'] = "$$".$Block['element']['text']."$$";
            return $Block;
        }


        $Block['element']['text'] .= "\n" . $Line['body'];

        // ~

        return $Block;
    }

    // ~

    protected function blockMathComplete($Block)
    {
        return $Block;
    }

    //
    // Block Checkbox
    // --------------------------------------------------------------------------

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
                        && $CurrentBlock['type'] === 'Paragraph'
                        && ! isset($CurrentBlock['interrupted'])
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

    // ~

    protected function blockListContinue($Line, array $Block)
    {
        if (isset($Block['interrupted']) && empty($Block['li']['handler']['argument'])) {
            return null;
        }

        $requiredIndent = ($Block['indent'] + strlen($Block['data']['marker']));

        if ($Line['indent'] < $requiredIndent
            && (
                (
                    $Block['data']['type'] === 'ol'
                    && preg_match('/^[0-9]++'.$Block['data']['markerTypeRegex'].'(?:[ ]++(.*)|$)/', $Line['text'], $matches)
                ) or (
                    $Block['data']['type'] === 'ul'
                    && preg_match('/^'.$Block['data']['markerTypeRegex'].'(?:[ ]++(.*)|$)/', $Line['text'], $matches)
                )
            )
        ) {
            if (isset($Block['interrupted'])) {
                $Block['li']['handler']['argument'] []= '';

                $Block['loose'] = true;

                unset($Block['interrupted']);
            }

            unset($Block['li']);

            $text = $matches[1] ?? '';

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
        } elseif ($Line['indent'] < $requiredIndent && $this->blockList($Line)) {
            return null;
        }

        if ($Line['text'][0] === '[' && $this->blockReference($Line)) {
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

            // ~

            return $Block;
        }

        if (! isset($Block['interrupted'])) {
            $text = preg_replace('/^[ ]{0,'.$requiredIndent.'}+/', '', $Line['body']);

            $Block['li']['handler']['argument'] []= $text;

            // ~

            return $Block;
        }
    }

    // ~

    protected function blockListComplete(array $Block)
    {
        if (isset($Block['loose'])) {
            foreach ($Block['element']['elements'] as &$li) {
                if (end($li['handler']['argument']) !== '') {
                    $li['handler']['argument'] []= '';
                }
            }
        }

        // ~

        return $Block;
    }


    // -------------------------------------------------------------------------
    // -----------------------         Helpers        --------------------------
    // -------------------------------------------------------------------------

    public function text($text)
    {
        $Elements = $this->textElements($text);

        // convert to markup
        $markup = $this->elements($Elements);

        // trim line breaks
        $markup = trim($markup, "\n");

        // merge consecutive dl elements

        $markup = preg_replace('/<\/dl>\s+<dl>\s+/', '', $markup);

        // add footnotes

        if (isset($this->DefinitionData['Footnote'])) {
            $Element = $this->buildFootnoteElement();

            $markup .= "\n" . $this->element($Element);
        }

        // ~

        return $markup;
    }


    private function smartTypographyReplace($Text)
    {
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
        return $this->pregReplaceAssoc($typographicReplace, $Text);
    }


    // Checkbox
    private function checkbox(&$text, &$attributes)
    {
        if (!$this->config['task']) {
            return;
        }

        if (strpos($text, '[x]') !== false || strpos($text, '[ ]') !== false) {
            $attributes = array("style" => "list-style: none;");
            $text = str_replace(array('[x]', '[ ]'), array(
                '<input type="checkbox" checked="true" disabled="true">',
                '<input type="checkbox" disabled="true">',
            ), $text);
        }
    }

    // pregReplaceAssoc
    private function pregReplaceAssoc(array $replace, $subject)
    {
        return preg_replace(array_keys($replace), array_values($replace), $subject);
    }



    private function createAnchorID($str, $options = array())
    {
        // Make sure string is in UTF-8 and strip invalid UTF-8 characters
        $str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());

        $defaults = array(
            'delimiter' => '-',
            'limit' => null,
            'lowercase' => true,
            'replacements' => array(),
            'transliterate' => false,
        );

        // Merge options
        $options = array_merge($defaults, $options);

        $char_map = array(
            // Latin
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'Aa', 'Æ' => 'AE', 'Ç' => 'C',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
            'Ø' => 'Oe', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
            'ß' => 'ss', 'Œ' => 'OE',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'aa', 'æ' => 'ae', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
            'ø' => 'oe', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
            'ÿ' => 'y', 'œ' => 'oe',
            // Latin symbols
            '©' => '(c)',
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

        // Make custom replacements
        $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);

        // Transliterate characters to ASCII
        if ($options['transliterate']) {
            $str = str_replace(array_keys($char_map), $char_map, $str);
        }

        // Replace non-alphanumeric characters with our delimiter
        $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);

        // Remove duplicate delimiters
        $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);

        // Truncate slug to max. characters
        $str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');

        // Remove delimiter from ends
        $str = trim($str, $options['delimiter']);

        return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
    }


    protected function parseAttributeData($attributeString)
    {
        $Data = array();

        $attributes = preg_split('/[ ]+/', $attributeString, - 1, PREG_SPLIT_NO_EMPTY);

        foreach ($attributes as $attribute) {
            if ($attribute[0] === '#') {
                $Data['id'] = substr($attribute, 1);
            } else { // "."
                $classes []= substr($attribute, 1);
            }
        }

        if (isset($classes)) {
            $Data['class'] = implode(' ', $classes);
        }

        return $Data;
    }

    protected $regexAttribute = '(?:[#.][-\w]+[ ]*)';
}
