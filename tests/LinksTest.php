<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class LinksTest extends TestCase
{
    private ParsedownExtended $parsedownExtended;
    private bool $hadOriginalHost;
    private ?string $originalHost;

    protected function setUp(): void
    {
        $this->parsedownExtended = new ParsedownExtended();
        $this->parsedownExtended->setSafeMode(true);
        $this->hadOriginalHost = array_key_exists('HTTP_HOST', $_SERVER);
        $this->originalHost = $_SERVER['HTTP_HOST'] ?? null;
        $_SERVER['HTTP_HOST'] = 'www.example.com';
    }

    protected function tearDown(): void
    {
        if ($this->hadOriginalHost) {
            $_SERVER['HTTP_HOST'] = $this->originalHost;
        } else {
            unset($_SERVER['HTTP_HOST']);
        }

        unset($this->parsedownExtended);
    }

    // General Link Settings
    // ----------------------------

    public function testLinksEnabled()
    {
        $this->parsedownExtended->config()->set('links.enabled', true);

        $markdown = '[Link](https://www.example.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringContainsString('<a href="https://www.example.com">Link</a>', $html);
    }

    public function testLinksDisabled()
    {
        $this->parsedownExtended->config()->set('links.enabled', false);

        $markdown = '[Link](https://www.example.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringNotContainsString('<a href="https://www.example.com">Link</a>', $html);
    }

    // Email Links
    // ----------------------------

    public function testEmailLinksEnabled()
    {
        $this->parsedownExtended->config()->set('links.email_links', true);

        $markdown = '<test@example.com>';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringContainsString('<a href="mailto:test@example.com">test@example.com</a>', $html);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    public function testEmailLinksDisabled()
    {
        $this->parsedownExtended->config()->set('links.email_links', false);

        $markdown = '<mailto:test@example.com>';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringNotContainsString('<a href="mailto:test@example.com">test@example.com</a>', $html);
    }

    // External Links Settings
    // ----------------------------

    public function testExternalLinksEnabled()
    {
        $this->parsedownExtended->config()->set('links.external_links', true);
        $this->parsedownExtended->config()->set('links.external_links.nofollow', false);
        $this->parsedownExtended->config()->set('links.external_links.noopener', false);
        $this->parsedownExtended->config()->set('links.external_links.noreferrer', false);
        $this->parsedownExtended->config()->set('links.external_links.open_in_new_window', false);

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringContainsString('<a href="https://www.google.com">External</a>', $html);
    }

    public function testExternalLinksDisabled()
    {
        $this->parsedownExtended->config()->set('links.external_links', false);

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringNotContainsString('<a href="https://www.google.com">External</a>', $html);
    }

    public function testNofollowEnabled()
    {
        $this->parsedownExtended->config()->set('links.external_links', true);
        $this->parsedownExtended->config()->set('links.external_links.nofollow', true);
        $this->parsedownExtended->config()->set('links.external_links.noopener', false);
        $this->parsedownExtended->config()->set('links.external_links.noreferrer', false);
        $this->parsedownExtended->config()->set('links.external_links.open_in_new_window', false);

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringContainsString('rel="nofollow"', $html);
    }

    public function testNofollowDisabled()
    {
        $this->parsedownExtended->config()->set('links.external_links', true);
        $this->parsedownExtended->config()->set('links.external_links.nofollow', false);
        $this->parsedownExtended->config()->set('links.external_links.noopener', false);
        $this->parsedownExtended->config()->set('links.external_links.noreferrer', false);
        $this->parsedownExtended->config()->set('links.external_links.open_in_new_window', false);

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringNotContainsString('rel="nofollow"', $html);
    }

    public function testNoopenerEnabled()
    {
        $this->parsedownExtended->config()->set('links.external_links', true);
        $this->parsedownExtended->config()->set('links.external_links.nofollow', false);
        $this->parsedownExtended->config()->set('links.external_links.noopener', true);
        $this->parsedownExtended->config()->set('links.external_links.noreferrer', false);
        $this->parsedownExtended->config()->set('links.external_links.open_in_new_window', false);

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringContainsString('rel="noopener"', $html);
    }

    public function testNoopenerDisabled()
    {
        $this->parsedownExtended->config()->set('links.external_links', true);
        $this->parsedownExtended->config()->set('links.external_links.nofollow', false);
        $this->parsedownExtended->config()->set('links.external_links.noopener', false);
        $this->parsedownExtended->config()->set('links.external_links.noreferrer', false);
        $this->parsedownExtended->config()->set('links.external_links.open_in_new_window', false);

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringNotContainsString('rel="noopener"', $html);
    }

    public function testNoreferrerEnabled()
    {
        $this->parsedownExtended->config()->set('links.external_links', true);
        $this->parsedownExtended->config()->set('links.external_links.nofollow', false);
        $this->parsedownExtended->config()->set('links.external_links.noopener', false);
        $this->parsedownExtended->config()->set('links.external_links.noreferrer', true);
        $this->parsedownExtended->config()->set('links.external_links.open_in_new_window', false);

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringContainsString('rel="noreferrer"', $html);
    }

    public function testNoreferrerDisabled()
    {
        $this->parsedownExtended->config()->set('links.external_links', true);
        $this->parsedownExtended->config()->set('links.external_links.nofollow', false);
        $this->parsedownExtended->config()->set('links.external_links.noopener', false);
        $this->parsedownExtended->config()->set('links.external_links.noreferrer', false);
        $this->parsedownExtended->config()->set('links.external_links.open_in_new_window', false);

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringNotContainsString('rel="noreferrer"', $html);
    }

    public function testOpenInNewWindowEnabled()
    {
        $this->parsedownExtended->config()->set('links.external_links', true);
        $this->parsedownExtended->config()->set('links.external_links.nofollow', false);
        $this->parsedownExtended->config()->set('links.external_links.noopener', false);
        $this->parsedownExtended->config()->set('links.external_links.noreferrer', false);
        $this->parsedownExtended->config()->set('links.external_links.open_in_new_window', true);

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testOpenInNewWindowDisabled()
    {
        $this->parsedownExtended->config()->set('links.external_links', true);
        $this->parsedownExtended->config()->set('links.external_links.nofollow', false);
        $this->parsedownExtended->config()->set('links.external_links.noopener', false);
        $this->parsedownExtended->config()->set('links.external_links.noreferrer', false);
        $this->parsedownExtended->config()->set('links.external_links.open_in_new_window', false);

        $markdown = '[External](https://www.google.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    // Internal and Same-Domain Links
    // ----------------------------


    public function testCustomInternalHostLink()
    {
        $this->parsedownExtended->config()->set('links.external_links.internal_hosts', ['google.com']);

        $markdown = '[Home](https://www.google.com)';
        $html = $this->parsedownExtended->text($markdown);


        $this->assertStringNotContainsString('noopener"', $html);
        $this->assertStringNotContainsString('nofollow', $html);
        $this->assertStringNotContainsString('noreferrer', $html);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    public function testInternalHostsCacheRefreshesAfterConfigUpdate()
    {
        $this->parsedownExtended->config()
            ->set('links.external_links.nofollow', true)
            ->set('links.external_links.noopener', true)
            ->set('links.external_links.noreferrer', true)
            ->set('links.external_links.open_in_new_window', true);

        $markdown = '[External](https://www.google.com)';

        $beforeUpdate = $this->parsedownExtended->text($markdown);
        $this->assertStringContainsString('rel="nofollow noopener noreferrer"', $beforeUpdate);
        $this->assertStringContainsString('target="_blank"', $beforeUpdate);

        $this->parsedownExtended->config()->set('links.external_links.internal_hosts', ['google.com']);

        $afterUpdate = $this->parsedownExtended->text($markdown);
        $this->assertStringNotContainsString('rel="nofollow noopener noreferrer"', $afterUpdate);
        $this->assertStringNotContainsString('target="_blank"', $afterUpdate);
    }

    public function testExternalLinkDefaultsDoNotAddAttributes()
    {
        $html = $this->parsedownExtended->text('[External](https://www.google.com)');

        $this->assertStringNotContainsString('rel=', $html);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    public function testSameDomainLinkWithoutNewWindow()
    {
        $markdown = '[Home](https://www.example.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    public function testSameDomainLinkWithAttributes()
    {
        $markdown = '[Home](https://www.example.com)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringNotContainsString('nofollow"', $html);
        $this->assertStringNotContainsString('noopener"', $html);
        $this->assertStringNotContainsString('noreferrer"', $html);
    }

    public function testConfiguredCurrentHostTakesPrecedenceOverServerHost()
    {
        $_SERVER['HTTP_HOST'] = 'www.example.com';
        $this->parsedownExtended->config()->set('links.current_host', 'docs.example.test');

        $markdown = '[Docs](https://docs.example.test/guide)';
        $html = $this->parsedownExtended->text($markdown);

        $this->assertStringNotContainsString('target="_blank"', $html);
        $this->assertStringNotContainsString('nofollow"', $html);
        $this->assertStringNotContainsString('noopener"', $html);
        $this->assertStringNotContainsString('noreferrer"', $html);
    }

    public function testNoServerVars()
    {
        $this->parsedownExtended->config()->set('links.enabled', true);

        unset($_SERVER['HTTP_HOST']);

        $markdown = '[Link](https://www.example.com/blah)';
        $html = $this->parsedownExtended->line($markdown);

        $this->assertStringContainsString('href="https://www.example.com/blah"', $html);
    }

    public function testLinksRemainNonNestableInsideEmphasis()
    {
        $markdown = '[foo *[bar [baz](/uri)](/uri)*](/uri)';
        $expectedHtml = '<p><a href="/uri">foo <em>[bar [baz](/uri)](/uri)</em></a></p>';

        $this->assertEquals($expectedHtml, $this->parsedownExtended->text($markdown));
    }

    public function testReferenceLinksRemainNonNestableInsideEmphasis()
    {
        $markdown = "[foo *bar [baz][ref]*][ref]\n\n[ref]: /uri";
        $expectedHtml = '<p><a href="/uri">foo <em>bar [baz][ref]</em></a></p>';

        $this->assertEquals($expectedHtml, $this->parsedownExtended->text($markdown));
    }

}
