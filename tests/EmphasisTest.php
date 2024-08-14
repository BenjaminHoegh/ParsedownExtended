<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class EmphasisTest extends TestCase
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

    public function testInlineBold()
    {
        $markdown = "**bold**";
        $expectedHtml = "<p><strong>bold</strong></p>";

        $this->parsedownExtended->config()
        ->set('emphasis', true)
        ->set('emphasis.bold', true);
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineItalic()
    {
        $markdown = "*italic*";
        $expectedHtml = "<p><em>italic</em></p>";

        $this->parsedownExtended->config()
        ->set('emphasis', true)
        ->set('emphasis.italic', true);
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineMark()
    {
        $markdown = "==marked==";
        $expectedHtml = "<p><mark>marked</mark></p>";

        $this->parsedownExtended->config()
        ->set('emphasis', true)
        ->set('emphasis.mark', true);
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineStrikethrough()
    {
        $markdown = "~~strikethrough~~";
        $expectedHtml = "<p><del>strikethrough</del></p>";

        $this->parsedownExtended->config()
        ->set('emphasis', true)
        ->set('emphasis.strikethroughs', true);
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineInserted()
    {
        $markdown = "++inserted++";
        $expectedHtml = "<p><ins>inserted</ins></p>";

        $this->parsedownExtended->config()
        ->set('emphasis', true)
        ->set('emphasis.insertions', true);
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineSuperscript()
    {
        $markdown = "X^2^";
        $expectedHtml = "<p>X<sup>2</sup></p>";

        $this->parsedownExtended->config()
        ->set('emphasis', true)
        ->set('emphasis.superscript', true);
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineSubscript()
    {
        $markdown = "sub~script~";
        $expectedHtml = "<p>sub<sub>script</sub></p>";

        $this->parsedownExtended->config()
        ->set('emphasis', true)
        ->set('emphasis.subscript', true);
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineKeystroke()
    {
        $markdown = "Press [[Ctrl]]>+[[C]] to copy";
        $expectedHtml = "<p>Press <kbd>Ctrl</kbd>&gt;+<kbd>C</kbd> to copy</p>";

        $this->parsedownExtended->config()
        ->set('emphasis', true)
        ->set('emphasis.keystrokes', true);
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }
}
