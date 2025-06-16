<?php

namespace BenjaminHoegh\ParsedownExtended\Features;

use Erusev\Parsedown\Configurables\BlockTypes;
use Erusev\Parsedown\Configurables\InlineTypes;
use Erusev\Parsedown\State;
use Erusev\Parsedown\StateBearer;

use BenjaminHoegh\ParsedownExtended\Components\Blocks\TocHeader;
use BenjaminHoegh\ParsedownExtended\Components\Blocks\TocSetextHeader;
use BenjaminHoegh\ParsedownExtended\Components\Inlines\Toc as TocInline;
use BenjaminHoegh\ParsedownExtended\Configurables\HeadingBook;

final class TableOfContents implements StateBearer
{
    /** @var State */
    private $State;

    public function __construct(?StateBearer $StateBearer = null)
    {
        $State = ($StateBearer ?? new State())->state();

        $BlockTypes = $State->get(BlockTypes::class)
            ->replacing(\Erusev\Parsedown\Components\Blocks\Header::class, TocHeader::class)
            ->replacing(\Erusev\Parsedown\Components\Blocks\SetextHeader::class, TocSetextHeader::class);

        $InlineTypes = $State->get(InlineTypes::class)
            ->addingHighPrecedence('[', [TocInline::class]);

        $this->State = $State
            ->setting($BlockTypes)
            ->setting($InlineTypes)
            ->setting(HeadingBook::initial());
    }

    public function state(): State
    {
        return $this->State;
    }

    /** @return self */
    public static function from(StateBearer $StateBearer)
    {
        return new self($StateBearer);
    }
}
