<?php

use Erusev\Parsedown\State;
use Erusev\Parsedown\Parsedown;
use Erusev\ParsedownExtra\ParsedownExtra;
use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class DiagramsTest extends TestCase
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

    public function testEnableDiagram()
    {
        $this->parsedownExtended->config()->set('diagrams', true);

        $markdown = "```mermaid\ngraph TD;\n    A-->B;\n```";
        $expectedHtml = "<div class=\"mermaid\">graph TD;\n    A-->B;</div>";

        $result = $this->parsedownExtended->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testDisableDiagram()
    {
        $this->parsedownExtended->config()->set('diagrams', false);

        $markdown = "```mermaid\ngraph TD;\n    A-->B;\n```";
        $expectedHtml = "<pre><code class=\"language-mermaid\">graph TD;\n    A--&gt;B;</code></pre>";

        $result = $this->parsedownExtended->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testTildeFencedDiagram()
    {
        $this->parsedownExtended->config()->set('diagrams', true);

        $markdown = "~~~mermaid\ngraph TD;\n    A-->B;\n~~~";
        $expectedHtml = "<div class=\"mermaid\">graph TD;\n    A-->B;</div>";

        $result = $this->parsedownExtended->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testLongFenceDiagram()
    {
        $this->parsedownExtended->config()->set('diagrams', true);

        $markdown = "~~~~mermaid\ngraph TD;\n    A-->B;\n~~~~~~   \n";
        $expectedHtml = "<div class=\"mermaid\">graph TD;\n    A-->B;</div>";

        $result = $this->parsedownExtended->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }
}
