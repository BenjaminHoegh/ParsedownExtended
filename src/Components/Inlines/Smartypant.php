<?php

namespace BenjaminHoegh\ParsedownExtended\Components\Inlines;

use Erusev\Parsedown\Components\Inline;
use Erusev\Parsedown\Components\Inlines\WidthTrait;
use Erusev\Parsedown\Html\Renderables\Text;
use Erusev\Parsedown\Html\Renderables\RawHtml;
use Erusev\Parsedown\Parsing\Excerpt;
use Erusev\Parsedown\State;

final class Smartypant implements Inline
{
    use WidthTrait;

    /** @var string */
    private $text;
    /** @var string */
    private $replacement;

    private function __construct(string $text, string $replacement, int $width)
    {
        $this->text = $text;
        $this->replacement = $replacement;
        $this->width = $width;
    }

    public static function build(Excerpt $Excerpt, State $State = null)
    {
        $text = $Excerpt->text();

        if (substr($text, 0, 3) === '---') {
            return new self('---', '&mdash;', 3);
        }

        if (substr($text, 0, 2) === '--') {
            return new self('--', '&ndash;', 2);
        }

        if (substr($text, 0, 3) === '...') {
            return new self('...', '&hellip;', 3);
        }

        if ($text !== '' && ($text[0] === '"' || $text[0] === "'")) {
            $quote = $text[0];
            $context = $Excerpt->context();
            $offset = $Excerpt->offset();

            $prev = $offset > 0 ? $context[$offset - 1] : '';
            $isOpener = $prev === '' || preg_match('/[\s\(\[{<]/', $prev);

            if ($quote === '"') {
                $replacement = $isOpener ? '&ldquo;' : '&rdquo;';
            } else {
                $replacement = $isOpener ? '&lsquo;' : '&rsquo;';
            }

            return new self($quote, $replacement, 1);
        }

        return null;
    }

    public function text(): string
    {
        return $this->text;
    }

    public function stateRenderable()
    {
        return new RawHtml($this->replacement);
    }

    public function bestPlaintext()
    {
        return new Text($this->text);
    }
}
