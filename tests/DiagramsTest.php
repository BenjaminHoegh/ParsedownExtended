<?php

use Erusev\Parsedown\Parsedown;
use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class DiagramsTest extends TestCase
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

    public function testEnableDiagram()
    {

        $markdown = "```mermaid\ngraph TD;\n    A-->B;\n```";
        $expectedHtml = "<div class=\"mermaid\">graph TD;\n    A-->B;</div>";

        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testDisableDiagram()
    {

        $markdown = "```mermaid\ngraph TD;\n    A-->B;\n```";
        $expectedHtml = "<pre><code class=\"language-mermaid\">graph TD;\n    A--&gt;B;</code></pre>";

        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }
}
