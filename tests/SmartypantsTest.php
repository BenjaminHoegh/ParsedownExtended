<?php

use Erusev\Parsedown\State;
use Erusev\Parsedown\Parsedown;
use Erusev\ParsedownExtra\ParsedownExtra;
use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class SmartypantsTest extends TestCase
{
    protected Parsedown $parsedownExtended;

    protected function setUp(): void
    {
        $this->parsedownExtended = new Parsedown(ParsedownExtended::from(new State()));

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

        $this->assertEquals($expected, $this->parsedownExtended->toHtml($markdown));
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

        $this->assertEquals($expected, $this->parsedownExtended->toHtml($markdown));
    }
}
