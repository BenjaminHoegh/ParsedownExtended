<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;

class TestParsedownExtended extends ParsedownExtended
{
    public function getTextLevelElements()
    {
        return $this->textLevelElements;
    }
}
