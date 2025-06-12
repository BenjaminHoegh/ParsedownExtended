<?php

namespace BenjaminHoegh\ParsedownExtended\Features;

use Erusev\Parsedown\State;
use Erusev\Parsedown\StateBearer;
use Erusev\Parsedown\Configurables\BlockTypes;

use BenjaminHoegh\ParsedownExtended\Configurables\AlertsConfig;
use BenjaminHoegh\ParsedownExtended\Components\Blocks\Alert;

final class Alerts implements StateBearer
{
    /** @var State */
    private $State;

    public function __construct(StateBearer $StateBearer = null)
    {
        $State = ($StateBearer ?? new State())->state();

        $BlockTypes = $State->get(BlockTypes::class)
            ->addingMarkedHighPrecedence('>', [Alert::class]);

        $alertsConfig = $State->get(AlertsConfig::class);
        if (!$alertsConfig instanceof AlertsConfig) {
            $alertsConfig = AlertsConfig::initial();
        }

        $this->State = $State
            ->setting($BlockTypes)
            ->setting($alertsConfig);
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
