<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class LinksTest extends TestCase
{
    private ParsedownExtended $parsedownExtended;

    protected function setUp(): void
    {
        $this->parsedownExtended = new ParsedownExtended();
        $this->parsedownExtended->setSafeMode(true); // As we always want to support safe mode
        $_SERVER['HTTP_HOST'] = 'www.example.com';  // Set the current domain for testing
    }

    protected function tearDown(): void
    {
        unset($this->parsedownExtended);
    }


    // Tests for External Links

    public function testExternalLinkWithNewWindow()
    {
        $this->parsedownExtended->config()->set('links.ext_in_new_window', true);
        $markdown = '[Google](https://www.google.com)';
        $html = $this->parsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testExternalLinkWithoutNewWindow()
    {
        $this->parsedownExtended->config()->set('links.ext_in_new_window', false);
        $markdown = '[Google](https://www.google.com)';
        $html = $this->parsedownExtended->text($markdown);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    // Tests for Relative Links

    public function testRelativeLinkWithNewWindow()
    {
        $this->parsedownExtended->config()->set('links.all_in_new_window', true);
        $markdown = '[Home](/home)';
        $html = $this->parsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testRelativeLinkWithoutNewWindow()
    {
        $this->parsedownExtended->config()->set('links.all_in_new_window', false);
        $markdown = '[Home](/home)';
        $html = $this->parsedownExtended->text($markdown);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    // Tests for Protocol-Relative Links

    public function testProtocolRelativeLinkWithNewWindow()
    {
        $this->parsedownExtended->config()->set('links.ext_in_new_window', true);
        $markdown = '[Google](//www.google.com)';
        $html = $this->parsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testProtocolRelativeLinkWithoutNewWindow()
    {
        $this->parsedownExtended->config()->set('links.ext_in_new_window', false);
        $markdown = '[Google](//www.google.com)';
        $html = $this->parsedownExtended->text($markdown);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    // Tests for Mailto Links

    public function testMailtoLinkWithNewWindowDefault()
    {
        $markdown = '[Email](mailto:test@example.com)';
        $html = $this->parsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html); // Mailto links always open in new window
    }

    // Tests for Auto-Detected Links

    public function testAutoDetectedUrlWithNewWindowDefault()
    {
        $this->parsedownExtended->config()->set('links.ext_in_new_window', true);
        $markdown = 'Visit https://www.google.com for more information.';
        $html = $this->parsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testAutoDetectedUrlWithoutNewWindow()
    {
        $this->parsedownExtended->config()->set('links.ext_in_new_window', false);
        $markdown = 'Visit https://www.google.com for more information.';
        $html = $this->parsedownExtended->text($markdown);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    // Tests for tag links
    public function testTagUrlLinkWithNewWindow()
    {
        $this->parsedownExtended->config()->set('links.ext_in_new_window', true);
        $markdown = '<https://www.google.com>';
        $html = $this->parsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testTagUrlLinkWithoutNewWindow()
    {
        $this->parsedownExtended->config()->set('links.ext_in_new_window', false);
        $markdown = '<https://www.google.com>';
        $html = $this->parsedownExtended->text($markdown);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    // Tests for Edge Cases

    public function testLinkWithNoTextButOnlyUrlWithNewWindow()
    {
        $this->parsedownExtended->config()->set('links.ext_in_new_window', true);
        $markdown = '<https://www.google.com>';
        $html = $this->parsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testLinkWithNoTextButOnlyUrlWithoutNewWindow()
    {
        $this->parsedownExtended->config()->set('links.ext_in_new_window', false);
        $markdown = '<https://www.google.com>';
        $html = $this->parsedownExtended->text($markdown);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    public function testLinkWithSpecialCharactersWithNewWindow()
    {
        $this->parsedownExtended->config()->set('links.ext_in_new_window', true);
        $markdown = '[Google](https://www.google.com/search?q=hello+world)';
        $html = $this->parsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testLinkWithSpecialCharactersWithoutNewWindow()
    {
        $this->parsedownExtended->config()->set('links.ext_in_new_window', false);
        $markdown = '[Google](https://www.google.com/search?q=hello+world)';
        $html = $this->parsedownExtended->text($markdown);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    public function testMultipleLinksInOneTextWithNewWindow()
    {
        $this->parsedownExtended->config()->set('links.ext_in_new_window', true);
        $markdown = '[Google](https://www.google.com) and [Bing](https://www.bing.com)';
        $html = $this->parsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testMultipleLinksInOneTextWithoutNewWindow()
    {
        $this->parsedownExtended->config()->set('links.ext_in_new_window', false);
        $markdown = '[Google](https://www.google.com) and [Bing](https://www.bing.com)';
        $html = $this->parsedownExtended->text($markdown);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    public function testRelativeAndExternalLinksCombined()
    {
        $this->parsedownExtended->config()->set('links.ext_in_new_window', true);
        $markdown = '[Google](https://www.google.com) and [Home](/home)';
        $html = $this->parsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html);
        $this->assertStringNotContainsString('<a href="/home" target="_blank">Home</a>', $html);
    }

    public function testAllLinksInNewWindow()
    {
        $this->parsedownExtended->config()->set('links.all_in_new_window', true);
        $markdown = '[Google](https://www.google.com) and [Home](/home)';
        $html = $this->parsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    // Test for Same Domain Links with Different Protocols
    public function testSameDomainHttpLinkWithNewWindow()
    {
        $this->parsedownExtended->config()->set('links.ext_in_new_window', true);
        $markdown = '[Example](http://www.example.com)';
        $html = $this->parsedownExtended->text($markdown);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    public function testSameDomainHttpsLinkWithNewWindow()
    {
        $this->parsedownExtended->config()->set('links.ext_in_new_window', true);
        $markdown = '[Example](https://www.example.com)';
        $html = $this->parsedownExtended->text($markdown);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    public function testSameDomainHttpLinkWithNewWindowSettingAll()
    {
        $this->parsedownExtended->config()->set('links.all_in_new_window', true);
        $markdown = '[Example](http://www.example.com)';
        $html = $this->parsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testSameDomainHttpsLinkWithNewWindowSettingAll()
    {
        $this->parsedownExtended->config()->set('links.all_in_new_window', true);
        $markdown = '[Example](https://www.example.com)';
        $html = $this->parsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html);
    }
}
