<?php

namespace BenjaminHoegh\ParsedownExtended\Components\Inlines;

use Erusev\Parsedown\Components\Inline;
use Erusev\Parsedown\Components\Inlines\WidthTrait;
use Erusev\Parsedown\Html\Renderables\Text;
use Erusev\Parsedown\Parsing\Excerpt;
use Erusev\Parsedown\State;

final class Math implements Inline
{
    use WidthTrait;

    /** @var string */
    private $text;

    public function __construct(string $text)
    {
        $this->text = $text;
        $this->width = \strlen($text);
    }

    /**
     * @param Excerpt $Excerpt
     * @param State $State
     * @return static|null
     */
    public static function build(Excerpt $Excerpt, State $State = null)
    {
        $State = $State ?: new State;

        if (\preg_match('/^\\\\\(.*\\\\\)|(?<!\$)\$[^$]{1,}\$(?!\$)/', $Excerpt->text(), $matches)) {
            $text = $matches[0];

            return new self($text);
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
        return new Text($this->text());
    }

    /**
     * @return Text
     */
    public function bestPlaintext()
    {
        return new Text($this->text());
    }
}
