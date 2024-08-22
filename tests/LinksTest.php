<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class LinksTest extends TestCase
{
    private ParsedownExtended $parsedownExtended;

    protected function setUp(): void
    {
        $this->parsedownExtended = new ParsedownExtended();
        $this->parsedownExtended->setSafeMode(true);
        $_SERVER['HTTP_HOST'] = 'www.example.com';  // Default host for testing
    }

    protected function tearDown(): void
    {
        unset($this->parsedownExtended);
    }

    // General Link Settings
    // ----------------------------

    public function testLinksEnabled()
    {
        $this->parsedownExtended->config()->set('links.enabled', true);

        $markdown = '[Link](https://www.example.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringContainsString('<a href="https://www.example.com">Link</a>', $html);
    }

    public function testLinksDisabled()
    {
        $this->parsedownExtended->config()->set('links.enabled', false);

        $markdown = '[Link](https://www.example.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringNotContainsString('<a href="https://www.example.com">Link</a>', $html);
    }

    // Email Links
    // ----------------------------

    public function testEmailLinksEnabled()
    {
        $this->parsedownExtended->config()->set('links.email_links', true);

        $markdown = '<test@example.com>';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringContainsString('<a href="mailto:test@example.com" target="_blank">test@example.com</a>', $html);
    }

    public function testEmailLinksDisabled()
    {
        $this->parsedownExtended->config()->set('links.email_links', false);

        $markdown = '<mailto:test@example.com>';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringNotContainsString('<a href="mailto:test@example.com">test@example.com</a>', $html);
    }

    // External Links Settings
    // ----------------------------

    public function testExternalLinksEnabled()
    {
        $this->parsedownExtended->config()->set('links.external_links', true);
        $this->parsedownExtended->config()->set('links.external_links.nofollow', false);
        $this->parsedownExtended->config()->set('links.external_links.noopener', false);
        $this->parsedownExtended->config()->set('links.external_links.noreferrer', false);
        $this->parsedownExtended->config()->set('links.external_links.open_in_new_window', false);

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringContainsString('<a href="https://www.google.com">External</a>', $html);
    }

    public function testExternalLinksDisabled()
    {
        $this->parsedownExtended->config()->set('links.external_links', false);

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringNotContainsString('<a href="https://www.google.com">External</a>', $html);
    }

    public function testNofollowEnabled()
    {
        $this->parsedownExtended->config()->set('links.external_links', true);
        $this->parsedownExtended->config()->set('links.external_links.nofollow', true);
        $this->parsedownExtended->config()->set('links.external_links.noopener', false);
        $this->parsedownExtended->config()->set('links.external_links.noreferrer', false);
        $this->parsedownExtended->config()->set('links.external_links.open_in_new_window', false);

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringContainsString('rel="nofollow"', $html);
    }

    public function testNofollowDisabled()
    {
        $this->parsedownExtended->config()->set('links.external_links', true);
        $this->parsedownExtended->config()->set('links.external_links.nofollow', false);
        $this->parsedownExtended->config()->set('links.external_links.noopener', false);
        $this->parsedownExtended->config()->set('links.external_links.noreferrer', false);
        $this->parsedownExtended->config()->set('links.external_links.open_in_new_window', false);

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringNotContainsString('rel="nofollow"', $html);
    }

    public function testNoopenerEnabled()
    {
        $this->parsedownExtended->config()->set('links.external_links', true);
        $this->parsedownExtended->config()->set('links.external_links.nofollow', false);
        $this->parsedownExtended->config()->set('links.external_links.noopener', true);
        $this->parsedownExtended->config()->set('links.external_links.noreferrer', false);
        $this->parsedownExtended->config()->set('links.external_links.open_in_new_window', false);

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringContainsString('rel="noopener"', $html);
    }

    public function testNoopenerDisabled()
    {
        $this->parsedownExtended->config()->set('links.external_links', true);
        $this->parsedownExtended->config()->set('links.external_links.nofollow', false);
        $this->parsedownExtended->config()->set('links.external_links.noopener', false);
        $this->parsedownExtended->config()->set('links.external_links.noreferrer', false);
        $this->parsedownExtended->config()->set('links.external_links.open_in_new_window', false);

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringNotContainsString('rel="noopener"', $html);
    }

    public function testNoreferrerEnabled()
    {
        $this->parsedownExtended->config()->set('links.external_links', true);
        $this->parsedownExtended->config()->set('links.external_links.nofollow', false);
        $this->parsedownExtended->config()->set('links.external_links.noopener', false);
        $this->parsedownExtended->config()->set('links.external_links.noreferrer', true);
        $this->parsedownExtended->config()->set('links.external_links.open_in_new_window', false);

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringContainsString('rel="noreferrer"', $html);
    }

    public function testNoreferrerDisabled()
    {
        $this->parsedownExtended->config()->set('links.external_links', true);
        $this->parsedownExtended->config()->set('links.external_links.nofollow', false);
        $this->parsedownExtended->config()->set('links.external_links.noopener', false);
        $this->parsedownExtended->config()->set('links.external_links.noreferrer', false);
        $this->parsedownExtended->config()->set('links.external_links.open_in_new_window', false);

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringNotContainsString('rel="noreferrer"', $html);
    }

    public function testOpenInNewWindowEnabled()
    {
        $this->parsedownExtended->config()->set('links.external_links', true);
        $this->parsedownExtended->config()->set('links.external_links.nofollow', false);
        $this->parsedownExtended->config()->set('links.external_links.noopener', false);
        $this->parsedownExtended->config()->set('links.external_links.noreferrer', false);
        $this->parsedownExtended->config()->set('links.external_links.open_in_new_window', true);

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testOpenInNewWindowDisabled()
    {
        $this->parsedownExtended->config()->set('links.external_links', true);
        $this->parsedownExtended->config()->set('links.external_links.nofollow', false);
        $this->parsedownExtended->config()->set('links.external_links.noopener', false);
        $this->parsedownExtended->config()->set('links.external_links.noreferrer', false);
        $this->parsedownExtended->config()->set('links.external_links.open_in_new_window', false);

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    // Internal and Same-Domain Links
    // ----------------------------


    public function testCustomInternalHostLink()
    {
        $this->parsedownExtended->config()->set('links.external_links.internal_hosts', ['google.com']);

        $markdown = '[Home](https://www.google.com)';
        $html = $this->parsedownExtended->text($markdown);


        $this->assertStringNotContainsString('noopener"', $html);
        $this->assertStringNotContainsString('nofollow', $html);
        $this->assertStringNotContainsString('noreferrer', $html);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    public function testSameDomainLinkWithoutNewWindow()
    {
        $markdown = '[Home](https://www.example.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    public function testSameDomainLinkWithAttributes()
    {
        $markdown = '[Home](https://www.example.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringNotContainsString('nofollow"', $html);
        $this->assertStringNotContainsString('noopener"', $html);
        $this->assertStringNotContainsString('noreferrer"', $html);
    }

    // Markdown Edge Cases
    // ----------------------------

    // public function testMultipleLinksInText()
    // {
    //     $markdown = '[Google](https://www.google.com) and [Bing](https://www.bing.com)';
    //     $html = $this->parsedownExtended->text($markdown);
    //     $this->assertStringContainsString('href="https://www.google.com"', $html);
    //     $this->assertStringContainsString('href="https://www.bing.com"', $html);
    // }

    // public function testLinkWithSpecialCharacters()
    // {
    //     $markdown = '[Google](https://www.google.com/search?q=hello+world)';
    //     $html = $this->parsedownExtended->text($markdown);
    //     $this->assertStringContainsString('href="https://www.google.com/search?q=hello+world"', $html);
    // }

    // public function testNestedMarkdownElements()
    // {
    //     $markdown = '![Image](https://www.example.com/image.jpg) and [Link](https://www.example.com)';
    //     $html = $this->parsedownExtended->text($markdown);
    //     $this->assertStringContainsString('<img src="https://www.example.com/image.jpg"', $html);
    //     $this->assertStringContainsString('<a href="https://www.example.com">Link</a>', $html);
    // }
}
