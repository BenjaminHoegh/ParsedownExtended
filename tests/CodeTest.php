<?php

use Erusev\Parsedown\Parsedown;
use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class CodeTest extends TestCase
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

    public function testEnableCode()
    {

        $markdown = "```php\n<?php echo \"Hello, World!\";\n```";
        $expectedHtml = "<pre><code class=\"language-php\">&lt;?php echo \"Hello, World!\";</code></pre>";

        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }

    public function testDisableCode()
    {

        $markdown = "```php\n<?php echo \"Hello, World!\";\n```";
        $expectedHtml = "<p>```php\n&lt;?php echo &quot;Hello, World!&quot;;\n```</p>";

        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }

    public function testFencedCodeBlock()
    {
        $markdown = "```php\n<?php echo \"Hello, World!\";\n```";
        $expectedHtml = "<pre><code class=\"language-php\">&lt;?php echo \"Hello, World!\";</code></pre>";

        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }

    public function testIndentedCodeBlock()
    {
        $markdown = "    <?php echo \"Indented Code\";";
        $expectedHtml = "<pre><code>&lt;?php echo \"Indented Code\";</code></pre>";
        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }
}
