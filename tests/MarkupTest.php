<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class MarkupTest extends TestCase
{
    protected ParsedownExtended $parsedownExtended;

    protected function setUp(): void
    {
        $this->parsedownExtended = new ParsedownExtended();
        $this->parsedownExtended->setSafeMode(false);
    }

    protected function tearDown(): void
    {
        unset($this->parsedownExtended);
    }

    public function testInlineRawHtmlEnabled()
    {
        $this->parsedownExtended->config()->set('allow_raw_html', true);

        $markdown = "This is <span>raw</span> text";
        $expectedHtml = "<p>This is <span>raw</span> text</p>";
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }

    public function testInlineRawHtmlDisabled()
    {
        $this->parsedownExtended->config()->set('allow_raw_html', false);

        $markdown = "This is <span>raw</span> text";
        $expectedHtml = "<p>This is &lt;span&gt;raw&lt;/span&gt; text</p>";
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }

    public function testBlockRawHtmlEnabled()
    {
        $this->parsedownExtended->config()->set('allow_raw_html', true);

        $markdown = "<div>raw</div>";
        $expectedHtml = "<div>raw</div>";
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }

    public function testBlockRawHtmlDisabled()
    {
        $this->parsedownExtended->config()->set('allow_raw_html', false);

        $markdown = "<div>raw</div>";
        $expectedHtml = "<p>&lt;div&gt;raw&lt;/div&gt;</p>";
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }
}
