<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions;

trait ExtensionRegistration
{
    use ExtensionRegistrar;
    use Concerns\RegistersInlineTypes;
    use Concerns\RegistersBlockTypes;
    use Concerns\MovesSpecialCharacterHandler;
}
