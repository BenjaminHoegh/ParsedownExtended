<?php

namespace BenjaminHoegh\ParsedownExtended\Components\Blocks;

use Erusev\Parsedown\Components\Block;
use Erusev\Parsedown\Components\ContinuableBlock;
use Erusev\Parsedown\Html\Renderables\Element;
use Erusev\Parsedown\Html\Renderables\Text;
use Erusev\Parsedown\Parsing\Context;
use Erusev\Parsedown\State;

final class Diagram implements ContinuableBlock
{
    /** @var string */
    private $text;

    /** @var string */
    private $language;

    /** @var string */
    private $marker;

    /** @var int */
    private $openerLength;

    /** @var bool */
    private $isComplete;

    private function __construct(string $text, string $language, string $marker, int $openerLength, bool $isComplete)
    {
        $this->text = $text;
        $this->language = $language;
        $this->marker = $marker;
        $this->openerLength = $openerLength;
        $this->isComplete = $isComplete;
    }

    public static function build(Context $Context, State $State, ?Block $Block = null)
    {
        $marker = substr($Context->line()->text(), 0, 1);

        if ($marker !== '`' && $marker !== '~') {
            return null;
        }

        $openerLength = strspn($Context->line()->text(), $marker);

        if ($openerLength < 3) {
            return null;
        }

        $infostring = trim(substr($Context->line()->text(), $openerLength), "\t ");

        if (strpos($infostring, '`') !== false) {
            return null;
        }

        $language = substr($infostring, 0, strcspn($infostring, " \t\n\f\r"));

        if ($language === false) {
            $language = '';
        }

        if (in_array($language, ['mermaid', 'graphviz', 'dot', 'chartjs', 'chart'])) {
            return new self('', $language, $marker, $openerLength, false);
        }

        return null;
    }

    public function advance(Context $Context, State $State)
    {
        if ($this->isComplete) {
            return null;
        }

        $newText = $this->text;

        $newText .= $Context->precedingEmptyLinesText();

        if (($len = strspn($Context->line()->text(), $this->marker)) >= $this->openerLength
            && chop(substr($Context->line()->text(), $len), ' ') === ''
        ) {
            return new self($newText, $this->language, $this->marker, $this->openerLength, true);
        }

        $newText .= $Context->line()->rawLine() . "\n";

        return new self($newText, $this->language, $this->marker, $this->openerLength, false);
    }

    public function text()
    {
        return $this->text;
    }

    public function stateRenderable()
    {
        if (in_array($this->language, ['chartjs', 'chart'])) {
            return new Element(
                'canvas',
                ['class' => 'chartjs'],
                [new Text($this->text)]
            );
        }

        if ($this->language === 'mermaid') {
            return new Element(
                'pre',
                ['class' => 'mermaid'],
                [new Text($this->text)]
            );
        }

        return new Element(
            'pre',
            ['class' => 'language-' . $this->language],
            [new Text($this->text)]
        );
    }
}
