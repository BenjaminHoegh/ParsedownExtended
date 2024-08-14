<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class CommentsTest extends TestCase
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

    public function testInlineComment()
    {
        $markdown = "<!-- This is a comment -->";
        $expectedHtml = "<!-- This is a comment -->";

        $this->parsedownExtended->setSafeMode(false); // Comments are not allowed in safe mode
        $this->parsedownExtended->config()->set('comments', true); // Comments are not turned into paragraphs

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }
}
