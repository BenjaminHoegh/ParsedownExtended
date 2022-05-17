<?php

namespace BenjaminHoegh\ParsedownExtended\Components\Inlines;

use Erusev\Parsedown\AST\Handler;
use Erusev\Parsedown\Components\Inline;
use Erusev\Parsedown\Components\Inlines\WidthTrait;
use Erusev\Parsedown\Html\Renderables\Text;
use Erusev\Parsedown\Html\Renderables\Element;
use Erusev\Parsedown\Parsedown;
use Erusev\Parsedown\Parsing\Excerpt;
use Erusev\Parsedown\State;


final class Highlight implements Inline
{
    use WidthTrait;

    /** @var string */
    private $text;

    private function __construct($text, $width)
    {
        $this->text = $text;
        $this->width = $width;
    }

    /**
     * @param Excerpt $Excerpt
     * @param State $State
     * @return static|null
     */
    public static function build(Excerpt $Excerpt, State $State = null)
    {
        $text = $Excerpt->text();

        if (\preg_match('/^==(?=\S)(.+?)(?<=\S)==/', $text, $matches)) {
            return new self($matches[1], \strlen($matches[0]));
        }

        return null;
    }

    public function text(): string
    {
        return $this->text;
    }

    /**
     * @return Element
     */
    public function stateRenderable()
    {
        return new Handler(
            /** @return Element */
            function (State $State) {
                return new Element(
                    'mark',
                    [],
                    $State->applyTo(Parsedown::line($this->text(), $State))
                );
            }
        );
    }

    /**
     * @return Text
     */
    public function bestPlaintext()
    {
        return new Text($this->text());
    }
}
