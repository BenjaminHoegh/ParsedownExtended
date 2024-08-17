<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class EmojiTest extends TestCase
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

    public function testEnableEmoji()
    {
        $this->parsedownExtended->config()->set('emojis', true);
        
        $markdown = ":smile:";
        $expectedHtml = '<p>😄</p>';

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testDisableEmoji()
    {
        $this->parsedownExtended->config()->set('emojis', false);
        
        $markdown = ":smile:";
        $expectedHtml = '<p>:smile:</p>';

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }
}
