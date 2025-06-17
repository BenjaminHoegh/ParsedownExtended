<?php

use Erusev\Parsedown\Parsedown;
use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class EmojiTest extends TestCase
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

    public function testEnableEmoji()
    {

        $markdown = ":grinning_face:";
        $expectedHtml = '<p>😀</p>';

        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testDisableEmoji()
    {

        $markdown = ":grinning_face:";
        $expectedHtml = '<p>:grinning_face:</p>';

        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    # Test that we dont parse emojis in code blocks
    public function testEmojiInCodeBlock()
    {

        $markdown = "```php\n:grinning_face:\n```";
        $expectedHtml = '<pre><code class="language-php">:grinning_face:</code></pre>';

        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    # Test that we dont parse emojis in inline code
    public function testEmojiInInlineCode()
    {

        $markdown = "`:grinning_face:`";
        $expectedHtml = '<p><code>:grinning_face:</code></p>';

        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    # Test that we dont parse emojis if the emoji code is inside a word (e.g. :grinning_face:ing or testing:grinning_face:)
    public function testEmojiInWord()
    {

        $markdown = "testing:grinning_face:";
        $expectedHtml = '<p>testing:grinning_face:</p>';

        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);

        $markdown = ":grinning_face:ing";
        $expectedHtml = '<p>:grinning_face:ing</p>';

        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }
}
