<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class DiagramsTest extends TestCase
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

    public function testEnableDiagram()
    {
        $this->parsedownExtended->config()->set('diagrams', true);

        $markdown = "```mermaid\ngraph TD;\n    A-->B;\n```";
        $expectedHtml = "<div class=\"mermaid\">graph TD;\n    A--&gt;B;</div>";

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testDisableDiagram()
    {
        $this->parsedownExtended->config()->set('diagrams', false);

        $markdown = "```mermaid\ngraph TD;\n    A-->B;\n```";
        $expectedHtml = "<pre><code class=\"language-mermaid\">graph TD;\n    A--&gt;B;</code></pre>";

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testDisableMermaidDiagramType()
    {
        $this->parsedownExtended->config()->set('diagrams', true);
        $this->parsedownExtended->config()->set('diagrams.mermaid', false);

        $markdown = "```mermaid\ngraph TD;\n    A-->B;\n```";
        $expectedHtml = "<pre><code class=\"language-mermaid\">graph TD;\n    A--&gt;B;</code></pre>";

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testDisableChartjsDiagramType()
    {
        $this->parsedownExtended->config()->set('diagrams', true);
        $this->parsedownExtended->config()->set('diagrams.chartjs', false);

        $markdown = "```chart\n{\"type\":\"line\",\"data\":{}}\n```";
        $expectedHtml = "<pre><code class=\"language-chart\">{\"type\":\"line\",\"data\":{}}</code></pre>";

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testDisableChartjsDiagramTypeForChartjsFenceAlias()
    {
        $this->parsedownExtended->config()->set('diagrams', true);
        $this->parsedownExtended->config()->set('diagrams.chartjs', false);

        $markdown = "```chartjs\n{\"type\":\"line\",\"data\":{}}\n```";
        $expectedHtml = "<pre><code class=\"language-chartjs\">{\"type\":\"line\",\"data\":{}}</code></pre>";

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }
}
