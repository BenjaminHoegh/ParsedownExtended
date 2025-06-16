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

    /** @var array<string,string> */
    private $attributes;

    /**
     * @param string $text
     * @param 1|2 $level
     * @param string $slug
     */
    private function __construct($text, $level, $slug, array $attributes = [])
    {
        $this->text = $text;
        $this->level = $level;
        $this->slug = $slug;
        $this->attributes = $attributes;
    }

    public static function build(Context $Context, State $State, ?Block $Block = null)
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

            list($text, $attributes) = self::parseAttributes($text);

            $HeaderSlug = $State->get(HeaderSlug::class);
            $Register = $State->get(SlugRegister::class);
            $slug = $attributes['id'] ?? $HeaderSlug->transform($Register, $text);

            $State->get(HeadingBook::class)->mutatingAdd($slug, $text, $level);

            return new self($text, $level, $slug, $attributes);
        }

        return null;
    }

    public function acquiredPrevious(): bool
    {
        return true;
    }

    public function text(): string
    {
        return $this->text;
    }

    public function level()
    {
        return $this->level;
    }

    /**
     * @return array<string,string>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }

    public function stateRenderable()
    {
        return new Handler(function (State $State) {
            $HeaderSlug = $State->get(HeaderSlug::class);
            $attributes = $HeaderSlug->isEnabled() ? ['id' => $this->slug] : [];
            $attributes = array_merge($attributes, $this->attributes);

            return new Element(
                'h' . \strval($this->level()),
                $attributes,
                $State->applyTo(Parsedown::line($this->text(), $State))
            );
        });
    }

    /**
     * @param string $text
     * @return array{0:string,1:array<string,string>}
     */
    private static function parseAttributes(string $text): array
    {
        $attributes = [];

        if (preg_match('/\s*\{([^}]*)\}\s*$/', $text, $m)) {
            $text = rtrim(substr($text, 0, -strlen($m[0])));
            $parts = preg_split('/\s+/', trim($m[1]));
            $classes = [];
            foreach ($parts as $part) {
                if ($part === '') {
                    continue;
                }
                if ($part[0] === '#') {
                    $attributes['id'] = substr($part, 1);
                } elseif ($part[0] === '.') {
                    $classes[] = substr($part, 1);
                }
            }
            if ($classes) {
                $attributes['class'] = implode(' ', $classes);
            }
        }

        return [$text, $attributes];
    }
}
