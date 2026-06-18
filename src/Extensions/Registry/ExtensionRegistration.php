<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Registry;

use BenjaminHoegh\ParsedownExtended\Extensions\Concerns\MovesSpecialCharacterHandler;
use BenjaminHoegh\ParsedownExtended\Extensions\Concerns\RegistersBlockTypes;
use BenjaminHoegh\ParsedownExtended\Extensions\Concerns\RegistersInlineTypes;

trait ExtensionRegistration
{
    use ExtensionRegistrar;
    use RegistersInlineTypes;
    use RegistersBlockTypes;
    use MovesSpecialCharacterHandler;
}
