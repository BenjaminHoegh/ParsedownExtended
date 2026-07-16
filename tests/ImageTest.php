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

    public function testInlineImageDisabled()
    {
        $this->parsedownExtended->config()->set('images', false);

        $markdown = "![alt text](image.png)";
        $expectedHtml = '<p>!<a href="image.png">alt text</a></p>';
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

    public function testImageRemainsEnabledWhenLinksAreDisabled(): void
    {
        $this->parsedownExtended->config()->set('links', false);

        $this->assertSame(
            '<p><img src="/image.png" alt="alt" /></p>',
            $this->parsedownExtended->text('![alt](/image.png)')
        );
    }

    public function testExternalImageRemainsEnabledWhenExternalLinksAreDisabled(): void
    {
        $this->parsedownExtended->config()
            ->set('links.current_host', 'example.test')
            ->set('links.external_links', false);

        $this->assertSame(
            '<p><img src="https://cdn.example.test/image.png" alt="alt" /></p>',
            $this->parsedownExtended->text('![alt](https://cdn.example.test/image.png)')
        );
    }
}
