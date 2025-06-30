<?php

use Erusev\Parsedown\State;
use Erusev\Parsedown\Parsedown;
use Erusev\ParsedownExtra\ParsedownExtra;
use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class QuotesTest extends TestCase
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

    public function testBlockQuote()
    {
        $markdown = "> This is a quote.";
        $expectedHtml = "<blockquote>\n<p>This is a quote.</p>\n</blockquote>";
        $result = $this->parsedownExtended->toHtml($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }
}
