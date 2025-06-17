<?php

use Erusev\Parsedown\Parsedown;
use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class TypographerTest extends TestCase
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

    public function testEnableTypographer(): void
    {

        $markdown = '(c) (r) (tm) ....';
        $expected = '<p>© ® ™ ...</p>';

        $result = $this->parsedown->toHtml($markdown);
        $this->assertEquals($expected, $result);
    }

    public function testDisableTypographer(): void
    {

        $markdown = '(c) (r) (tm) ....';
        $expected = '<p>(c) (r) (tm) ....</p>';

        $result = $this->parsedown->toHtml($markdown);
        $this->assertEquals($expected, $result);
    }

    public function testTypographerWithSmartypants(): void
    {

        $markdown = '(c) (r) (tm) ...';
        $expected = '<p>© ® ™ …</p>';

        $result = $this->parsedown->toHtml($markdown);
        $this->assertEquals($expected, $result);
    }

}
