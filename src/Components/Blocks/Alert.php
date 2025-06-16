<?php

namespace BenjaminHoegh\ParsedownExtended\Components\Blocks;

use Erusev\Parsedown\AST\Handler;
use Erusev\Parsedown\Components\Block;
use Erusev\Parsedown\Components\ContinuableBlock;
use Erusev\Parsedown\Html\Renderables\Element;
use Erusev\Parsedown\Parsedown;
use Erusev\Parsedown\Parsing\Context;
use Erusev\Parsedown\Parsing\Line;
use Erusev\Parsedown\Parsing\Lines;
use Erusev\Parsedown\State;
use BenjaminHoegh\ParsedownExtended\Configurables\AlertsConfig;

final class Alert implements ContinuableBlock
{
    /** @var string */
    private $type;

    /** @var lines */
    private $lines;

    /** @var string */
    private $class;

    /**
     * @param string $type
     * @param lines $lines
     * @param string $class
     */
    private function __construct(string $type, Lines $lines, string $class)
    {
        $this->type = $type;
        $this->lines = $lines;
        $this->class = $class;
    }

    public static function build(Context $Context, State $State, Block $Block = null)
    {
        $config = $State->get(AlertsConfig::class);
        $typesPattern = implode('|', array_map('strtoupper', $config->types()));

        if (preg_match('/^(>[ \t]?+)\[!(' . $typesPattern . ')\](.*)/i', $Context->line()->text(), $matches)) {
            $indentOffset = $Context->line()->indentOffset() + $Context->line()->indent() + strlen($matches[1]);

            $recoveredSpaces = 0;
            if (strlen($matches[1]) === 2 && substr($matches[1], 1, 1) === "\t") {
                $recoveredSpaces = Line::tabShortage(0, $indentOffset - 1) - 1;
            }

            $lines = Lines::fromTextLines(
                str_repeat(' ', $recoveredSpaces) . ltrim($matches[3]),
                $indentOffset
            );

            return new self(strtolower($matches[2]), $lines, $config->class());
        }

        return null;
    }

    public function advance(Context $Context, State $State)
    {
        if ($Context->precedingEmptyLines() > 0) {
            return null;
        }

        $config = $State->get(AlertsConfig::class);
        $typesPattern = implode('|', array_map('strtoupper', $config->types()));

        $text = $Context->line()->text();

        if (preg_match('/^(>[ \t]?+)\[!(' . $typesPattern . ')\]/i', $text)) {
            return null;
        }

        if (preg_match('/^(>[ \t]?+)(.*+)/', $text, $matches)) {
            $indentOffset = $Context->line()->indentOffset() + $Context->line()->indent() + strlen($matches[1]);

            $recoveredSpaces = 0;
            if (strlen($matches[1]) === 2 && substr($matches[1], 1, 1) === "\t") {
                $recoveredSpaces = Line::tabShortage(0, $indentOffset - 1) - 1;
            }

            $lines = $this->lines->appendingTextLines(
                str_repeat(' ', $recoveredSpaces) . $matches[2],
                $indentOffset
            );

            return new self($this->type, $lines, $this->class);
        }

        if (!($Context->precedingEmptyLines() > 0)) {
            $indentOffset = $Context->line()->indentOffset() + $Context->line()->indent();
            $lines = $this->lines->appendingTextLines($Context->line()->text(), $indentOffset);

            return new self($this->type, $lines, $this->class);
        }

        return null;
    }

    /**
     * @return Element
     */
    public function stateRenderable()
    {
        $class = $this->class;
        $title = ucfirst($this->type);

        return new Handler(function (State $State) use ($class, $title) {
            list($Blocks, $State) = Parsedown::blocks($this->lines, $State);

            $StateRenderables = Parsedown::stateRenderablesFrom($Blocks);

            $elements = [];
            $elements[] = new Element('p', ['class' => $class . '-title'], $State->applyTo(Parsedown::line($title, $State)));
            $elements = array_merge($elements, $State->applyTo($StateRenderables));

            return new Element('div', ['class' => $class . ' ' . $class . '-' . $this->type], $elements);
        });
    }
}
