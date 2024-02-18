<?php

use PHPUnit\Framework\TestCase;

class TocTest extends TestCase
{
    protected $parsedownExtended;

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
     * Test case for table of contents.
     */
    public function testTocEnabled()
    {
        $this->parsedownExtended->setSetting('headings.auto_anchors', true);
        $this->parsedownExtended->setSetting('toc', true);

        $markdown = <<<MARKDOWN
        # Heading 1
        ## Heading 1.1
        ## Heading 1.2
        # Heading 2
        ## Heading 2.1
        ## Heading 2.2
        MARKDOWN;

        $expected = <<<HTML
        <ul>
        <li><a href="#heading-1">Heading 1</a>
        <ul>
        <li><a href="#heading-1-1">Heading 1.1</a></li>
        <li><a href="#heading-1-2">Heading 1.2</a></li>
        </ul></li>
        <li><a href="#heading-2">Heading 2</a>
        <ul>
        <li><a href="#heading-2-1">Heading 2.1</a></li>
        <li><a href="#heading-2-2">Heading 2.2</a></li>
        </ul></li>
        </ul>
        HTML;

        $actual = $this->parsedownExtended->body($markdown);
        $actual = $this->parsedownExtended->contentsList();
        $this->assertEquals($expected, $actual);
    }

    public function testTocDisabled()
    {
        $this->parsedownExtended->setSetting('headings.auto_anchors', true);
        $this->parsedownExtended->setSetting('toc', false);

        $markdown = <<<MARKDOWN
        [toc]
        # Heading 1
        ## Heading 1.1
        ## Heading 1.2
        # Heading 2
        ## Heading 2.1
        ## Heading 2.2
        MARKDOWN;

        $expected = <<<HTML
        <p>[toc]</p>
        <h1 id="heading-1">Heading 1</h1>
        <h2 id="heading-1-1">Heading 1.1</h2>
        <h2 id="heading-1-2">Heading 1.2</h2>
        <h1 id="heading-2">Heading 2</h1>
        <h2 id="heading-2-1">Heading 2.1</h2>
        <h2 id="heading-2-2">Heading 2.2</h2>
        HTML;

        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test case for table of contents with custom heading levels.
     */
    public function testTocWithCustomHeadingLevels()
    {
        $this->parsedownExtended->setSetting('headings.auto_anchors', true);
        $this->parsedownExtended->setSetting('toc.headings', ['h1', 'h2'], true);

        $markdown = <<<MARKDOWN
        # Heading 1
        ## Heading 1.1
        ## Heading 1.2
        ### Heading 1.2.1
        ### Heading 1.2.2
        # Heading 2
        ## Heading 2.1
        ## Heading 2.2
        ### Heading 2.2.1
        ### Heading 2.2.2
        MARKDOWN;

        $expected = <<<HTML
        <ul>
        <li><a href="#heading-1">Heading 1</a>
        <ul>
        <li><a href="#heading-1-1">Heading 1.1</a></li>
        <li><a href="#heading-1-2">Heading 1.2</a></li>
        </ul></li>
        <li><a href="#heading-2">Heading 2</a>
        <ul>
        <li><a href="#heading-2-1">Heading 2.1</a></li>
        <li><a href="#heading-2-2">Heading 2.2</a></li>
        </ul></li>
        </ul>
        HTML;

        $actual = $this->parsedownExtended->body($markdown);
        $actual = $this->parsedownExtended->contentsList();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test case for table of contents toc tag.
     */

    public function testTocTag()
    {
        $this->parsedownExtended->setSetting('headings.auto_anchors', true);
        $this->parsedownExtended->setSetting('toc.toc_tag', '[toc]');

        $markdown = <<<MARKDOWN
        [toc]
        # Heading 1
        ## Heading 1.1
        ## Heading 1.2
        # Heading 2
        ## Heading 2.1
        ## Heading 2.2
        MARKDOWN;

        $expected = <<<HTML
        <div id="toc"><ul>
        <li><a href="#heading-1">Heading 1</a>
        <ul>
        <li><a href="#heading-1-1">Heading 1.1</a></li>
        <li><a href="#heading-1-2">Heading 1.2</a></li>
        </ul></li>
        <li><a href="#heading-2">Heading 2</a>
        <ul>
        <li><a href="#heading-2-1">Heading 2.1</a></li>
        <li><a href="#heading-2-2">Heading 2.2</a></li>
        </ul></li>
        </ul></div>
        <h1 id="heading-1">Heading 1</h1>
        <h2 id="heading-1-1">Heading 1.1</h2>
        <h2 id="heading-1-2">Heading 1.2</h2>
        <h1 id="heading-2">Heading 2</h1>
        <h2 id="heading-2-1">Heading 2.1</h2>
        <h2 id="heading-2-2">Heading 2.2</h2>
        HTML;

        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }
}
