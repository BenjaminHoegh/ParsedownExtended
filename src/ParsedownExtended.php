<?php

namespace BenjaminHoegh\ParsedownExtended;

use Erusev\Parsedown\State;
use Erusev\Parsedown\StateBearer;

use BenjaminHoegh\ParsedownExtended\Features\Maths;
use BenjaminHoegh\ParsedownExtended\Features\Highlights;
use BenjaminHoegh\ParsedownExtended\Features\Keystrokes;
use BenjaminHoegh\ParsedownExtended\Features\Superscripts;
use BenjaminHoegh\ParsedownExtended\Features\Subscripts;
use BenjaminHoegh\ParsedownExtended\Features\Emojis;
use BenjaminHoegh\ParsedownExtended\Features\Typographers;

final class ParsedownExtended implements StateBearer
{
    /** @var State */
    private $State;

    public function __construct(StateBearer $StateBearer = null)
    {
        $StateBearer = Maths::from($StateBearer ?? new State());
        $StateBearer = Highlights::from($StateBearer);
        $StateBearer = Keystrokes::from($StateBearer);
        $StateBearer = Superscripts::from($StateBearer);
        $StateBearer = Subscripts::from($StateBearer);
        $StateBearer = Emojis::from($StateBearer);
        $StateBearer = Typographers::from($StateBearer);

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
