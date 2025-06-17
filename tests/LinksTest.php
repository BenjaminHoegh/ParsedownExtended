<?php

use Erusev\Parsedown\Parsedown;
use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class LinksTest extends TestCase
{
    private Parsedown $parsedown;

    protected function setUp(): void
    {
        $this->parsedown = new Parsedown(new ParsedownExtended());
        $_SERVER['HTTP_HOST'] = 'www.example.com';  // Default host for testing
    }

    protected function tearDown(): void
    {
        unset($this->parsedown);
    }

    // General Link Settings
    // ----------------------------

    public function testLinksEnabled()
    {

        $markdown = '[Link](https://www.example.com)';
        $html = $this->parsedown->toHtml($markdown);

        $this->assertStringContainsString('<a href="https://www.example.com">Link</a>', $html);
    }

    public function testLinksDisabled()
    {

        $markdown = '[Link](https://www.example.com)';
        $html = $this->parsedown->toHtml($markdown);

        $this->assertStringNotContainsString('<a href="https://www.example.com">Link</a>', $html);
    }

    // Email Links
    // ----------------------------

    public function testEmailLinksEnabled()
    {

        $markdown = '<test@example.com>';
        $html = $this->parsedown->toHtml($markdown);

        $this->assertStringContainsString('<a href="mailto:test@example.com" target="_blank">test@example.com</a>', $html);
    }

    public function testEmailLinksDisabled()
    {

        $markdown = '<mailto:test@example.com>';
        $html = $this->parsedown->toHtml($markdown);

        $this->assertStringNotContainsString('<a href="mailto:test@example.com">test@example.com</a>', $html);
    }

    // External Links Settings
    // ----------------------------

    public function testExternalLinksEnabled()
    {

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedown->toHtml($markdown);

        $this->assertStringContainsString('<a href="https://www.google.com">External</a>', $html);
    }

    public function testExternalLinksDisabled()
    {

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedown->toHtml($markdown);

        $this->assertStringNotContainsString('<a href="https://www.google.com">External</a>', $html);
    }

    public function testNofollowEnabled()
    {

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedown->toHtml($markdown);

        $this->assertStringContainsString('rel="nofollow"', $html);
    }

    public function testNofollowDisabled()
    {

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedown->toHtml($markdown);

        $this->assertStringNotContainsString('rel="nofollow"', $html);
    }

    public function testNoopenerEnabled()
    {

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedown->toHtml($markdown);

        $this->assertStringContainsString('rel="noopener"', $html);
    }

    public function testNoopenerDisabled()
    {

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedown->toHtml($markdown);

        $this->assertStringNotContainsString('rel="noopener"', $html);
    }

    public function testNoreferrerEnabled()
    {

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedown->toHtml($markdown);

        $this->assertStringContainsString('rel="noreferrer"', $html);
    }

    public function testNoreferrerDisabled()
    {

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedown->toHtml($markdown);

        $this->assertStringNotContainsString('rel="noreferrer"', $html);
    }

    public function testOpenInNewWindowEnabled()
    {

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedown->toHtml($markdown);

        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testOpenInNewWindowDisabled()
    {

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedown->toHtml($markdown);

        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    // Internal and Same-Domain Links
    // ----------------------------


    public function testCustomInternalHostLink()
    {

        $markdown = '[Home](https://www.google.com)';
        $html = $this->parsedown->toHtml($markdown);


        $this->assertStringNotContainsString('noopener"', $html);
        $this->assertStringNotContainsString('nofollow', $html);
        $this->assertStringNotContainsString('noreferrer', $html);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    public function testSameDomainLinkWithoutNewWindow()
    {
        $markdown = '[Home](https://www.example.com)';
        $html = $this->parsedown->toHtml($markdown);

        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    public function testSameDomainLinkWithAttributes()
    {
        $markdown = '[Home](https://www.example.com)';
        $html = $this->parsedown->toHtml($markdown);

        $this->assertStringNotContainsString('nofollow"', $html);
        $this->assertStringNotContainsString('noopener"', $html);
        $this->assertStringNotContainsString('noreferrer"', $html);
    }

    // Markdown Edge Cases
    // ----------------------------

    // public function testMultipleLinksInText()
    // {
    //     $markdown = '[Google](https://www.google.com) and [Bing](https://www.bing.com)';
    //     $html = $this->parsedown->toHtml($markdown);
    //     $this->assertStringContainsString('href="https://www.google.com"', $html);
    //     $this->assertStringContainsString('href="https://www.bing.com"', $html);
    // }

    // public function testLinkWithSpecialCharacters()
    // {
    //     $markdown = '[Google](https://www.google.com/search?q=hello+world)';
    //     $html = $this->parsedown->toHtml($markdown);
    //     $this->assertStringContainsString('href="https://www.google.com/search?q=hello+world"', $html);
    // }

    // public function testNestedMarkdownElements()
    // {
    //     $markdown = '![Image](https://www.example.com/image.jpg) and [Link](https://www.example.com)';
    //     $html = $this->parsedown->toHtml($markdown);
    //     $this->assertStringContainsString('<img src="https://www.example.com/image.jpg"', $html);
    //     $this->assertStringContainsString('<a href="https://www.example.com">Link</a>', $html);
    // }
}
