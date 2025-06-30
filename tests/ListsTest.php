<?php

use Erusev\Parsedown\State;
use Erusev\Parsedown\Parsedown;
use Erusev\ParsedownExtra\ParsedownExtra;
use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class ListsTest extends TestCase
{
    protected Parsedown $parsedownExtended;

    protected function setUp(): void
    {
        $this->parsedownExtended = new Parsedown(ParsedownExtended::from(new State()));

    }

    protected function tearDown(): void
    {
        unset($this->parsedownExtended);
    }

    public function testBlockUnorderedList()
    {
        $markdown = "- Item 1\n- Item 2";
        $expectedHtml = "<ul>\n<li>Item 1</li>\n<li>Item 2</li>\n</ul>";
        $result = $this->parsedownExtended->toHtml($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }

    public function testBlockOrderedList()
    {
        $markdown = "1. Item 1\n2. Item 2";
        $expectedHtml = "<ol>\n<li>Item 1</li>\n<li>Item 2</li>\n</ol>";
        $result = $this->parsedownExtended->toHtml($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }
}
