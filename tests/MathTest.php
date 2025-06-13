<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class MathTest extends TestCase
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

    public function testInlineMath()
    {
        $markdown = '$E=mc^2$';
        $expectedHtml = '<p>$E=mc^2$</p>';

        $this->parsedownExtended->config()->set('math', true);
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testBlockMath()
    {
        $markdown = '$$E=mc^2$$';
        $expectedHtml = 'E=mc^2';

        $this->parsedownExtended->config()->set('math', true);
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }
}
