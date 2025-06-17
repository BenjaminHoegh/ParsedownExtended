<?php

use Erusev\Parsedown\Parsedown;
use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class AlertsTest extends TestCase
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

    public function testEnableAlerts()
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

        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals(trim($expectedHtml), trim($result));
    }

    public function testDisableAlerts()
    {

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

        $result = $this->parsedown->toHtml($markdown);

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

        $result = $this->parsedown->toHtml($markdown);

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

        $result = $this->parsedown->toHtml($markdown);

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

        $result = $this->parsedown->toHtml($markdown);

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

        $result = $this->parsedown->toHtml($markdown);

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

        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals(trim($expectedHtml), trim($result));
    }

    public function testCustomAlertTypes()
    {

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

        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals(trim($expectedHtml), trim($result));
    }
}
