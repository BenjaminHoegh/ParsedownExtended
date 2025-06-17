<?php

// NOTE: Add special attributes test to HeadingsTest.php

use Erusev\Parsedown\Parsedown;
use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class HeadingsTest extends TestCase
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

    /**
     * Test case for heading without anchor.
     */
    public function testHeadingWithoutAnchor()
    {

        $markdown = '# Heading 1';
        $expected = '<h1>Heading 1</h1>';
        $actual = $this->parsedown->toHtml($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test case for heading with anchor.
     */
    public function testHeadingWithAnchor()
    {

        $markdown = '# Heading 1';
        $expected = '<h1 id="heading-1">Heading 1</h1>';
        $actual = $this->parsedown->toHtml($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test case for heading with multiple occurrences.
     */
    public function testHeadingWithMultipleOccurrences()
    {

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
        $actual = $this->parsedown->toHtml($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test case for heading with custom anchor.
     */
    public function testHeadingWithCustomAnchor()
    {

        $markdown = '# Heading 1 {#custom-anchor}';
        $expected = '<h1 id="custom-anchor">Heading 1</h1>';
        $actual = $this->parsedown->toHtml($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test case for heading with blacklisted header ids.
     */
    public function testHeadingWithBlacklistedHeaderIds()
    {

        $markdown = '# Heading 1';
        $expected = '<h1 id="heading-1-1">Heading 1</h1>';
        $actual = $this->parsedown->toHtml($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test case for heading blacklist with multiple occurrences.
     */
    public function testHeadingBlacklistWithMultipleOccurrences()
    {

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
        $actual = $this->parsedown->toHtml($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test case for heading with custom anchor and blacklisted header ids.
     */
    public function testHeadingWithCustomAnchorAndBlacklistedHeaderIds()
    {

        $markdown = '# Heading 1 {#custom-anchor}';
        $expected = '<h1 id="custom-anchor-1">Heading 1</h1>';
        $actual = $this->parsedown->toHtml($markdown);
        $this->assertEquals($expected, $actual);
    }


    /**
     * Test case for heading with limited allowed levels.
     */
    public function testHeadingWithLimitedAllowedLevels()
    {

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
        $actual = $this->parsedown->toHtml($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test case for heading with lowercase.
     */
    public function testHeadingWithLowercase()
    {

        $markdown = '# Heading 1';
        $expected = '<h1 id="heading-1">Heading 1</h1>';
        $actual = $this->parsedown->toHtml($markdown);
        $this->assertEquals($expected, $actual);



        $markdown = '# Heading 1';
        $expected = '<h1 id="Heading-1">Heading 1</h1>';
        $actual = $this->parsedown->toHtml($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test case for heading with custom delimiter.
     */
    public function testHeadingWithDelimiter()
    {

        $markdown = '# Heading 1';
        $expected = '<h1 id="heading_1">Heading 1</h1>';
        $actual = $this->parsedown->toHtml($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test case for heading with replacement.
     */
    public function testHeadingWithReplacement()
    {
            '/h/' => 'd',
        ]);

        $markdown = '# Heading 1';
        $expected = '<h1 id="deading-1">Heading 1</h1>';
        $actual = $this->parsedown->toHtml($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test case for heading with custom logic using callback.
     */
    public function testHeadingUsingCallback()
    {
        $this->parsedown->setCreateAnchorIDCallback(function ($text) {
            return 'custom-anchor';
        });

        $markdown = '# Heading 1';
        $expected = '<h1 id="custom-anchor">Heading 1</h1>';
        $actual = $this->parsedown->toHtml($markdown);
        $this->assertEquals($expected, $actual);
    }
}
