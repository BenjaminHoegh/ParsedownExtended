<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class LinksTest extends TestCase
{
    private ParsedownExtended $ParsedownExtended;

    protected function setUp(): void
    {
        $this->ParsedownExtended = new ParsedownExtended();
        $_SERVER['HTTP_HOST'] = 'www.example.com';  // Set the current domain for testing
    }

    // Tests for External Links

    public function testExternalLinkWithNewWindow()
    {
        $this->ParsedownExtended->config()->set('links.ext_in_new_window', true);
        $markdown = '[Google](https://www.google.com)';
        $html = $this->ParsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testExternalLinkWithoutNewWindow()
    {
        $this->ParsedownExtended->config()->set('links.ext_in_new_window', false);
        $markdown = '[Google](https://www.google.com)';
        $html = $this->ParsedownExtended->text($markdown);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    // Tests for Relative Links

    public function testRelativeLinkWithNewWindow()
    {
        $this->ParsedownExtended->config()->set('links.all_in_new_window', true);
        $markdown = '[Home](/home)';
        $html = $this->ParsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testRelativeLinkWithoutNewWindow()
    {
        $this->ParsedownExtended->config()->set('links.all_in_new_window', false);
        $markdown = '[Home](/home)';
        $html = $this->ParsedownExtended->text($markdown);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    // Tests for Protocol-Relative Links

    public function testProtocolRelativeLinkWithNewWindow()
    {
        $this->ParsedownExtended->config()->set('links.ext_in_new_window', true);
        $markdown = '[Google](//www.google.com)';
        $html = $this->ParsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testProtocolRelativeLinkWithoutNewWindow()
    {
        $this->ParsedownExtended->config()->set('links.ext_in_new_window', false);
        $markdown = '[Google](//www.google.com)';
        $html = $this->ParsedownExtended->text($markdown);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    // Tests for Mailto Links

    public function testMailtoLinkWithNewWindowDefault()
    {
        $markdown = '[Email](mailto:test@example.com)';
        $html = $this->ParsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html); // Mailto links always open in new window
    }

    // Tests for Auto-Detected Links

    public function testAutoDetectedUrlWithNewWindowDefault()
    {
        $this->ParsedownExtended->config()->set('links.ext_in_new_window', true);
        $markdown = 'Visit https://www.google.com for more information.';
        $html = $this->ParsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testAutoDetectedUrlWithoutNewWindow()
    {
        $this->ParsedownExtended->config()->set('links.ext_in_new_window', false);
        $markdown = 'Visit https://www.google.com for more information.';
        $html = $this->ParsedownExtended->text($markdown);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    // Tests for tag links
    public function testTagUrlLinkWithNewWindow()
    {
        $this->ParsedownExtended->config()->set('links.ext_in_new_window', true);
        $markdown = '<https://www.google.com>';
        $html = $this->ParsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testTagUrlLinkWithoutNewWindow()
    {
        $this->ParsedownExtended->config()->set('links.ext_in_new_window', false);
        $markdown = '<https://www.google.com>';
        $html = $this->ParsedownExtended->text($markdown);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    // Tests for Edge Cases

    public function testLinkWithNoTextButOnlyUrlWithNewWindow()
    {
        $this->ParsedownExtended->config()->set('links.ext_in_new_window', true);
        $markdown = '<https://www.google.com>';
        $html = $this->ParsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testLinkWithNoTextButOnlyUrlWithoutNewWindow()
    {
        $this->ParsedownExtended->config()->set('links.ext_in_new_window', false);
        $markdown = '<https://www.google.com>';
        $html = $this->ParsedownExtended->text($markdown);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    public function testLinkWithSpecialCharactersWithNewWindow()
    {
        $this->ParsedownExtended->config()->set('links.ext_in_new_window', true);
        $markdown = '[Google](https://www.google.com/search?q=hello+world)';
        $html = $this->ParsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testLinkWithSpecialCharactersWithoutNewWindow()
    {
        $this->ParsedownExtended->config()->set('links.ext_in_new_window', false);
        $markdown = '[Google](https://www.google.com/search?q=hello+world)';
        $html = $this->ParsedownExtended->text($markdown);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    public function testMultipleLinksInOneTextWithNewWindow()
    {
        $this->ParsedownExtended->config()->set('links.ext_in_new_window', true);
        $markdown = '[Google](https://www.google.com) and [Bing](https://www.bing.com)';
        $html = $this->ParsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testMultipleLinksInOneTextWithoutNewWindow()
    {
        $this->ParsedownExtended->config()->set('links.ext_in_new_window', false);
        $markdown = '[Google](https://www.google.com) and [Bing](https://www.bing.com)';
        $html = $this->ParsedownExtended->text($markdown);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    public function testRelativeAndExternalLinksCombined()
    {
        $this->ParsedownExtended->config()->set('links.ext_in_new_window', true);
        $markdown = '[Google](https://www.google.com) and [Home](/home)';
        $html = $this->ParsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html);
        $this->assertStringNotContainsString('<a href="/home" target="_blank">Home</a>', $html);
    }

    public function testAllLinksInNewWindow()
    {
        $this->ParsedownExtended->config()->set('links.all_in_new_window', true);
        $markdown = '[Google](https://www.google.com) and [Home](/home)';
        $html = $this->ParsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    // Test for Same Domain Links with Different Protocols
    public function testSameDomainHttpLinkWithNewWindow()
    {
        $this->ParsedownExtended->config()->set('links.ext_in_new_window', true);
        $markdown = '[Example](http://www.example.com)';
        $html = $this->ParsedownExtended->text($markdown);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    public function testSameDomainHttpsLinkWithNewWindow()
    {
        $this->ParsedownExtended->config()->set('links.ext_in_new_window', true);
        $markdown = '[Example](https://www.example.com)';
        $html = $this->ParsedownExtended->text($markdown);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    public function testSameDomainHttpLinkWithNewWindowSettingAll()
    {
        $this->ParsedownExtended->config()->set('links.all_in_new_window', true);
        $markdown = '[Example](http://www.example.com)';
        $html = $this->ParsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testSameDomainHttpsLinkWithNewWindowSettingAll()
    {
        $this->ParsedownExtended->config()->set('links.all_in_new_window', true);
        $markdown = '[Example](https://www.example.com)';
        $html = $this->ParsedownExtended->text($markdown);
        $this->assertStringContainsString('target="_blank"', $html);
    }
}
