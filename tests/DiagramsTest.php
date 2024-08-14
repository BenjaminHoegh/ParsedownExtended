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

    public function testBlockDiagram()
    {
        $markdown = "```mermaid\ngraph TD;\n    A-->B;\n```";
        $expectedHtml = "<div class=\"mermaid\">graph TD;\n    A-->B;</div>";

        $this->parsedownExtended->config()->set('diagrams', true);
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }
}
