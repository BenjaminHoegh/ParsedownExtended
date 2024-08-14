<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class CodeTest extends TestCase
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

    public function testFencedCodeBlock()
    {
        $markdown = "```php\n<?php echo \"Hello, World!\";\n```";
        $expectedHtml = "<pre><code class=\"language-php\">&lt;?php echo \"Hello, World!\";</code></pre>";
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }

    public function testIndentedCodeBlock()
    {
        $markdown = "    <?php echo \"Indented Code\";";
        $expectedHtml = "<pre><code>&lt;?php echo \"Indented Code\";</code></pre>";
        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }
}
