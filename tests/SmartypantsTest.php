<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class SmartypantsTest extends TestCase
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

    public function testEnableSmartypants()
    {
        $this->parsedownExtended->config()->set('smartypants', true);

        $markdown = <<<MARKDOWN
            "Hello," he said.
            MARKDOWN;

        $expected = <<<HTML
            <p>“Hello,” he said.</p>
            HTML;

        $this->assertEquals($expected, $this->parsedownExtended->text($markdown));
    }

    public function testDisableSmartypants()
    {
        $this->parsedownExtended->config()->set('smartypants', false);

        $markdown = <<<MARKDOWN
            "Hello," he said.
            MARKDOWN;

        $expected = <<<HTML
            <p>"Hello," he said.</p>
            HTML;

        $this->assertEquals($expected, $this->parsedownExtended->text($markdown));
    }

    public function testSmartypantsCanBeEnabledAfterParsing()
    {
        $markdown = <<<MARKDOWN
            "Hello," he said.
            MARKDOWN;

        $this->assertEquals('<p>"Hello," he said.</p>', $this->parsedownExtended->text($markdown));

        $this->parsedownExtended->config()->set('smartypants', true);

        $this->assertEquals('<p>“Hello,” he said.</p>', $this->parsedownExtended->text($markdown));
    }

    public function testSmartypantsSubstitutionsCanChangeAfterParsing(): void
    {
        $markdown = 'Before --- after';

        $this->parsedownExtended->config()->set('smartypants', true);
        $this->assertEquals('<p>Before — after</p>', $this->parsedownExtended->text($markdown));

        $this->parsedownExtended->config()->set('smartypants.substitutions.mdash', '[dash]');
        $this->assertEquals('<p>Before [dash] after</p>', $this->parsedownExtended->text($markdown));
    }

    public function testSmartypantsDashesAndEllipses(): void
    {
        $this->parsedownExtended->config()->set('smartypants', true);

        $markdown = 'a -- b --- c ...';

        $this->assertEquals('<p>a – b — c …</p>', $this->parsedownExtended->text($markdown));
    }

    public function testSmartypantsDashAndEllipsisRulesCanBeDisabled(): void
    {
        $this->parsedownExtended->config()->set('smartypants', true);
        $this->parsedownExtended->config()->set('smartypants.smart_dashes', false);
        $this->parsedownExtended->config()->set('smartypants.smart_ellipses', false);

        $markdown = 'a -- b --- c ...';

        $this->assertEquals('<p>a -- b --- c ...</p>', $this->parsedownExtended->text($markdown));
    }
}
