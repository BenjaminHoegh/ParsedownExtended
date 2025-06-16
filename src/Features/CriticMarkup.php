<?php

namespace BenjaminHoegh\ParsedownExtended\Features;

use Erusev\Parsedown\State;
use Erusev\Parsedown\StateBearer;
use Erusev\Parsedown\Configurables\InlineTypes;

use BenjaminHoegh\ParsedownExtended\Components\Inlines\CriticAddition;
use BenjaminHoegh\ParsedownExtended\Components\Inlines\CriticDeletion;
use BenjaminHoegh\ParsedownExtended\Components\Inlines\CriticSubstitution;
use BenjaminHoegh\ParsedownExtended\Components\Inlines\CriticComment;
use BenjaminHoegh\ParsedownExtended\Components\Inlines\CriticHighlight;

final class CriticMarkup implements StateBearer
{
    /** @var State */
    private $State;

    public function __construct(?StateBearer $StateBearer = null)
    {
        $State = ($StateBearer ?? new State())->state();

        $InlineTypes = $State->get(InlineTypes::class)
            ->addingHighPrecedence('{', [
                CriticAddition::class,
                CriticDeletion::class,
                CriticSubstitution::class,
                CriticComment::class,
                CriticHighlight::class,
            ]);

        $this->State = $State->setting($InlineTypes);
    }

    public function state(): State
    {
        return $this->State;
    }

    public static function from(StateBearer $StateBearer)
    {
        return new self($StateBearer);
    }
}
