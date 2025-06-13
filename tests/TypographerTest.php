<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class TypographerTest extends TestCase
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

    public function testEnableTypographer(): void
    {
        $this->parsedownExtended->config()->set('typographer', true);

        $markdown = '(c) (r) (tm) ....';
        $expected = '<p>© ® ™ ...</p>';

        $result = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $result);
    }

    public function testDisableTypographer(): void
    {
        $this->parsedownExtended->config()->set('typographer', false);

        $markdown = '(c) (r) (tm) ....';
        $expected = '<p>(c) (r) (tm) ....</p>';

        $result = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $result);
    }

    public function testTypographerWithSmartypants(): void
    {
        $this->parsedownExtended->config()->set('typographer', true);
        $this->parsedownExtended->config()->set('smarty', true);

        $markdown = '(c) (r) (tm) ...';
        $expected = '<p>© ® ™ …</p>';

        $result = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $result);
    }

}
