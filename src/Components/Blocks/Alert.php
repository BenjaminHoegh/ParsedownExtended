<?php

namespace BenjaminHoegh\ParsedownExtended\Components\Blocks;

use Erusev\Parsedown\AST\Handler;
use Erusev\Parsedown\Components\Block;
use Erusev\Parsedown\Components\ContinuableBlock;
use Erusev\Parsedown\Html\Renderables\Element;
use Erusev\Parsedown\Parsedown;
use Erusev\Parsedown\Parsing\Context;
use Erusev\Parsedown\State;
use BenjaminHoegh\ParsedownExtended\Configurables\AlertsConfig;

final class Alert implements ContinuableBlock
{
    /** @var string */
    private $type;

    /** @var string[] */
    private $lines;

    /** @var string */
    private $class;

    /**
     * @param string $type
     * @param string[] $lines
     * @param string $class
     */
    private function __construct(string $type, array $lines, string $class)
    {
        $this->type = $type;
        $this->lines = $lines;
        $this->class = $class;
    }

    public static function build(Context $Context, State $State, Block $Block = null)
    {
        $config = $State->get(AlertsConfig::class);
        $typesPattern = implode('|', array_map('strtoupper', $config->types()));

        if (preg_match('/^> \[!(' . $typesPattern . ')\]/i', $Context->line()->text(), $matches)) {
            $type = strtolower($matches[1]);
            return new self($type, [], $config->class());
        }

        return null;
    }

    public function advance(Context $Context, State $State)
    {
        $config = $State->get(AlertsConfig::class);
        $typesPattern = implode('|', array_map('strtoupper', $config->types()));

        $text = $Context->line()->text();

        if (preg_match('/^> \[!(' . $typesPattern . ')\]/i', $text)) {
            return null;
        }

        if (isset($text[0]) && $text[0] === '>' && preg_match('/^> ?(.*)/', $text, $matches)) {
            $lines = $this->lines;
            $lines[] = $matches[1];
            return new self($this->type, $lines, $this->class);
        }

        if ($text !== '') {
            $lines = $this->lines;
            $lines[] = $text;
            return new self($this->type, $lines, $this->class);
        }

        $lines = $this->lines;
        $lines[] = '';
        return new self($this->type, $lines, $this->class);
    }

    /**
     * @return Element
     */
    public function stateRenderable()
    {
        $class = $this->class;
        $title = ucfirst($this->type);
        return new Handler(function (State $State) use ($class, $title) {
            $elements = [];
            $elements[] = new Element('p', ['class' => $class . '-title'], $State->applyTo(Parsedown::line($title, $State)));
            foreach ($this->lines as $line) {
                $elements[] = new Element('p', [], $State->applyTo(Parsedown::line($line, $State)));
            }
            return new Element('div', ['class' => $class . ' ' . $class . '-' . $this->type], $elements);
        });
    }
}
