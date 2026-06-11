<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions;

trait ExtensionRegistrar
{
    private function registerExtensions(): void
    {
        $this->registerCustomInlineTypes();
        $this->registerCustomBlockTypes();
        $this->moveSpecialCharacterHandlerToEnd($this->InlineTypes);
        $this->moveSpecialCharacterHandlerToEnd($this->BlockTypes);
    }
}
