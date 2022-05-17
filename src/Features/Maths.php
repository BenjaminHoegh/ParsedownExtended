<?php

namespace BenjaminHoegh\ParsedownExtended\Features;

use Erusev\Parsedown\Parsedown;
use Erusev\Parsedown\State;
use Erusev\Parsedown\StateBearer;
use Erusev\Parsedown\Configurables\BlockTypes;
use Erusev\Parsedown\Configurables\InlineTypes;

use BenjaminHoegh\ParsedownExtended\Components\Blocks\Math;
use BenjaminHoegh\ParsedownExtended\Components\Inlines\Math as InlineMath;

final class Maths implements StateBearer
{
    /** @var State */
    private $State;

    public function __construct(StateBearer $StateBearer = null)
    {
        $State = ($StateBearer ?? new State)->state();

        $BlockTypes = $State->get(BlockTypes::class)
            ->addingMarkedHighPrecedence('\\', [Math::class])
            ->addingMarkedHighPrecedence('$', [Math::class])
        ;

        $InlineTypes = $State->get(InlineTypes::class)
            ->addingHighPrecedence('\\', [InlineMath::class])
            ->addingHighPrecedence('$', [InlineMath::class])
        ;

        $this->State = $State
            ->setting($BlockTypes)
            ->setting($InlineTypes)
        ;
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
