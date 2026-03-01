<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class AlertsTest extends TestCase
{
    protected ParsedownExtended $parsedownExtended;

    protected function setUp(): void
    {
        $this->parsedownExtended = new ParsedownExtended();
        $this->parsedownExtended->setSafeMode(true); // Set any necessary configurations
    }

    protected function tearDown(): void
    {
        unset($this->parsedownExtended);
    }

    public function testEnableAlerts()
    {
        $this->parsedownExtended->config()->set('alerts', true);

        $markdown = <<<MARKDOWN
            > [!NOTE]
            > This is a note.
            MARKDOWN;

        $expectedHtml = <<<HTML
            <div class="markdown-alert markdown-alert-note">
            <p class="markdown-alert-title">Note</p>
            <p>This is a note.</p>
            </div>
            HTML;

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals(trim($expectedHtml), trim($result));
    }

    public function testDisableAlerts()
    {
        $this->parsedownExtended->config()->set('alerts', false);

        $markdown = <<<MARKDOWN
            > [!NOTE]
            > This is a note.
            MARKDOWN;

        $expectedHtml = <<<HTML
            <blockquote>
            <p>[!NOTE]
            This is a note.</p>
            </blockquote>
            HTML;

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals(trim($expectedHtml), trim($result));
    }

    public function testNoteAlert()
    {
        $markdown = <<<MARKDOWN
            > [!NOTE]
            > This is a note.
            MARKDOWN;

        $expectedHtml = <<<HTML
            <div class="markdown-alert markdown-alert-note">
            <p class="markdown-alert-title">Note</p>
            <p>This is a note.</p>
            </div>
            HTML;

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals(trim($expectedHtml), trim($result));
    }

    public function testTipAlert()
    {
        $markdown = <<<MARKDOWN
            > [!TIP]
            > This is a tip.
            MARKDOWN;

        $expectedHtml = <<<HTML
            <div class="markdown-alert markdown-alert-tip">
            <p class="markdown-alert-title">Tip</p>
            <p>This is a tip.</p>
            </div>
            HTML;

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals(trim($expectedHtml), trim($result));
    }

    public function testImportantAlert()
    {
        $markdown = <<<MARKDOWN
            > [!IMPORTANT]
            > This is important.
            MARKDOWN;

        $expectedHtml = <<<HTML
            <div class="markdown-alert markdown-alert-important">
            <p class="markdown-alert-title">Important</p>
            <p>This is important.</p>
            </div>
            HTML;

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals(trim($expectedHtml), trim($result));
    }

    public function testWarningAlert()
    {
        $markdown = <<<MARKDOWN
            > [!WARNING]
            > This is a warning.
            MARKDOWN;

        $expectedHtml = <<<HTML
            <div class="markdown-alert markdown-alert-warning">
            <p class="markdown-alert-title">Warning</p>
            <p>This is a warning.</p>
            </div>
            HTML;

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals(trim($expectedHtml), trim($result));
    }

    public function testCautionAlert()
    {
        $markdown = <<<MARKDOWN
            > [!CAUTION]
            > This is a caution.
            MARKDOWN;

        $expectedHtml = <<<HTML
            <div class="markdown-alert markdown-alert-caution">
            <p class="markdown-alert-title">Caution</p>
            <p>This is a caution.</p>
            </div>
            HTML;

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals(trim($expectedHtml), trim($result));
    }

    public function testCustomAlertTypes()
    {
        $this->parsedownExtended->config()->set('alerts.types', ['custom']);

        $markdown = <<<MARKDOWN
            > [!CUSTOM]
            > This is a custom alert.
            MARKDOWN;

        $expectedHtml = <<<HTML
            <div class="markdown-alert markdown-alert-custom">
            <p class="markdown-alert-title">Custom</p>
            <p>This is a custom alert.</p>
            </div>
            HTML;

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals(trim($expectedHtml), trim($result));
    }

    public function testMultiLineAlert()
    {
        $markdown = <<<MARKDOWN
            > [!NOTE]
            > First line.
            >
            > Second line.
            MARKDOWN;

        $expectedHtml = <<<HTML
            <div class="markdown-alert markdown-alert-note">
            <p class="markdown-alert-title">Note</p>
            <p>First line.</p>
            <p>Second line.</p>
            </div>
            HTML;

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals(trim($expectedHtml), trim($result));
    }

    public function testAlertCannotBeNestedInsideAlert()
    {
        $markdown = <<<MARKDOWN
            > [!NOTE]
            > Outer note.
            > > [!WARNING]
            > > Inner warning should not become an alert.
            MARKDOWN;

        $expectedHtml = <<<HTML
            <div class="markdown-alert markdown-alert-note">
            <p class="markdown-alert-title">Note</p>
            <p>Outer note.</p>
            <blockquote>
            <p>[!WARNING]
            Inner warning should not become an alert.</p>
            </blockquote>
            </div>
            HTML;

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals(trim($expectedHtml), trim($result));
    }

    public function testLinksWorkInsideAlert()
    {
        $markdown = <<<MARKDOWN
            > [!NOTE]
            > Visit [Docs](/docs).
            MARKDOWN;

        $result = $this->parsedownExtended->text($markdown);

        $this->assertStringContainsString('<div class="markdown-alert markdown-alert-note">', $result);
        $this->assertStringContainsString('<p class="markdown-alert-title">Note</p>', $result);
        $this->assertStringContainsString('<p>Visit <a href="/docs">Docs</a>.</p>', $result);
    }

    public function testBoldAndItalicWorkInsideAlert()
    {
        $markdown = <<<MARKDOWN
            > [!NOTE]
            > This is *italic* and **bold** text.
            MARKDOWN;

        $result = $this->parsedownExtended->text($markdown);

        $this->assertStringContainsString('<div class="markdown-alert markdown-alert-note">', $result);
        $this->assertStringContainsString('<p class="markdown-alert-title">Note</p>', $result);
        $this->assertStringContainsString('<p>This is <em>italic</em> and <strong>bold</strong> text.</p>', $result);
    }

    public function testAlertTypesAreRegexEscaped()
    {
        $this->parsedownExtended->config()->set('alerts.types', ['A.B']);

        $matchingMarkdown = <<<MARKDOWN
            > [!A.B]
            > Dot type should match exactly.
            MARKDOWN;

        $nonMatchingMarkdown = <<<MARKDOWN
            > [!AXB]
            > This should not match A.B.
            MARKDOWN;

        $matchingResult = $this->parsedownExtended->text($matchingMarkdown);
        $nonMatchingResult = $this->parsedownExtended->text($nonMatchingMarkdown);

        $this->assertStringContainsString('<div class="markdown-alert markdown-alert-a.b">', $matchingResult);
        $this->assertStringNotContainsString('<div class="markdown-alert', $nonMatchingResult);
        $this->assertStringContainsString('<blockquote>', $nonMatchingResult);
    }
}
