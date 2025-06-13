<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class AbbreviationsTest extends TestCase
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

    public function testEnabledAbbreviation()
    {
        $markdown = "*[CSS]: Cascading stylesheet\n\nCSS is an abbreviation";
        $expectedHtml = "<p><abbr title=\"Cascading stylesheet\">CSS</abbr> is an abbreviation</p>";

        $this->parsedownExtended->config()
        ->set('abbreviations', true)
        ->set('abbreviations.allow_custom', true);
        $this->assertEquals($expectedHtml, $this->parsedownExtended->text($markdown));
    }

    public function testDisabledAbbreviation()
    {
        $markdown = "*[CSS]: Cascading stylesheet\n\nCSS is an abbreviation";
        $expectedHtml = "<p>*[CSS]: Cascading stylesheet</p>\n<p>CSS is an abbreviation</p>";

        $this->parsedownExtended->config()->set('abbreviations', false);
        $this->assertEquals($expectedHtml, $this->parsedownExtended->text($markdown));
    }

    public function testDisabledCustomAbbreviation()
    {
        $markdown = "*[CSS]: Cascading stylesheet\n\nCSS is an abbreviation";
        $expectedHtml = "<p>*[CSS]: Cascading stylesheet</p>\n<p>CSS is an abbreviation</p>";

        $this->parsedownExtended->config()
        ->set('abbreviations', true)
        ->set('abbreviations.allow_custom', false);
        $this->assertEquals($expectedHtml, $this->parsedownExtended->text($markdown));
    }

    public function testPredefinedAbbreviations()
    {

        $markdown = "HTML is an abbreviation.";
        $expectedHtml = "<p><abbr title=\"HyperText Markup Language\">HTML</abbr> is an abbreviation.</p>";

        $this->parsedownExtended->config()->set('abbreviations', true);
        $this->parsedownExtended->config()->set('abbreviations.predefined', ['HTML' => 'HyperText Markup Language']);

        $this->assertEquals($expectedHtml, $this->parsedownExtended->text($markdown));
    }
}
