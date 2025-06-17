<?php

use Erusev\Parsedown\Parsedown;
use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class TocTest extends TestCase
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
     * Test case for table of contents.
     */
    public function testTocEnabled()
    {

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

        $actual = $this->parsedown->body($markdown);
        $actual = $this->parsedown->contentsList();
        $this->assertEquals($expected, $actual);
    }

    public function testTocDisabled()
    {

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

        $actual = $this->parsedown->toHtml($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test case for table of contents with custom heading levels.
     */
    public function testTocWithCustomHeadingLevels()
    {

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

        $actual = $this->parsedown->body($markdown);
        $actual = $this->parsedown->contentsList();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test case for table of contents toc tag.
     */

    public function testTocTag()
    {

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

        $actual = $this->parsedown->toHtml($markdown);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test case for table of contents with multiple settings at once.
     */

    public function testTocMultipleSettings()
    {
            'tag' => '[custom-toc-tag]',
            'levels' => ['h1', 'h2'],
        ]);

        $markdown = <<<MARKDOWN
            [custom-toc-tag]
            # Heading 1
            ## Heading 1.1
            ## Heading 1.2
            ### Heading 1.2.1
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
            <h3 id="heading-1-2-1">Heading 1.2.1</h3>
            <h1 id="heading-2">Heading 2</h1>
            <h2 id="heading-2-1">Heading 2.1</h2>
            <h2 id="heading-2-2">Heading 2.2</h2>
            HTML;

        $actual = $this->parsedown->toHtml($markdown);
        $this->assertEquals($expected, $actual);
    }
}
