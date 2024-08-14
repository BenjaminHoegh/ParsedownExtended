<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class ListsTest extends TestCase
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

    public function testBlockUnorderedList()
    {
        $markdown = "- Item 1\n- Item 2";
        $expectedHtml = "<ul>\n<li>Item 1</li>\n<li>Item 2</li>\n</ul>";
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }

    public function testBlockOrderedList()
    {
        $markdown = "1. Item 1\n2. Item 2";
        $expectedHtml = "<ol>\n<li>Item 1</li>\n<li>Item 2</li>\n</ol>";
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }
}
