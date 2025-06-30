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

    public static function build(Excerpt $Excerpt, ?State $State = null)
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

            $root = [];
            $stack = [];

            foreach ($headings as $h) {
                $node = ['heading' => $h, 'children' => []];

                while (!empty($stack) && end($stack)['heading']['level'] >= $h['level']) {
                    array_pop($stack);
                }

                if (empty($stack)) {
                    $root[] = $node;
                    $stack[] = &$root[array_key_last($root)];
                } else {
                    $parent = &$stack[array_key_last($stack)];
                    $parent['children'][] = $node;
                    $stack[] = &$parent['children'][array_key_last($parent['children'])];
                }
            }

            $buildList = function (array $nodes) use (&$buildList, $State) {
                $items = [];
                foreach ($nodes as $n) {
                    $contents = [
                        new Element(
                            'a',
                            ['href' => '#' . $n['heading']['slug']],
                            $State->applyTo(Parsedown::line($n['heading']['text'], $State))
                        )
                    ];

                    if (!empty($n['children'])) {
                        $contents[] = new Element('ul', [], $buildList($n['children']));
                    }

                    $items[] = new Element('li', [], $contents);
                }
                return $items;
            };

            return new Element('nav', ['class' => 'toc'], [
                new Element('ul', [], $buildList($root))
            ]);
        });
    }

    public function bestPlaintext()
    {
        return new Text('[toc]');
    }
}
