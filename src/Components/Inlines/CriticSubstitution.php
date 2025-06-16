<?php

namespace BenjaminHoegh\ParsedownExtended\Components\Inlines;

use Erusev\Parsedown\AST\Handler;
use Erusev\Parsedown\Components\Inline;
use Erusev\Parsedown\Components\Inlines\WidthTrait;
use Erusev\Parsedown\Html\Renderables\RawHtml;
use Erusev\Parsedown\Html\Renderables\Text;
use Erusev\Parsedown\Parsing\Excerpt;
use Erusev\Parsedown\State;

final class CriticSubstitution implements Inline
{
    use WidthTrait;

    /** @var string */
    private $delText;
    /** @var string */
    private $insText;

    private function __construct(string $delText, string $insText, int $width)
    {
        $this->delText = $delText;
        $this->insText = $insText;
        $this->width = $width;
    }

    public static function build(Excerpt $Excerpt, State $State = null)
    {
        $text = $Excerpt->text();

        if (preg_match('/^\{~~(?=\S)(.+?)(?<=\S)~>(?=\S)(.+?)(?<=\S)~~\}/s', $text, $matches)) {
            return new self($matches[1], $matches[2], strlen($matches[0]));
        }

        return null;
    }

    public function stateRenderable()
    {
        return new Handler(function (State $State) {
            return new RawHtml('<del>' . $this->delText . '</del><ins>' . $this->insText . '</ins>');
        });
    }

    public function bestPlaintext()
    {
        return new Text($this->insText);
    }
}
