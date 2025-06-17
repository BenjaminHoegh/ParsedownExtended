<?php

use Erusev\Parsedown\Parsedown;
use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class ThematicBreaksTest extends TestCase
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

    public function testEnableThematicBreaks()
    {

        $this->assertEquals("<hr />", $this->parsedown->toHtml("***"));
    }

    public function testDisableThematicBreaks()
    {

        $this->assertEquals("<p>***</p>", $this->parsedown->toHtml("***"));
    }

    public function testDifferentThematicBreaks()
    {

        $this->assertEquals("<hr />", $this->parsedown->toHtml("***"));
        $this->assertEquals("<hr />", $this->parsedown->toHtml("---"));
        $this->assertEquals("<hr />", $this->parsedown->toHtml("___"));
    }
}
