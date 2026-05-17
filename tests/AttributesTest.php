<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class AttributesTest extends TestCase
{
    private ParsedownExtended $parsedownExtended;

    protected function setUp(): void
    {
        $this->parsedownExtended = new ParsedownExtended();
        $this->parsedownExtended->setSafeMode(true);
    }

    protected function tearDown(): void
    {
        unset($this->parsedownExtended);
    }

    // -------------------------------------------------------------------------
    // Basic inline attribute syntax
    // -------------------------------------------------------------------------

    public function testInlineClassOnLink()
    {
        $markdown = '[link](http://example.com){.my-class}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringContainsString('class="my-class"', $actual);
    }

    public function testInlineIdOnLink()
    {
        $markdown = '[link](http://example.com){#my-id}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringContainsString('id="my-id"', $actual);
    }

    public function testInlineDataAttributeOnLink()
    {
        $this->parsedownExtended->config()->set('attributes.data_attributes', true);

        $markdown = '[link](http://example.com){[data-foo="bar"]}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringContainsString('data-foo="bar"', $actual);
    }

    public function testInlineMultipleAttributesOnLink()
    {
        $this->parsedownExtended->config()->set('attributes.data_attributes', true);

        $markdown = '[link](http://example.com){.highlight #nav [data-section="main"]}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringContainsString('class="highlight"', $actual);
        $this->assertStringContainsString('id="nav"', $actual);
        $this->assertStringContainsString('data-section="main"', $actual);
    }

    public function testInlineClassOnImage()
    {
        $markdown = '![alt](http://example.com/img.png){.thumb}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringContainsString('class="thumb"', $actual);
    }

    // -------------------------------------------------------------------------
    // Denylist
    // -------------------------------------------------------------------------

    public function testDenylistBlocksClass()
    {
        $this->parsedownExtended->config()->set('attributes.denylist.classes', ['secret']);

        $markdown = '[link](http://example.com){.secret .visible}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringNotContainsString('secret', $actual);
        $this->assertStringContainsString('class="visible"', $actual);
    }

    public function testDenylistBlocksId()
    {
        $this->parsedownExtended->config()->set('attributes.denylist.ids', ['forbidden']);

        $markdown = '[link](http://example.com){#forbidden}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringNotContainsString('id=', $actual);
    }

    public function testDenylistBlocksDataAttribute()
    {
        $this->parsedownExtended->config()->set('attributes.data_attributes', true);
        $this->parsedownExtended->config()->set('attributes.denylist.data_attributes', ['data-secret']);

        $markdown = '[link](http://example.com){[data-secret="yes"] [data-public="yes"]}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringNotContainsString('data-secret', $actual);
        $this->assertStringContainsString('data-public="yes"', $actual);
    }

    public function testDenylistWildcardBlocksAllClasses()
    {
        $this->parsedownExtended->config()->set('attributes.denylist.classes', ['*']);

        $markdown = '[link](http://example.com){.foo .bar}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringNotContainsString('class=', $actual);
    }

    public function testDenylistWildcardBlocksAllIds()
    {
        $this->parsedownExtended->config()->set('attributes.denylist.ids', ['*']);

        $markdown = '[link](http://example.com){#any-id}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringNotContainsString('id=', $actual);
    }

    public function testDenylistWildcardBlocksAllDataAttributes()
    {
        $this->parsedownExtended->config()->set('attributes.data_attributes', true);
        $this->parsedownExtended->config()->set('attributes.denylist.data_attributes', ['*']);

        $markdown = '[link](http://example.com){[data-foo="yes"] [data-bar="true"]}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringNotContainsString('data-foo', $actual);
        $this->assertStringNotContainsString('data-bar', $actual);
    }

    // -------------------------------------------------------------------------
    // Allowlist
    // -------------------------------------------------------------------------

    public function testAllowlistPermitsOnlyListedClasses()
    {
        $this->parsedownExtended->config()->set('attributes.allowlist.classes', ['highlight', 'note']);

        $markdown = '[link](http://example.com){.highlight .danger}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringContainsString('class="highlight"', $actual);
        $this->assertStringNotContainsString('danger', $actual);
    }

    public function testAllowlistPermitsOnlyListedIds()
    {
        $this->parsedownExtended->config()->set('attributes.allowlist.ids', ['nav', 'header']);

        $markdown = '[link](http://example.com){#other}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringNotContainsString('id=', $actual);
    }

    public function testAllowlistPermitsOnlyListedDataAttributes()
    {
        $this->parsedownExtended->config()->set('attributes.data_attributes', true);
        $this->parsedownExtended->config()->set('attributes.allowlist.data_attributes', ['data-allowed']);

        $markdown = '[link](http://example.com){[data-allowed="yes"] [data-blocked="no"]}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringContainsString('data-allowed="yes"', $actual);
        $this->assertStringNotContainsString('data-blocked', $actual);
    }

    public function testAllowlistWildcardAllowsAllClasses()
    {
        $this->parsedownExtended->config()->set('attributes.allowlist.classes', ['*']);

        $markdown = '[link](http://example.com){.foo .bar}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringContainsString('class="foo bar"', $actual);
    }

    public function testAllowlistTakesPriorityOverDenylist()
    {
        $this->parsedownExtended->config()->set('attributes.allowlist.classes', ['highlight']);
        $this->parsedownExtended->config()->set('attributes.denylist.classes', ['highlight']);

        $markdown = '[link](http://example.com){.highlight .other}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringContainsString('class="highlight"', $actual);
        $this->assertStringNotContainsString('other', $actual);
    }

    // -------------------------------------------------------------------------
    // Security: only class, id, and data-* are permitted; data-* off by default
    // -------------------------------------------------------------------------

    public function testDataAttributesDisabledByDefault()
    {
        $markdown = '[link](http://example.com){[data-foo="bar"]}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringNotContainsString('data-foo', $actual);
    }

    public function testDataAttributesCanBeEnabled()
    {
        $this->parsedownExtended->config()->set('attributes.data_attributes', true);

        $markdown = '[link](http://example.com){[data-foo="bar"]}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringContainsString('data-foo="bar"', $actual);
    }

    public function testEventHandlerAttributeIsBlocked()
    {
        $markdown = '[link](http://example.com){[onclick="alert(1)"]}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringNotContainsString('onclick', $actual);
    }

    public function testStyleAttributeIsBlocked()
    {
        $markdown = '[link](http://example.com){[style="color:red"]}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringNotContainsString('style=', $actual);
    }

    public function testSrcAttributeIsBlocked()
    {
        $markdown = '[link](http://example.com){[src="http://evil.com"]}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringNotContainsString('src=', $actual);
    }

    public function testArbitraryHtmlAttributeIsBlocked()
    {
        $markdown = '[link](http://example.com){[tabindex="0"] [accesskey="x"]}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringNotContainsString('tabindex', $actual);
        $this->assertStringNotContainsString('accesskey', $actual);
    }

    public function testOnlyClassAndIdPermittedByDefault()
    {
        // data-* is off by default; only class and id should pass through
        $markdown = '[link](http://example.com){.safe #safe-id [data-ok="yes"] [onclick="bad"] [style="bad"]}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringContainsString('class="safe"', $actual);
        $this->assertStringContainsString('id="safe-id"', $actual);
        $this->assertStringNotContainsString('data-ok', $actual);
        $this->assertStringNotContainsString('onclick', $actual);
        $this->assertStringNotContainsString('style=', $actual);
    }

    public function testOnlyClassIdAndDataPrefixArePermittedWhenEnabled()
    {
        $this->parsedownExtended->config()->set('attributes.data_attributes', true);

        $markdown = '[link](http://example.com){.safe #safe-id [data-ok="yes"] [onclick="bad"] [style="bad"]}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringContainsString('class="safe"', $actual);
        $this->assertStringContainsString('id="safe-id"', $actual);
        $this->assertStringContainsString('data-ok="yes"', $actual);
        $this->assertStringNotContainsString('onclick', $actual);
        $this->assertStringNotContainsString('style=', $actual);
    }

    public function testMixedCaseEventHandlerIsBlocked()
    {
        // Attribute names are lowercased before the safety check
        $markdown = '[link](http://example.com){[ONCLICK="alert(1)"]}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringNotContainsString('onclick', $actual);
        $this->assertStringNotContainsString('ONCLICK', $actual);
    }


    public function testFilteredAttributesBracesSuppressed()
    {
        // When all attributes are filtered out the {…} syntax must not appear in output
        $this->parsedownExtended->config()->set('attributes.denylist.classes', ['*']);

        $markdown = '[link](http://example.com){.foo}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringNotContainsString('{', $actual);
    }

    public function testAttributesDisabledLeavesNoTrailingBraces()
    {
        $this->parsedownExtended->config()->set('headings.attributes', false);

        $markdown = '[link](http://example.com){.foo}';
        $actual = $this->parsedownExtended->line($markdown);
        $this->assertStringNotContainsString('class=', $actual);
    }
}
