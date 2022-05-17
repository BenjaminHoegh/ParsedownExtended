<?php

namespace BenjaminHoegh\ParsedownExtended\Components\Blocks;

use Erusev\Parsedown\Components\Block;
use Erusev\Parsedown\Components\ContinuableBlock;
use Erusev\Parsedown\Html\Renderables\Text;
use Erusev\Parsedown\Parsing\Context;
use Erusev\Parsedown\Parsing\Line;
use Erusev\Parsedown\Parsing\Lines;
use Erusev\Parsedown\State;

final class Math implements ContinuableBlock
{
    /** @var string */
    private $text;

    /** @var string */
    private $marker;

    /** @var int */
    private $openerLength;

    /** @var bool */
    private $isComplete;

    /**
     * @param string $text
     * @param string $marker
     * @param bool $isComplete
     */
    private function __construct($text, $marker, $isComplete)
    {
        $this->text = $text;
        $this->marker = $marker;
        $this->isComplete = $isComplete;
    }

    /**
     * @param Context $Context
     * @param State $State
     * @param Block|null $Block
     * @return static|null
     */
    public static function build(
        Context $Context,
        State $State,
        Block $Block = null
    ) {
        $line = $Context->line()->text();

        switch ($line)
        {
            // MathJax/KaTeX Standard
            case '\\[':
                $marker = $line;
                break;
            case '$$':
                $marker = $line;
                break;
            // KaTeX Environments
            case (preg_match('/^\\\begin{.*}({.*})?$/', $line) ? true : false):
                $marker = $line;
                break;
            default:
                return null;
        }

        return new self('', $marker, false);
    }

    /**
     * @param Context $Context
     * @param State $State
     * @return self|null
     */
    public function advance(Context $Context, State $State)
    {
        if ($this->isComplete) {
            return null;
        }

        $newText = $this->text;

        $newText .= $Context->precedingEmptyLinesText();

        switch ($this->marker)
        {
            // MathJax/KaTeX Standard
            case '\\[':
                if ($Context->line()->text() == '\\]')
                {
                    return new self('\\['.$newText.'\\]', $this->marker, true);
                }
                break;
            case '$$':
                if ($Context->line()->text() == '$$')
                {
                    return new self('$$'.$newText.'$$', $this->marker, true);
                }
                break;
            // KaTeX Environments
            case (preg_match('/^\\\begin{.*}({.*})?$/', $this->marker) ? true : false):

                $endMarker = str_replace('begin', 'end', $this->marker);

                if (substr_count($endMarker, "{") > 1)
                {
                    $endMarker = substr($endMarker, 0, strrpos( $endMarker, '{'));
                }

                if ($endMarker == $Context->line()->text())
                {
                    return new self($this->marker.$newText.$endMarker, $this->marker, true);
                }
                break;
        }

        $newText .= $Context->line()->rawLine() . "\n";

        return new self($newText, $this->marker, false);
    }

    /** @return string */
    public function text()
    {
        return $this->text;
    }

    /**
     * @return Element
     */
    public function stateRenderable()
    {
        return new Text($this->text());
    }
}
?>
