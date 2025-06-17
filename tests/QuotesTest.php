<?php

use Erusev\Parsedown\Parsedown;
use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class QuotesTest extends TestCase
{
    protected Parsedown $parsedown;

    protected function setUp(): void
    {
        $this->parsedown = new Parsedown(new ParsedownExtended());
    }

    protected function tearDown(): void
    {
        unset($this->parsedown);
    }

    public function testBlockQuote()
    {
        $markdown = "> This is a quote.";
        $expectedHtml = "<blockquote>\n<p>This is a quote.</p>\n</blockquote>";
        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }
}
