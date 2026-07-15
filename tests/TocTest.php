<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class TocTest extends TestCase
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
     * Test case for table of contents.
     */
    public function testTocEnabled()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', true);
        $this->parsedownExtended->config()->set('toc', true);

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
            <li><a href="#heading-1">Heading 1</a><ul>
            <li><a href="#heading-1-1">Heading 1.1</a></li>
            <li><a href="#heading-1-2">Heading 1.2</a></li>
            </ul>
            </li>
            <li><a href="#heading-2">Heading 2</a><ul>
            <li><a href="#heading-2-1">Heading 2.1</a></li>
            <li><a href="#heading-2-2">Heading 2.2</a></li>
            </ul>
            </li>
            </ul>
            HTML;

        $actual = $this->parsedownExtended->body($markdown);
        $actual = $this->parsedownExtended->contentsList();
        $this->assertEquals($expected, $actual);
    }

    public function testTocDisabled()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', true);
        $this->parsedownExtended->config()->set('toc', false);

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

    public function testTextWithoutTocTag()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', true);

        $markdown = '# Heading 1';
        $expected = '<h1 id="heading-1">Heading 1</h1>';

        $actual = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expected, $actual);
    }

    public function testContentsListAvailableAfterTextWithoutTocTag()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', true);

        $this->parsedownExtended->text('# Heading 1');

        $expected = <<<HTML
            <ul>
            <li><a href="#heading-1">Heading 1</a></li>
            </ul>
            HTML;

        $this->assertEquals($expected, $this->parsedownExtended->contentsList());
    }

    /**
     * Test case for table of contents with custom heading levels.
     */
    public function testTocWithCustomHeadingLevels()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', true);
        $this->parsedownExtended->config()->set('toc.levels', ['h1', 'h2']);

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
            <li><a href="#heading-1">Heading 1</a><ul>
            <li><a href="#heading-1-1">Heading 1.1</a></li>
            <li><a href="#heading-1-2">Heading 1.2</a></li>
            </ul>
            </li>
            <li><a href="#heading-2">Heading 2</a><ul>
            <li><a href="#heading-2-1">Heading 2.1</a></li>
            <li><a href="#heading-2-2">Heading 2.2</a></li>
            </ul>
            </li>
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
        $this->parsedownExtended->config()->set('headings.auto_anchors', true);
        $this->parsedownExtended->config()->set('toc.tag', '[toc]');

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
            <li><a href="#heading-1">Heading 1</a><ul>
            <li><a href="#heading-1-1">Heading 1.1</a></li>
            <li><a href="#heading-1-2">Heading 1.2</a></li>
            </ul>
            </li>
            <li><a href="#heading-2">Heading 2</a><ul>
            <li><a href="#heading-2-1">Heading 2.1</a></li>
            <li><a href="#heading-2-2">Heading 2.2</a></li>
            </ul>
            </li>
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

    public function testTocWithCustomId()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', true);
        $this->parsedownExtended->config()->set('toc.tag', '[toc]');
        $this->parsedownExtended->config()->set('toc.id', 'contents');

        $markdown = <<<MARKDOWN
            [toc]
            # Heading 1
            MARKDOWN;

        $expected = <<<HTML
            <div id="contents"><ul>
            <li><a href="#heading-1">Heading 1</a></li>
            </ul></div>
            <h1 id="heading-1">Heading 1</h1>
            HTML;

        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    public function testTocUsesExplicitIdWhenAutomaticAnchorsAreDisabled(): void
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', false);

        $actual = $this->parsedownExtended->text("[TOC]\n\n# Heading {#custom}");

        $this->assertStringContainsString('<a href="#custom">Heading</a>', $actual);
        $this->assertStringNotContainsString('<a href="#">', $actual);
    }

    public function testTocSkipsHeadingsWithoutAnId(): void
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', false);

        $actual = $this->parsedownExtended->text("[TOC]\n\n# Heading");

        $this->assertStringNotContainsString('<a href="#">', $actual);
        $this->assertStringContainsString('<h1>Heading</h1>', $actual);
    }

    public function testTocClampsSkippedHeadingLevels(): void
    {
        $this->parsedownExtended->body("# One\n\n### Three");

        $expected = <<<HTML
            <ul>
            <li><a href="#one">One</a><ul>
            <li><a href="#three">Three</a></li>
            </ul>
            </li>
            </ul>
            HTML;

        $actual = $this->parsedownExtended->contentsList();

        $this->assertSame($expected, $actual);
        $this->assertStringNotContainsString("<ul>\n<ul>", $actual);
    }

    public function testCustomTocTagCannotBypassSafeMode()
    {
        $tag = '<img src=x onerror=alert(1)>';
        $this->parsedownExtended->config()->set('toc.tag', $tag);

        $actual = $this->parsedownExtended->text("Before {$tag} after");

        $this->assertSame(
            '<p>Before &lt;img src=x onerror=alert(1)&gt; after</p>',
            $actual
        );
        $this->assertStringNotContainsString('<img', $actual);
    }

    public function testCustomTocIdIsEscaped()
    {
        $this->parsedownExtended->config()->set('toc.id', 'contents" onmouseover="alert(1)');

        $actual = $this->parsedownExtended->text("[TOC]\n\n# Heading");

        $this->assertStringContainsString(
            '<div id="contents&quot; onmouseover=&quot;alert(1)">',
            $actual
        );
        $this->assertStringNotContainsString('onmouseover="alert(1)"', $actual);
    }

    /**
     * Test case for table of contents with multiple settings at once.
     */

    public function testTocMultipleSettings()
    {
        $this->parsedownExtended->config()->set('toc', [
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
            <li><a href="#heading-1">Heading 1</a><ul>
            <li><a href="#heading-1-1">Heading 1.1</a></li>
            <li><a href="#heading-1-2">Heading 1.2</a></li>
            </ul>
            </li>
            <li><a href="#heading-2">Heading 2</a><ul>
            <li><a href="#heading-2-1">Heading 2.1</a></li>
            <li><a href="#heading-2-2">Heading 2.2</a></li>
            </ul>
            </li>
            </ul></div>
            <h1 id="heading-1">Heading 1</h1>
            <h2 id="heading-1-1">Heading 1.1</h2>
            <h2 id="heading-1-2">Heading 1.2</h2>
            <h3 id="heading-1-2-1">Heading 1.2.1</h3>
            <h1 id="heading-2">Heading 2</h1>
            <h2 id="heading-2-1">Heading 2.1</h2>
            <h2 id="heading-2-2">Heading 2.2</h2>
            HTML;

        $actual = $this->parsedownExtended->text($markdown);
        $this->assertEquals($expected, $actual);
    }

    public function testContentsListJsonFallbackOnEncodingFailure()
    {
        $contentsProperty = new ReflectionProperty(ParsedownExtended::class, 'contentsList');
        $contentsProperty->setAccessible(true);
        $contentsProperty->setValue($this->parsedownExtended, [NAN]);

        $actual = $this->parsedownExtended->contentsList('json');

        $this->assertSame('[]', $actual);
    }

    public function testContentsListJson()
    {
        $markdown = <<<MARKDOWN
            # Heading 1
            ## Heading 2
            MARKDOWN;

        $this->parsedownExtended->body($markdown);

        $expected = '[{"text":"Heading 1","id":"heading-1","level":"h1"},{"text":"Heading 2","id":"heading-2","level":"h2"}]';
        $actual = $this->parsedownExtended->contentsList('json');

        $this->assertSame($expected, $actual);
    }

    public function testContentsListJsonAvailableAfterTextWithTocTag()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', true);

        $this->parsedownExtended->text("[TOC]\n\n# Heading");

        $expected = '[{"text":"Heading","id":"heading","level":"h1"}]';
        $actual = $this->parsedownExtended->contentsList('json');

        $this->assertSame($expected, $actual);
    }

    public function testTocStateResetsBetweenParses()
    {
        $this->parsedownExtended->config()->set('headings.auto_anchors', true);

        $this->parsedownExtended->text('# Heading');

        $markdown = <<<MARKDOWN
            [TOC]
            # Heading
            MARKDOWN;

        $expected = <<<HTML
            <div id="toc"><ul>
            <li><a href="#heading">Heading</a></li>
            </ul></div>
            <h1 id="heading">Heading</h1>
            HTML;

        $actual = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expected, $actual);
    }

    public function testTocStateDoesNotLeakBetweenParses()
    {
        $first = $this->parsedownExtended->text("[TOC]\n\n# First");
        $second = $this->parsedownExtended->text("[TOC]\n\n# Second");

        $this->assertStringContainsString('#first', $first);
        $this->assertStringNotContainsString('#first', $second);
        $this->assertStringContainsString('#second', $second);
    }

    public function testContentsListInvalidReturnType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Unknown return type 'xml' given while parsing ToC.");

        $this->parsedownExtended->contentsList('xml');
    }
}
