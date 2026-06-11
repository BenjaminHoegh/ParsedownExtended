<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Extensions\Concerns;

trait MovesSpecialCharacterHandler
{
    /**
     * Ensures the special-character handler always executes last for each marker list.
     *
     * @param array $types Parser type map keyed by marker.
     * @return void
     */
    private function moveSpecialCharacterHandlerToEnd(array &$types): void
    {
        foreach ($types as &$list) {
            $key = array_search('SpecialCharacter', $list, true);
            if ($key === false) {
                continue;
            }

            unset($list[$key]);
            $list[] = 'SpecialCharacter';
        }
        unset($list);
    }
}
