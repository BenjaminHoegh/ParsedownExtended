<?php

namespace BenjaminHoegh\ParsedownExtended\Components\Blocks;

use Erusev\Parsedown\AST\Handler;
use Erusev\Parsedown\Components\AcquisitioningBlock;
use Erusev\Parsedown\Components\Block;
use Erusev\Parsedown\Configurables\HeaderSlug;
use Erusev\Parsedown\Configurables\SlugRegister;
use Erusev\Parsedown\Html\Renderables\Element;
use Erusev\Parsedown\Parsedown;
use Erusev\Parsedown\Parsing\Context;
use Erusev\Parsedown\State;
use BenjaminHoegh\ParsedownExtended\Configurables\HeadingBook;

final class TocSetextHeader implements AcquisitioningBlock
{
    /** @var string */
    private $text;

    /** @var 1|2 */
    private $level;

    /** @var string */
    private $slug;

    /**
     * @param string $text
     * @param 1|2 $level
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
        if (! isset($Block) || ! $Block instanceof \Erusev\Parsedown\Components\Blocks\Paragraph || $Context->precedingEmptyLines() > 0) {
            return null;
        }

        $marker = \substr($Context->line()->text(), 0, 1);

        if ($marker !== '=' && $marker !== '-') {
            return null;
        }

        if (
            $Context->line()->indent() < 4
            && \chop(\chop($Context->line()->text(), " \t"), $marker) === ''
        ) {
            $level = ($marker === '=' ? 1 : 2);

            $text = \trim($Block->text());

            $HeaderSlug = $State->get(HeaderSlug::class);
            $Register = $State->get(SlugRegister::class);
            $slug = $HeaderSlug->transform($Register, $text);

            $State->get(HeadingBook::class)->mutatingAdd($slug, $text, $level);

            return new self($text, $level, $slug);
        }

        return null;
    }

    public function acquiredPrevious()
    {
        return true;
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
