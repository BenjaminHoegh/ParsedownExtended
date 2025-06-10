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

final class TaskCheckbox implements Inline
{
    use WidthTrait;

    /** @var bool */
    private $checked;

    private function __construct(bool $checked, int $width)
    {
        $this->checked = $checked;
        $this->width = $width;
    }

    public static function build(Excerpt $Excerpt, State $State = null)
    {
        $text = $Excerpt->text();

        if (preg_match('/^\[(x|X| )\](?=\s)/', $text, $matches)) {
            $checked = strtolower($matches[1]) === 'x';
            return new self($checked, strlen($matches[0]));
        }

        return null;
    }

    public function stateRenderable()
    {
        $attributes = [
            'type' => 'checkbox',
            'disabled' => 'disabled',
        ];
        if ($this->checked) {
            $attributes['checked'] = 'checked';
        }
        return new Element('input', $attributes, []);
    }

    public function bestPlaintext()
    {
        return new Text($this->checked ? '[x]' : '[ ]');
    }
}
