<?php

use Erusev\Parsedown\Parsedown;
use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
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

    public function testInlineImage()
    {
        $markdown = "![](image.png)";
        $expectedHtml = '<p><img src="image.png" alt="" /></p>';
        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }

    public function testImageWithAltText()
    {
        $markdown = "![alt text](image.png)";
        $expectedHtml = '<p><img src="image.png" alt="alt text" /></p>';
        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }
}
