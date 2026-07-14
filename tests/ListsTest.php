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

    public function testBlockListsDisabled()
    {
        $this->parsedownExtended->config()->set('lists', false);

        $markdown = "- Item 1\n- Item 2";
        $expectedHtml = "<p>- Item 1\n- Item 2</p>";
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

    public function testTaskListMarkerRequiresWhitespace(): void
    {
        $this->assertSame(
            "<ul>\n<li>[x]foo</li>\n</ul>",
            $this->parsedownExtended->text('- [x]foo')
        );
    }

    public function testTaskListMarkerConsumesOnlyItsSeparator(): void
    {
        $this->assertSame(
            "<ul>\n<li><input type=\"checkbox\" disabled=\"disabled\" checked=\"checked\" />foo</li>\n</ul>",
            $this->parsedownExtended->text('- [x] foo')
        );
    }
}
