<?php

use Erusev\Parsedown\State;
use Erusev\Parsedown\Parsedown;
use Erusev\ParsedownExtra\ParsedownExtra;
use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class ThematicBreaksTest extends TestCase
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

    public function testEnableThematicBreaks()
    {
        $this->parsedownExtended->config()->set('thematic_breaks', true);

        $this->assertEquals("<hr />", $this->parsedownExtended->toHtml("***"));
    }

    public function testDisableThematicBreaks()
    {
        $this->parsedownExtended->config()->set('thematic_breaks', false);

        $this->assertEquals("<p>***</p>", $this->parsedownExtended->toHtml("***"));
    }

    public function testDifferentThematicBreaks()
    {
        $this->parsedownExtended->config()->set('thematic_breaks', true);

        $this->assertEquals("<hr />", $this->parsedownExtended->toHtml("***"));
        $this->assertEquals("<hr />", $this->parsedownExtended->toHtml("---"));
        $this->assertEquals("<hr />", $this->parsedownExtended->toHtml("___"));
    }
}
