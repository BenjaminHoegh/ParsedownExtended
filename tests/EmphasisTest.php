<?php

use Erusev\Parsedown\Parsedown;
use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class EmphasisTest extends TestCase
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

    public function testInlineBoldUsingAsterisks()
    {
        $markdown = "**bold**";
        $expectedHtml = "<p><strong>bold</strong></p>";

        ->set('emphasis', true)
        ->set('emphasis.bold', true);
        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineBoldUsingAsterisksDisabled()
    {
        $markdown = "**bold**";
        $expectedHtml = "<p>**bold**</p>";

        ->set('emphasis', true)
        ->set('emphasis.bold', false);
        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineItalicUsingAsterisks()
    {
        $markdown = "*italic*";
        $expectedHtml = "<p><em>italic</em></p>";

        ->set('emphasis', true)
        ->set('emphasis.italic', true);
        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineItalicUsingAsterisksDisabled()
    {
        $markdown = "*italic*";
        $expectedHtml = "<p>*italic*</p>";

        ->set('emphasis', true)
        ->set('emphasis.italic', false);
        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineBoldUsingUnderscores()
    {
        $markdown = "__bold__";
        $expectedHtml = "<p><strong>bold</strong></p>";

        ->set('emphasis', true)
        ->set('emphasis.bold', true);
        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineBoldUsingUnderscoresDisabled()
    {
        $markdown = "__bold__";
        $expectedHtml = "<p>__bold__</p>";

        ->set('emphasis', true)
        ->set('emphasis.bold', false);
        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineItalicUsingUnderscores()
    {
        $markdown = "_italic_";
        $expectedHtml = "<p><em>italic</em></p>";

        ->set('emphasis', true)
        ->set('emphasis.italic', true);
        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineItalicUsingUnderscoresDisabled()
    {
        $markdown = "_italic_";
        $expectedHtml = "<p>_italic_</p>";

        ->set('emphasis', true)
        ->set('emphasis.italic', false);
        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineMark()
    {
        $markdown = "==marked==";
        $expectedHtml = "<p><mark>marked</mark></p>";

        ->set('emphasis', true)
        ->set('emphasis.mark', true);
        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineMarkDisabled()
    {
        $markdown = "==marked==";
        $expectedHtml = "<p>==marked==</p>";

        ->set('emphasis', true)
        ->set('emphasis.mark', false);
        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineStrikethrough()
    {
        $markdown = "~~strikethrough~~";
        $expectedHtml = "<p><del>strikethrough</del></p>";

        ->set('emphasis', true)
        ->set('emphasis.strikethroughs', true);
        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineStrikethroughDisabled()
    {
        $markdown = "~~strikethrough~~";
        $expectedHtml = "<p>~~strikethrough~~</p>";

        ->set('emphasis', true)
        ->set('emphasis.strikethroughs', false);
        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineInserted()
    {
        $markdown = "++inserted++";
        $expectedHtml = "<p><ins>inserted</ins></p>";

        ->set('emphasis', true)
        ->set('emphasis.insertions', true);
        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineInsertedDisabled()
    {
        $markdown = "++inserted++";
        $expectedHtml = "<p>++inserted++</p>";

        ->set('emphasis', true)
        ->set('emphasis.insertions', false);
        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineSuperscript()
    {
        $markdown = "X^2^";
        $expectedHtml = "<p>X<sup>2</sup></p>";

        ->set('emphasis', true)
        ->set('emphasis.superscript', true);
        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineSuperscriptDisabled()
    {
        $markdown = "X^2^";
        $expectedHtml = "<p>X^2^</p>";

        ->set('emphasis', true)
        ->set('emphasis.superscript', false);
        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineSubscript()
    {
        $markdown = "sub~script~";
        $expectedHtml = "<p>sub<sub>script</sub></p>";

        ->set('emphasis', true)
        ->set('emphasis.subscript', true);
        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineSubscriptDisabled()
    {
        $markdown = "sub~script~";
        $expectedHtml = "<p>sub~script~</p>";

        ->set('emphasis', true)
        ->set('emphasis.subscript', false);
        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineKeystroke()
    {
        $markdown = "Press [[Ctrl]]>+[[C]] to copy";
        $expectedHtml = "<p>Press <kbd>Ctrl</kbd>&gt;+<kbd>C</kbd> to copy</p>";

        ->set('emphasis', true)
        ->set('emphasis.keystrokes', true);
        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineKeystrokeDisabled()
    {
        $markdown = "Press [[Ctrl]]>+[[C]] to copy";
        $expectedHtml = "<p>Press [[Ctrl]]&gt;+[[C]] to copy</p>";

        ->set('emphasis', true)
        ->set('emphasis.keystrokes', false);
        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }
}
