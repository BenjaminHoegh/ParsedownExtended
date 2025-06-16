<?php

namespace BenjaminHoegh\ParsedownExtended\Components\Inlines;

use Erusev\Parsedown\AST\Handler;
use Erusev\Parsedown\Components\Inline;
use Erusev\Parsedown\Components\Inlines\WidthTrait;
use Erusev\Parsedown\Html\Renderables\Element;
use Erusev\Parsedown\Html\Renderables\Text;
use Erusev\Parsedown\Parsedown;
use Erusev\Parsedown\Parsing\Excerpt;
use Erusev\Parsedown\State;

final class CriticComment implements Inline
{
    use WidthTrait;

    /** @var string */
    private $text;

    private function __construct(string $text, int $width)
    {
        $this->text = $text;
        $this->width = $width;
    }

    public static function build(Excerpt $Excerpt, State $State = null)
    {
        $text = $Excerpt->text();

        if (preg_match('/^\{>>(?=\S)(.+?)(?<=\S)<<\}/s', $text, $matches)) {
            return new self($matches[1], strlen($matches[0]));
        }

        return null;
    }

    public function stateRenderable()
    {
        return new Handler(function (State $State) {
            return new Element('span', ['class' => 'critic comment'], $State->applyTo(Parsedown::line($this->text, $State)));
        });
    }

    public function bestPlaintext()
    {
        return new Text($this->text);
    }
}
