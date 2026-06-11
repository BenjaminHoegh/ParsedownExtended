<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class EscapeSequenceTest extends TestCase
{
    protected ParsedownExtended $parsedownExtended;

    protected function setUp(): void
    {
        $this->parsedownExtended = new ParsedownExtended();
        $this->parsedownExtended->setSafeMode(true);
    }

    protected function tearDown(): void
    {
        unset($this->parsedownExtended);
    }

    public function testEscapeSequence()
    {
        $markdown = '\\*literal\\*';
        $expectedHtml = '<p>literal</p>';
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }

    public function testEscapeSequenceWithMathDelimiters()
    {
        $this->parsedownExtended->config()->set('math', true);

        $markdown = '\\$not math\\$ and $math$';
        $expectedHtml = '<p>not math and $math$</p>';
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }
}
