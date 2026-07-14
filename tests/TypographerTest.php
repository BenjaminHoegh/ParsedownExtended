<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class TypographerTest extends TestCase
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

    public function testEnableTypographer(): void
    {
        $this->parsedownExtended->config()->set('typographer', true);

        $markdown = '(c) (r) (tm) ....';
        $expected = '<p>© ® ™ ...</p>';

        $result = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $result);
    }

    public function testDisableTypographer(): void
    {
        $this->parsedownExtended->config()->set('typographer', false);

        $markdown = '(c) (r) (tm) ....';
        $expected = '<p>(c) (r) (tm) ....</p>';

        $result = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $result);
    }

    public function testTypographerWithSmartypants(): void
    {
        $this->parsedownExtended->config()->set('typographer', true);
        $this->parsedownExtended->config()->set('smartypants', true);

        $markdown = '(c) (r) (tm) ...';
        $expected = '<p>© ® ™ …</p>';

        $result = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $result);
    }

    public function testTypographerCanBeDisabledAfterParsing(): void
    {
        $markdown = '(c)';

        $this->parsedownExtended->config()->set('typographer', true);
        $this->assertEquals('<p>©</p>', $this->parsedownExtended->text($markdown));

        $this->parsedownExtended->config()->set('typographer', false);
        $this->assertEquals('<p>(c)</p>', $this->parsedownExtended->text($markdown));
    }

    public function testTypographerDoesNotConsumeRemainingInlineMarkdown(): void
    {
        $this->parsedownExtended->config()->set('typographer', true);

        $result = $this->parsedownExtended->text('(c) **bold** and [link](https://example.com)');

        $this->assertStringContainsString(
            '© <strong>bold</strong> and <a href="https://example.com"',
            $result
        );
        $this->assertStringContainsString('>link</a>', $result);
        $this->assertStringNotContainsString('**bold**', $result);
    }
}
