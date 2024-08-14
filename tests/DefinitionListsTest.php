<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class DefinitionListsTest extends TestCase
{
    protected ParsedownExtended $parsedownExtended;

    protected function setUp(): void
    {
        $this->parsedownExtended = new ParsedownExtended();
        $this->parsedownExtended->setSafeMode(true); // As we always want to support safe mode
    }

    protected function tearDown(): void
    {
        unset($this->parsedownExtended);
    }

    public function testBlockDefinitionList()
    {
        $markdown = "Term\n: Definition";
        $expectedHtml = "<dl>\n<dt>Term</dt>\n<dd>Definition</dd>\n</dl>";
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }
}
