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
        $this->parsedownExtended->config()->set('headings.allowed', ['h1', 'h2'], true);

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
}
