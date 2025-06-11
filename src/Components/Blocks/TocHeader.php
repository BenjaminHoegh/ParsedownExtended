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

    /** @var array<string,string> */
    private $attributes;

    /**
     * @param string $text
     * @param 1|2|3|4|5|6 $level
     * @param string $slug
     */
    private function __construct($text, $level, $slug, array $attributes = [])
    {
        $this->text = $text;
        $this->level = $level;
        $this->slug = $slug;
        $this->attributes = $attributes;
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


        list($text, $attributes) = self::parseAttributes($text);

        $HeaderSlug = $State->get(HeaderSlug::class);
        $Register = $State->get(SlugRegister::class);
        $slug = $attributes['id'] ?? $HeaderSlug->transform($Register, $text);

        $State->get(HeadingBook::class)->mutatingAdd($slug, $text, $level);

        return new self($text, $level, $slug, $attributes);
    }

    public function text()
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
