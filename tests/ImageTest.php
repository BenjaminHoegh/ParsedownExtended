<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
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

    public function testInlineImage()
    {
        $markdown = "![](image.png)";
        $expectedHtml = '<p><img src="image.png" alt="" /></p>';
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }

    public function testImageWithAltText()
    {
        $markdown = "![alt text](image.png)";
        $expectedHtml = '<p><img src="image.png" alt="alt text" /></p>';
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }

    public function testImageWithTrailingAttributes()
    {
        $markdown = '![some image](image.png){.shadow.center [data-zoom] #hero}';
        $expectedHtml = '<p><img src="image.png" alt="some image" class="shadow center" data-zoom="data-zoom" id="hero" /></p>';
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }

    public function testImageWithInvalidTrailingAttributesFallsBackToText()
    {
        $markdown = '![some image](image.png){.shadow.center [data-zoom] #hero';
        $expectedHtml = '<p><img src="image.png" alt="some image" />{.shadow.center [data-zoom] #hero</p>';
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }

    public function testImageDisallowsSensitiveTrailingAttributes()
    {
        $markdown = '![some image](image.png){[src=evil.png] [style=color:red] .safe #ok [data-track=1]}';
        $result = $this->parsedownExtended->text($markdown);

        $this->assertStringContainsString('src="image.png"', $result);
        $this->assertStringContainsString('class="safe"', $result);
        $this->assertStringContainsString('id="ok"', $result);
        $this->assertStringContainsString('data-track="1"', $result);
        $this->assertStringNotContainsString('src="evil.png"', $result);
        $this->assertStringNotContainsString('style="color:red"', $result);
    }
}
