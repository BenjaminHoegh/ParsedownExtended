<?php

use Erusev\Parsedown\State;
use Erusev\Parsedown\Parsedown;
use Erusev\ParsedownExtra\ParsedownExtra;
use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
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

    public function testInlineImage()
    {
        $markdown = "![](image.png)";
        $expectedHtml = '<p><img src="image.png" alt="" /></p>';
        $result = $this->parsedownExtended->toHtml($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }

    public function testImageWithAltText()
    {
        $markdown = "![alt text](image.png)";
        $expectedHtml = '<p><img src="image.png" alt="alt text" /></p>';
        $result = $this->parsedownExtended->toHtml($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }
}
