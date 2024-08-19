<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class SmartypantsTest extends TestCase
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

    public function testEnableSmartypants()
    {
        $this->parsedownExtended->config()->set('smartypants', true);

        $markdown = <<<MARKDOWN
        "Hello," he said.
        MARKDOWN;

        $expected = <<<HTML
        <p>“Hello,” he said.</p>
        HTML;

        $this->assertEquals($expected, $this->parsedownExtended->text($markdown));
    }

    public function testDisableSmartypants()
    {
        $this->parsedownExtended->config()->set('smartypants', false);

        $markdown = <<<MARKDOWN
        "Hello," he said.
        MARKDOWN;

        $expected = <<<HTML
        <p>&quot;Hello,&quot; he said.</p>
        HTML;

        $this->assertEquals($expected, $this->parsedownExtended->text($markdown));
    }
}
