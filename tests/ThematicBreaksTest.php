<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class ThematicBreaksTest extends TestCase
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

    public function testEnableThematicBreaks()
    {
        $this->parsedownExtended->config()->set('thematic_breaks', true);

        $this->assertEquals("<hr />", $this->parsedownExtended->text("***"));
    }

    public function testDisableThematicBreaks()
    {
        $this->parsedownExtended->config()->set('thematic_breaks', false);

        $this->assertEquals("<p>***</p>", $this->parsedownExtended->text("***"));
    }

    public function testDifferentThematicBreaks()
    {
        $this->parsedownExtended->config()->set('thematic_breaks', true);

        $this->assertEquals("<hr />", $this->parsedownExtended->text("***"));
        $this->assertEquals("<hr />", $this->parsedownExtended->text("---"));
        $this->assertEquals("<hr />", $this->parsedownExtended->text("___"));
    }
}
