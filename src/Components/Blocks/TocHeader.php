<?php

namespace BenjaminHoegh\ParsedownExtended\Components\Blocks;

use Erusev\Parsedown\AST\Handler;
use Erusev\Parsedown\Components\Block;
use Erusev\Parsedown\Configurables\HeaderSlug;
use Erusev\Parsedown\Configurables\SlugRegister;
use Erusev\Parsedown\Html\Renderables\Element;
use Erusev\Parsedown\Parsedown;
use Erusev\Parsedown\Parsing\Context;
use Erusev\Parsedown\State;
use BenjaminHoegh\ParsedownExtended\Configurables\HeadingBook;

final class TocHeader implements Block
{
    /** @var string */
    private $text;

    /** @var 1|2|3|4|5|6 */
    private $level;

    /** @var string */
    private $slug;

    /**
     * @param string $text
     * @param 1|2|3|4|5|6 $level
     * @param string $slug
     */
    private function __construct($text, $level, $slug)
    {
        $this->text = $text;
        $this->level = $level;
        $this->slug = $slug;
    }

    public static function build(Context $Context, State $State, Block $Block = null)
    {
        if ($Context->line()->indent() > 3) {
            return null;
        }

        $level = \strspn($Context->line()->text(), '#');

        if ($level > 6 || $level < 1) {
            return null;
        }

        /** @var 1|2|3|4|5|6 $level */

        $text = \ltrim($Context->line()->text(), '#');
        $firstChar = \substr($text, 0, 1);

        if ($State->get(\Erusev\Parsedown\Configurables\StrictMode::class)->isEnabled()
            && \trim($firstChar, " \t") !== ''
        ) {
            return null;
        }

        $text = \trim($text, " \t");
        $removedClosing = \rtrim($text, '#');
        $lastChar = \substr($removedClosing, -1);

        if (\trim($lastChar, " \t") === '') {
            $text = \rtrim($removedClosing, " \t");
        }

        $HeaderSlug = $State->get(HeaderSlug::class);
        $Register = $State->get(SlugRegister::class);
        $slug = $HeaderSlug->transform($Register, $text);

        $State->get(HeadingBook::class)->mutatingAdd($slug, $text, $level);

        return new self($text, $level, $slug);
    }

    public function text()
    {
        return $this->text;
    }

    public function level()
    {
        return $this->level;
    }

    public function stateRenderable()
    {
        return new Handler(function (State $State) {
            $HeaderSlug = $State->get(HeaderSlug::class);
            $attributes = $HeaderSlug->isEnabled() ? ['id' => $this->slug] : [];

            return new Element(
                'h' . \strval($this->level()),
                $attributes,
                $State->applyTo(Parsedown::line($this->text(), $State))
            );
        });
    }
}
