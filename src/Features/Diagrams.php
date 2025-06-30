<?php

namespace BenjaminHoegh\ParsedownExtended\Features;

use Erusev\Parsedown\State;
use Erusev\Parsedown\StateBearer;
use Erusev\Parsedown\Configurables\BlockTypes;

use BenjaminHoegh\ParsedownExtended\Components\Blocks\Diagram;

final class Diagrams implements StateBearer
{
    /** @var State */
    private $State;

    public function __construct(?StateBearer $StateBearer = null)
    {
        $State = ($StateBearer ?? new State)->state();

        $BlockTypes = $State->get(BlockTypes::class)
            ->addingMarkedHighPrecedence('`', [Diagram::class])
            ->addingMarkedHighPrecedence('~', [Diagram::class]);

        $this->State = $State
            ->setting($BlockTypes);
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
