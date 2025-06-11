<?php

namespace BenjaminHoegh\ParsedownExtended\Features;

use Erusev\Parsedown\State;
use Erusev\Parsedown\StateBearer;
use Erusev\Parsedown\Configurables\InlineTypes;

use BenjaminHoegh\ParsedownExtended\Components\Inlines\Smartypant;

final class Smartypants implements StateBearer
{
    /** @var State */
    private $State;

    public function __construct(StateBearer $StateBearer = null)
    {
        $State = ($StateBearer ?? new State)->state();

        $InlineTypes = $State->get(InlineTypes::class)
            ->addingHighPrecedence('-',[Smartypant::class])
            ->addingHighPrecedence('"',[Smartypant::class])
            ->addingHighPrecedence("'",[Smartypant::class])
            ->addingHighPrecedence('.', [Smartypant::class]);

        $this->State = $State->setting($InlineTypes);
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
