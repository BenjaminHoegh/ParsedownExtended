<?php

namespace BenjaminHoegh\ParsedownExtended\Components\Blocks;

use Erusev\Parsedown\Components\Block;
use Erusev\Parsedown\Components\ContinuableBlock;
use Erusev\Parsedown\Html\Renderables\Element;
use Erusev\Parsedown\Html\Renderables\Text;
use Erusev\Parsedown\Parsing\Context;
use Erusev\Parsedown\State;

final class Diagram implements ContinuableBlock
{
    /** @var string */
    private $text;

    /** @var string */
    private $language;

    /** @var string */
    private $marker;

    /** @var int */
    private $openerLength;

    /** @var bool */
    private $isComplete;

    public const SUPPORTED_LANGUAGES = [
        'mermaid' => [
            'element' => 'pre',
            'class' => 'mermaid',
        ],
        'chartjs' => [
            'element' => 'canvas',
            'class' => 'chartjs',
        ],
        'chart' => [
            'element' => 'canvas',
            'class' => 'chartjs',
        ],
        // add other supported languages here
    ];

    private function __construct(string $text, string $language, string $marker, int $openerLength, bool $isComplete)
    {
        $this->text = $text;
        $this->language = $language;
        $this->marker = $marker;
        $this->openerLength = $openerLength;
        $this->isComplete = $isComplete;
    }

    public static function build(Context $Context, State $State, ?Block $Block = null)
    {
        $marker = substr($Context->line()->text(), 0, 1);

        if ($marker !== '`' && $marker !== '~') {
            return null;
        }

        $openerLength = strspn($Context->line()->text(), $marker);

        if ($openerLength < 3) {
            return null;
        }

        $infostring = trim(substr($Context->line()->text(), $openerLength), "\t ");

        if (strpos($infostring, $marker) !== false) {
            return null;
        }

        $language = substr($infostring, 0, strcspn($infostring, " \t\n\f\r"));

        if (array_key_exists($language, self::SUPPORTED_LANGUAGES)) {
            return new self('', $language, $marker, $openerLength, false);
        }

        return null;
    }

    public function advance(Context $Context, State $State)
    {
        if ($this->isComplete) {
            return null;
        }

        $newText = $this->text;

        $newText .= $Context->precedingEmptyLinesText();

        if (($len = strspn($Context->line()->text(), $this->marker)) >= $this->openerLength
            && rtrim(substr($Context->line()->text(), $len)) === ''
        ) {
            return new self($newText, $this->language, $this->marker, $this->openerLength, true);
        }

        $newText .= $Context->line()->rawLine() . "\n";

        return new self($newText, $this->language, $this->marker, $this->openerLength, false);
    }

    public function text()
    {
        return $this->text;
    }

    public function stateRenderable()
    {
        if (isset(self::SUPPORTED_LANGUAGES[$this->language])) {
            $config = self::SUPPORTED_LANGUAGES[$this->language];
            return new Element(
                $config['element'],
                ['class' => $config['class']],
                [new Text($this->text)]
            );
        }

        return new Element(
            'pre',
            ['class' => 'language-' . $this->language],
            [new Text($this->text)]
        );
    }
}
