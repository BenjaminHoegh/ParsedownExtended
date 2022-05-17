<?php

namespace BenjaminHoegh\ParsedownExtended;

use Erusev\Parsedown\State;
use Erusev\Parsedown\StateBearer;

use BenjaminHoegh\ParsedownExtended\Features\Maths;
use BenjaminHoegh\ParsedownExtended\Features\Marks;

final class ParsedownExtended implements StateBearer
{
    /** @var State */
    private $State;

    public function __construct(StateBearer $StateBearer = null)
    {
        $StateBearer = Maths::from($StateBearer ?? new State());
        $StateBearer = Marks::from($StateBearer);

        $this->State = $StateBearer->state();
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
