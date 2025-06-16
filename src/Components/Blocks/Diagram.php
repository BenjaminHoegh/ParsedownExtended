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

    /** @var bool */
    private $isComplete;

    private function __construct(string $text, string $language, bool $isComplete)
    {
        $this->text = $text;
        $this->language = $language;
        $this->isComplete = $isComplete;
    }

    public static function build(Context $Context, State $State, ?Block $Block = null)
    {
        $line = $Context->line()->text();

        if (preg_match('/^```(mermaid|graphviz|dot|chartjs|chart)\s*$/', $line, $m)) {
            return new self('', $m[1], false);
        }

        return null;
    }

    public function advance(Context $Context, State $State)
    {
        if ($this->isComplete) {
            return null;
        }

        if ($Context->line()->text() === '```') {
            return new self($this->text, $this->language, true);
        }

        $newText = $this->text . $Context->line()->rawLine() . "\n";

        return new self($newText, $this->language, false);
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
