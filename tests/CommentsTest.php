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

    public function testEnableComment()
    {
        
        $this->parsedownExtended->setSafeMode(false); // Comments are not allowed in safe mode
        $this->parsedownExtended->config()->set('comments', true); // Comments are not turned into paragraphs
        
        $markdown = "<!-- This is a comment -->";
        $expectedHtml = "<!-- This is a comment -->";
        
        $result = $this->parsedownExtended->text($markdown);
        
        $this->assertEquals($expectedHtml, $result);
    }
    
    public function testDisableComment()
    {
        $this->parsedownExtended->setSafeMode(false); // Comments are not allowed in safe mode
        $this->parsedownExtended->config()->set('comments', false); // Comments are turned into paragraphs
        
        $markdown = "<!-- This is a comment -->";
        $expectedHtml = "<p><!-- This is a comment --></p>";

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }
}
