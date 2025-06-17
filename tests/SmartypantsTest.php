<?php

use Erusev\Parsedown\Parsedown;
use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class SmartypantsTest extends TestCase
{
    protected Parsedown $parsedown;

    protected function setUp(): void
    {
        $this->parsedown = new Parsedown(new ParsedownExtended());
    }

    protected function tearDown(): void
    {
        unset($this->parsedown);
    }

    public function testEnableSmartypants()
    {

        $markdown = <<<MARKDOWN
            "Hello," he said.
            MARKDOWN;

        $expected = <<<HTML
            <p>“Hello,” he said.</p>
            HTML;

        $this->assertEquals($expected, $this->parsedown->toHtml($markdown));
    }

    public function testDisableSmartypants()
    {

        $markdown = <<<MARKDOWN
            "Hello," he said.
            MARKDOWN;

        $expected = <<<HTML
            <p>&quot;Hello,&quot; he said.</p>
            HTML;

        $this->assertEquals($expected, $this->parsedown->toHtml($markdown));
    }
}
