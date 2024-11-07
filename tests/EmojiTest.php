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

        $markdown = ":grinning_face:";
        $expectedHtml = '<p>😀</p>';

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testDisableEmoji()
    {
        $this->parsedownExtended->config()->set('emojis', false);

        $markdown = ":grinning_face:";
        $expectedHtml = '<p>:grinning_face:</p>';

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    # Test that we dont parse emojis in code blocks
    public function testEmojiInCodeBlock()
    {
        $this->parsedownExtended->config()->set('emojis', true);

        $markdown = "```php\n:grinning_face:\n```";
        $expectedHtml = '<pre><code class="language-php">:grinning_face:</code></pre>';

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    # Test that we dont parse emojis in inline code
    public function testEmojiInInlineCode()
    {
        $this->parsedownExtended->config()->set('emojis', true);

        $markdown = "`:grinning_face:`";
        $expectedHtml = '<p><code>:grinning_face:</code></p>';

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    # Test that we dont parse emojis if the emoji code is inside a word (e.g. :grinning_face:ing or testing:grinning_face:)
    public function testEmojiInWord()
    {
        $this->parsedownExtended->config()->set('emojis', true);

        $markdown = "testing:grinning_face:";
        $expectedHtml = '<p>testing:grinning_face:</p>';

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);

        $markdown = ":grinning_face:ing";
        $expectedHtml = '<p>:grinning_face:ing</p>';

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }
}
