<?php

// NOTE: Add special attributes test to HeadingsTest.php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class HeadingsTest extends TestCase
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

    /**
     * Test case for heading without anchor.
     */
    public function testHeadingWithoutAnchor()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', false);

        $markdown = '# Heading 1';
        $expected = '<h1>Heading 1</h1>';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test case for heading without anchor ID attributes when auto anchors are disabled.
     */
    public function testHeadingWithoutAnchorDoesNotRenderEmptyIdAttribute()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', false);

        $markdown = <<<MARKDOWN
            # Heading 1

            Setext Heading
            ===
            MARKDOWN;

        $actual = $this->parsedownExtended->text($markdown);

        $this->assertStringNotContainsString(' id=""', $actual);
        $this->assertStringNotContainsString(' id', $actual);
    }

    /**
     * Test case for heading with anchor.
     */
    public function testHeadingWithAnchor()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', true);

        $markdown = '# Heading 1';
        $expected = '<h1 id="heading-1">Heading 1</h1>';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test case for heading with multiple occurrences.
     */
    public function testHeadingWithMultipleOccurrences()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', true);

        $markdown = <<<MARKDOWN
            # Heading 1
            # Heading 1
            # Heading 1
            MARKDOWN;

        $expected = <<<HTML
            <h1 id="heading-1">Heading 1</h1>
            <h1 id="heading-1-1">Heading 1</h1>
            <h1 id="heading-1-2">Heading 1</h1>
            HTML;
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test case for heading with custom anchor.
     */
    public function testHeadingWithCustomAnchor()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', true);

        $markdown = '# Heading 1 {#custom-anchor}';
        $expected = '<h1 id="custom-anchor">Heading 1</h1>';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test case for heading with blacklisted header ids.
     */
    public function testHeadingWithBlacklistedHeaderIds()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', true);
        $this->parsedownExtended->config()->set('headings.auto_anchors.blacklist', ['heading-1']);

        $markdown = '# Heading 1';
        $expected = '<h1 id="heading-1-1">Heading 1</h1>';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test case for heading blacklist with multiple occurrences.
     */
    public function testHeadingBlacklistWithMultipleOccurrences()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', true);
        $this->parsedownExtended->config()->set('headings.auto_anchors.blacklist', ['heading-1', 'heading-4']);

        $markdown = <<<MARKDOWN
            # Heading
            # Heading
            # Heading
            # Heading
            MARKDOWN;

        $expected = <<<HTML
            <h1 id="heading">Heading</h1>
            <h1 id="heading-2">Heading</h1>
            <h1 id="heading-3">Heading</h1>
            <h1 id="heading-5">Heading</h1>
            HTML;
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test case for heading with custom anchor and blacklisted header ids.
     */
    public function testHeadingWithCustomAnchorAndBlacklistedHeaderIds()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', true);
        $this->parsedownExtended->config()->set('headings.auto_anchors.blacklist', ['custom-anchor']);

        $markdown = '# Heading 1 {#custom-anchor}';
        $expected = '<h1 id="custom-anchor-1">Heading 1</h1>';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }


    /**
     * Test case for heading with limited allowed levels.
     */
    public function testHeadingWithLimitedAllowedLevels()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', false);
        $this->parsedownExtended->config()->set('headings.allowed_levels', ['h1', 'h2'], true);

        $markdown = <<<MARKDOWN
            # Heading 1
            ## Heading 2
            ### Heading 3
            #### Heading 4
            ##### Heading 5
            ###### Heading 6
            MARKDOWN;

        $expected = <<<HTML
            <h1>Heading 1</h1>
            <h2>Heading 2</h2>
            <p>### Heading 3
            #### Heading 4
            ##### Heading 5
            ###### Heading 6</p>
            HTML;
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test case for heading with lowercase.
     */
    public function testHeadingWithLowercase()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', true);
        $this->parsedownExtended->config()->set('headings.auto_anchors.lowercase', true);

        $markdown = '# Heading 1';
        $expected = '<h1 id="heading-1">Heading 1</h1>';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);


        $this->parsedownExtended->config()->set('headings.auto_anchors.lowercase', false);

        $markdown = '# Heading 1';
        $expected = '<h1 id="Heading-1">Heading 1</h1>';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test case for heading with custom delimiter.
     */
    public function testHeadingWithDelimiter()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', true);
        $this->parsedownExtended->config()->set('headings.auto_anchors.delimiter', '_');

        $markdown = '# Heading 1';
        $expected = '<h1 id="heading_1">Heading 1</h1>';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test case for heading with replacement.
     */
    public function testHeadingWithReplacement()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', true);
        $this->parsedownExtended->config()->set('headings.auto_anchors.replacements', [
            '/h/' => 'd',
        ]);

        $markdown = '# Heading 1';
        $expected = '<h1 id="deading-1">Heading 1</h1>';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Empty headings should fall back to id="heading".
     */
    public function testEmptyHeadingProducesNoId()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', true);

        $markdown = '# ';
        $actual = $this->parsedownExtended->text($markdown);

        $this->assertStringContainsString(' id="heading"', $actual);
    }

    /**
     * Headings consisting only of characters stripped during sanitization should fall back
     * to id="heading".
     */
    public function testHeadingWithOnlyStrippedCharsProducesNoId()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', true);

        $markdown = '# ---';
        $actual = $this->parsedownExtended->text($markdown);

        $this->assertStringContainsString(' id="heading"', $actual);
    }

    /**
     * Test case for heading with custom logic using callback.
     */
    public function testHeadingUsingCallback()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', true);
        $this->parsedownExtended->setCreateAnchorIDCallback(function ($text) {
            return 'custom-anchor';
        });

        $markdown = '# Heading 1';
        $expected = '<h1 id="custom-anchor">Heading 1</h1>';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test attributes: basic class and id.
     */
    public function testAttributesClassAndId()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', false);

        $markdown = '# Heading {.my-class #my-id}';
        $expected = '<h1 class="my-class" id="my-id">Heading</h1>';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test attributes: denylist blocks a specific class.
     */
    public function testAttributesDenylistClass()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', false);
        $this->parsedownExtended->config()->set('attributes.denylist.classes', ['secret', 'internal']);

        $markdown = '# Heading {.visible .secret}';
        $expected = '<h1 class="visible">Heading</h1>';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test attributes: denylist removes all classes when all are denied.
     */
    public function testAttributesDenylistAllClasses()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', false);
        $this->parsedownExtended->config()->set('attributes.denylist.classes', ['secret']);

        $markdown = '# Heading {.secret}';
        $expected = '<h1>Heading</h1>';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test attributes: denylist blocks a specific id.
     */
    public function testAttributesDenylistId()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', false);
        $this->parsedownExtended->config()->set('attributes.denylist.ids', ['forbidden-id']);

        $markdown = '# Heading {#forbidden-id}';
        $expected = '<h1>Heading</h1>';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test attributes: denylist blocks a specific data-* attribute (inline element).
     */
    public function testAttributesDenylistDataAttribute()
    {
        $this->parsedownExtended->config()->set('attributes.data_attributes', true);
        $this->parsedownExtended->config()->set('attributes.denylist.data_attributes', ['data-secret']);

        $markdown = '[link](http://example.com){[data-secret="yes"] [data-visible="true"]}';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertStringNotContainsString('data-secret', $actual);
        $this->assertStringContainsString('data-visible="true"', $actual);
    }

    /**
     * Test attributes: allowlist permits only specific classes.
     */
    public function testAttributesAllowlistClass()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', false);
        $this->parsedownExtended->config()->set('attributes.allowlist.classes', ['highlight', 'note']);

        $markdown = '# Heading {.highlight .danger}';
        $expected = '<h1 class="highlight">Heading</h1>';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test attributes: allowlist with wildcard allows all classes.
     */
    public function testAttributesAllowlistClassWildcard()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', false);
        $this->parsedownExtended->config()->set('attributes.allowlist.classes', ['*']);

        $markdown = '# Heading {.anything .whatever}';
        $expected = '<h1 class="anything whatever">Heading</h1>';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test attributes: denylist with wildcard blocks all classes.
     */
    public function testAttributesDenylistClassWildcard()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', false);
        $this->parsedownExtended->config()->set('attributes.denylist.classes', ['*']);

        $markdown = '# Heading {.anything .whatever}';
        $expected = '<h1>Heading</h1>';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test attributes: allowlist permits only specific ids.
     */
    public function testAttributesAllowlistId()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', false);
        $this->parsedownExtended->config()->set('attributes.allowlist.ids', ['allowed-id']);

        $markdown = '# Heading {#other-id}';
        $expected = '<h1>Heading</h1>';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test attributes: denylist with wildcard blocks all ids.
     */
    public function testAttributesDenylistIdWildcard()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', false);
        $this->parsedownExtended->config()->set('attributes.denylist.ids', ['*']);

        $markdown = '# Heading {#any-id}';
        $expected = '<h1>Heading</h1>';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test attributes: allowlist permits only specific data-* attributes (inline element).
     */
    public function testAttributesAllowlistDataAttribute()
    {
        $this->parsedownExtended->config()->set('attributes.data_attributes', true);
        $this->parsedownExtended->config()->set('attributes.allowlist.data_attributes', ['data-allowed']);

        $markdown = '[link](http://example.com){[data-allowed="yes"] [data-blocked="no"]}';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertStringContainsString('data-allowed="yes"', $actual);
        $this->assertStringNotContainsString('data-blocked', $actual);
    }

    /**
     * Test attributes: denylist with wildcard blocks all data-* attributes (inline element).
     */
    public function testAttributesDenylistDataAttributeWildcard()
    {
        $this->parsedownExtended->config()->set('attributes.data_attributes', true);
        $this->parsedownExtended->config()->set('attributes.denylist.data_attributes', ['*']);

        $markdown = '[link](http://example.com){[data-foo="yes"] [data-bar="true"]}';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertStringNotContainsString('data-foo', $actual);
        $this->assertStringNotContainsString('data-bar', $actual);
    }

    /**
     * Test attributes: allowlist takes priority over denylist.
     */
    public function testAttributesAllowlistTakesPriorityOverDenylist()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', false);
        $this->parsedownExtended->config()->set('attributes.allowlist.classes', ['highlight']);
        $this->parsedownExtended->config()->set('attributes.denylist.classes', ['highlight']);

        $markdown = '# Heading {.highlight .other}';
        $expected = '<h1 class="highlight">Heading</h1>';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that attributes can be disabled entirely.
     */
    public function testAttributesCanBeDisabled()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', false);
        $this->parsedownExtended->config()->set('headings.attributes', false);

        // When disabled, ParsedownExtra still strips the {.my-class} syntax from the text
        $markdown = '# Heading {.my-class}';
        $expected = '<h1>Heading</h1>';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that old "special_attributes.*" config paths still work and emit a deprecation warning.
     */
    public function testSpecialAttributesDeprecatedAlias()
    {
        $deprecations = [];
        set_error_handler(function ($errno, $errstr) use (&$deprecations) {
            $deprecations[] = $errstr;
            return true;
        }, E_USER_DEPRECATED);

        $this->parsedownExtended->config()->set('special_attributes.denylist.classes', ['secret']);

        restore_error_handler();

        $this->assertCount(1, $deprecations);
        $this->assertStringContainsString('special_attributes.denylist.classes', $deprecations[0]);
        $this->assertStringContainsString('attributes.denylist.classes', $deprecations[0]);

        // Verify the value was actually applied via the new path
        $markdown = '# Heading {.secret .visible}';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertStringNotContainsString('secret', $actual);
        $this->assertStringContainsString('visible', $actual);
    }

    /**
     * Test that old "headings.special_attributes" config path still works and emits a deprecation warning.
     */
    public function testHeadingsSpecialAttributesDeprecatedAlias()
    {
        $deprecations = [];
        set_error_handler(function ($errno, $errstr) use (&$deprecations) {
            $deprecations[] = $errstr;
            return true;
        }, E_USER_DEPRECATED);

        $this->parsedownExtended->config()->set('headings.auto_anchors', false);
        $this->parsedownExtended->config()->set('headings.special_attributes', false);

        restore_error_handler();

        $this->assertCount(1, $deprecations);
        $this->assertStringContainsString('headings.special_attributes', $deprecations[0]);
        $this->assertStringContainsString('headings.attributes', $deprecations[0]);

        $markdown = '# Heading {.my-class}';
        $expected = '<h1>Heading</h1>';
        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }
}
