<?php

namespace BenjaminHoegh\ParsedownExtended\Components\Inlines;

use Erusev\Parsedown\AST\Handler;
use Erusev\Parsedown\Components\Inline;
use Erusev\Parsedown\Components\Inlines\WidthTrait;
use Erusev\Parsedown\Html\Renderables\Text;
use Erusev\Parsedown\Html\Renderables\Element;
use Erusev\Parsedown\Html\Renderables\RawHtml;
use Erusev\Parsedown\Parsedown;
use Erusev\Parsedown\Parsing\Excerpt;
use Erusev\Parsedown\State;


final class Typographer implements Inline
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

        if (\preg_match('/\+-|\(p\)|\(tm\)|\(r\)|\(c\)|\.{2,}|\!\.{3,}|\?\.{3,}/i', $text, $matches)) {
            return new self($matches[0], \strlen($matches[0]));
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
        $substitutions = [
            '/\(c\)/i' => '&copy;',
            '/\(r\)/i' => '&reg;',
            '/\(tm\)/i' => '&trade;',
            '/\(p\)/i' => '&para;',
            '/\+-/i' => '&plusmn;',
            '/\.{4,}|\.{2}/i' => '...',
            '/\!\.{3,}/i' => '!..',
            '/\?\.{3,}/i' => '?..',
        ];

        return new RawHtml(preg_replace(array_keys($substitutions), array_values($substitutions), $this->text()));
    }

    /**
     * @return Text
     */
    public function bestPlaintext()
    {
        return new Text($this->text());
    }
}
