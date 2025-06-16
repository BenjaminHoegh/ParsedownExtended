<?php

namespace BenjaminHoegh\ParsedownExtended\Features;

use Erusev\Parsedown\State;
use Erusev\Parsedown\StateBearer;
use Erusev\Parsedown\Configurables\HeaderSlug;
use Erusev\Parsedown\Configurables\SlugRegister;

final class HeadingPermalinks implements StateBearer
{
    /** @var State */
    private $State;

    public function __construct(?StateBearer $StateBearer = null)
    {
        $State = ($StateBearer ?? new State())->state();

        $HeaderSlug = HeaderSlug::enabled();
        $SlugRegister = $State->get(SlugRegister::class);

        $this->State = $State
            ->setting($HeaderSlug)
            ->setting($SlugRegister);
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
