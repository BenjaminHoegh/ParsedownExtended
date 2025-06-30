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

final class CriticMarkup implements Inline
{
    use WidthTrait;

    private const ADDITION = 'addition';
    private const DELETION = 'deletion';
    private const SUBSTITUTION = 'substitution';
    private const COMMENT = 'comment';
    private const HIGHLIGHT = 'highlight';

    /** @var string */
    private $type;

    /** @var string */
    private $text;

    /** @var string|null */
    private $replacement;

    private function __construct(string $type, string $text, ?string $replacement, int $width)
    {
        $this->type = $type;
        $this->text = $text;
        $this->replacement = $replacement;
        $this->width = $width;
    }

    public static function build(Excerpt $Excerpt, ?State $State = null)
    {
        $text = $Excerpt->text();

        if (preg_match('/^\{\+\+(?=\S)(.+?)(?<=\S)\+\+\}/s', $text, $m)) {
            return new self(self::ADDITION, $m[1], null, strlen($m[0]));
        }

        if (preg_match('/^\{--(?=\S)(.+?)(?<=\S)--\}/s', $text, $m)) {
            return new self(self::DELETION, $m[1], null, strlen($m[0]));
        }

        if (preg_match('/^\{~~(?=\S)(.+?)(?<=\S)~>(?=\S)(.+?)(?<=\S)~~\}/s', $text, $m)) {
            return new self(self::SUBSTITUTION, $m[1], $m[2], strlen($m[0]));
        }

        if (preg_match('/^\{>>(?=\S)(.+?)(?<=\S)<<\}/s', $text, $m)) {
            return new self(self::COMMENT, $m[1], null, strlen($m[0]));
        }

        if (preg_match('/^\{==(?=\S)(.+?)(?<=\S)==\}/s', $text, $m)) {
            return new self(self::HIGHLIGHT, $m[1], null, strlen($m[0]));
        }

        return null;
    }

    public function stateRenderable()
    {
        return new Handler(function (State $State) {
            switch ($this->type) {
                case self::ADDITION:
                    return new Element('ins', [], $State->applyTo(Parsedown::line($this->text, $State)));
                case self::DELETION:
                    return new Element('del', [], $State->applyTo(Parsedown::line($this->text, $State)));
                case self::SUBSTITUTION:
                    return new Element('span', [], [
                        new Element('del', [], $State->applyTo(Parsedown::line($this->text, $State))),
                        new Element('ins', [], $State->applyTo(Parsedown::line($this->replacement, $State))),
                    ]);
                case self::COMMENT:
                    return new Element('span', ['class' => 'critic comment'], $State->applyTo(Parsedown::line($this->text, $State)));
                case self::HIGHLIGHT:
                    return new Element('mark', [], $State->applyTo(Parsedown::line($this->text, $State)));
                default:
                    return new Text($this->text);
            }
        });
    }

    public function bestPlaintext()
    {
        return new Text($this->type === self::SUBSTITUTION ? $this->replacement : $this->text);
    }
}
