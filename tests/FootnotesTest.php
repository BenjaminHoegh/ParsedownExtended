<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class FootnotesTest extends TestCase
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

    public function testFootnotesEnabled()
    {
        $this->parsedownExtended->config()->set('footnotes', true);

        $markdown = "Text with a footnote.[^1]\n\n[^1]: The footnote content.";
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringContainsString('<sup', $html);
        $this->assertStringContainsString('The footnote content.', $html);
    }

    public function testFootnotesDisabled()
    {
        $this->parsedownExtended->config()->set('footnotes', false);

        $markdown = "Text with a footnote.[^1]\n\n[^1]: The footnote content.";
        $html = $this->parsedownExtended->text($markdown);

        // When footnotes are disabled there should be no superscript footnote links
        $this->assertStringNotContainsString('<sup', $html);
        // The footnote definition is not processed as a footnote, so no back-reference link appears
        $this->assertStringNotContainsString('href="#fn:', $html);
    }

    public function testFootnotesDisabledDoesNotTreatFootnoteDefinitionsAsReferences()
    {
        $this->parsedownExtended->config()->set('footnotes', false);
        $this->parsedownExtended->config()->set('references', true);

        $markdown = "Text[^1]\n\n[^1]: Note.";
        $expected = "<p>Text[^1]</p>\n<p>[^1]: Note.</p>";

        $this->assertEquals($expected, $this->parsedownExtended->text($markdown));
    }

    public function testFootnoteConfigChangesAffectLaterParses()
    {
        $markdown = "Text[^1]\n\n[^1]: Note.";
        $disabledExpected = "<p>Text[^1]</p>\n<p>[^1]: Note.</p>";

        $this->parsedownExtended->config()->set('footnotes', false);
        $this->assertEquals($disabledExpected, $this->parsedownExtended->text($markdown));

        $this->parsedownExtended->config()->set('footnotes', true);
        $this->assertStringContainsString('<sup', $this->parsedownExtended->text($markdown));

        $this->parsedownExtended->config()->set('footnotes', false);
        $this->assertEquals($disabledExpected, $this->parsedownExtended->text($markdown));
    }

    public function testMultipleFootnotes()
    {
        $this->parsedownExtended->config()->set('footnotes', true);

        $markdown = "First[^1] and second[^2].\n\n[^1]: First footnote.\n\n[^2]: Second footnote.";
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringContainsString('First footnote.', $html);
        $this->assertStringContainsString('Second footnote.', $html);
    }

    public function testFootnoteNumbersResetBetweenParses(): void
    {
        $markdown = "Text[^first] and more[^second].\n\n"
            . "[^first]: First footnote.\n\n"
            . "[^second]: Second footnote.";

        $first = $this->parsedownExtended->text($markdown);
        $second = $this->parsedownExtended->text($markdown);

        $this->assertSame($first, $second);
        $this->assertStringContainsString('>1</a>', $second);
        $this->assertStringContainsString('>2</a>', $second);
    }
}
