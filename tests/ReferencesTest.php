<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class ReferencesTest extends TestCase
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

    public function testReferencesEnabled()
    {
        $this->parsedownExtended->config()->set('references', true);

        $markdown = "[link text][ref]\n\n[ref]: https://example.com";
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringContainsString('<a href="https://example.com">link text</a>', $html);
    }

    public function testReferencesDisabled()
    {
        $this->parsedownExtended->config()->set('references', false);

        $markdown = "[link text][ref]\n\n[ref]: https://example.com";
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringNotContainsString('<a href="https://example.com">link text</a>', $html);
    }

    public function testReferenceWithTitle()
    {
        $this->parsedownExtended->config()->set('references', true);

        $markdown = "[link text][ref]\n\n[ref]: https://example.com \"Example Title\"";
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringContainsString('href="https://example.com"', $html);
        $this->assertStringContainsString('title="Example Title"', $html);
    }
}
