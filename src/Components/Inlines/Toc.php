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
use BenjaminHoegh\ParsedownExtended\Configurables\HeadingBook;

final class Toc implements Inline
{
    use WidthTrait;

    private function __construct(int $width)
    {
        $this->width = $width;
    }

    public static function build(Excerpt $Excerpt, State $State = null)
    {
        if (preg_match('/^\[toc\]/i', $Excerpt->text(), $m)) {
            return new self(strlen($m[0]));
        }
        return null;
    }

    public function stateRenderable()
    {
        return new Handler(function (State $State) {
            $headings = $State->get(HeadingBook::class)->all();

            $items = array_map(function ($h) use ($State) {
                return new Element('li', [], [
                    new Element(
                        'a',
                        ['href' => '#' . $h['slug']],
                        $State->applyTo(Parsedown::line($h['text'], $State))
                    )
                ]);
            }, $headings);

            return new Element('nav', ['class' => 'toc'], [
                new Element('ul', [], $items)
            ]);
        });
    }

    public function bestPlaintext()
    {
        return new Text('[toc]');
    }
}
