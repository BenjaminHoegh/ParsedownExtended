<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class TablesTest extends TestCase
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

    public function testEnableTables(): void
    {
        $this->parsedownExtended->config()->set('tables', true);

        $markdown = <<<MARKDOWN
        | Header 1 | Header 2 |
        | -------- | -------- |
        | Cell 1   | Cell 2   |
        MARKDOWN;

        $expectedHtml = <<<HTML
        <table>
        <thead>
        <tr>
        <th>Header 1</th>
        <th>Header 2</th>
        </tr>
        </thead>
        <tbody>
        <tr>
        <td>Cell 1</td>
        <td>Cell 2</td>
        </tr>
        </tbody>
        </table>
        HTML;

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals(trim($expectedHtml), trim($result));
    }

    public function testDisableTables(): void
    {
        $this->parsedownExtended->config()->set('tables', false);

        $markdown = <<<MARKDOWN
        | Header 1 | Header 2 |
        | -------- | -------- |
        | Cell 1   | Cell 2   |
        MARKDOWN;

        $expectedHtml = <<<HTML
        <p>| Header 1 | Header 2 |
        | -------- | -------- |
        | Cell 1   | Cell 2   |</p>
        HTML;

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals(trim($expectedHtml), trim($result));
    }

    public function testTableAlign(): void
    {
        $this->parsedownExtended->config()->set('tables', true);

        $markdown = <<<MARKDOWN
        | Left-aligned | Center-aligned | Right-aligned |
        | :---         |     :---:      |          ---: |
        | git status   | git status     | git status    |
        | git diff     | git diff       | git diff      |
        MARKDOWN;

        $expectedHtml = <<<HTML
        <table>
        <thead>
        <tr>
        <th style="text-align: left;">Left-aligned</th>
        <th style="text-align: center;">Center-aligned</th>
        <th style="text-align: right;">Right-aligned</th>
        </tr>
        </thead>
        <tbody>
        <tr>
        <td style="text-align: left;">git status</td>
        <td style="text-align: center;">git status</td>
        <td style="text-align: right;">git status</td>
        </tr>
        <tr>
        <td style="text-align: left;">git diff</td>
        <td style="text-align: center;">git diff</td>
        <td style="text-align: right;">git diff</td>
        </tr>
        </tbody>
        </table>
        HTML;

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testTableSpan(): void
    {
        $this->parsedownExtended->config()->set('tables', true);
        $this->parsedownExtended->config()->set('tables.tablespan', true);

        $markdown = <<<MARKDOWN
        | >     | >           |   Colspan       | >           | for thead |
        | ----- | ----------- | --------------- | ----------- | --------- |
        | Lorem | ipsum       |    dolor        | sit         | amet      |
        | ^     | -           |      >          | more words  | .         |
        | ,     | >           | some long text  | >           | 2x2 cell  |
        | >     | another 2x2 |      +          | >           | ^         |
        | >     | ^           |                 |             | !         |

        MARKDOWN;

        $expectedHtml = <<<HTML
        <table>
        <thead>
        <tr>
        <th colspan="3">Colspan</th>
        <th colspan="2">for thead</th>
        </tr>
        </thead>
        <tbody>
        <tr>
        <td rowspan="2">Lorem</td>
        <td>ipsum</td>
        <td>dolor</td>
        <td>sit</td>
        <td>amet</td>
        </tr>
        <tr>
        <td>-</td>
        <td colspan="2">more words</td>
        <td>.</td>
        </tr>
        <tr>
        <td>,</td>
        <td colspan="2">some long text</td>
        <td colspan="2" rowspan="2">2x2 cell</td>
        </tr>
        <tr>
        <td colspan="2" rowspan="2">another 2x2</td>
        <td>+</td>
        </tr>
        <tr>
        <td></td>
        <td></td>
        <td>!</td>
        </tr>
        </tbody>
        </table>
        HTML;

        $result = $this->parsedownExtended->text($markdown);

        $this->assertEquals(trim($expectedHtml), trim($result));
    }
}
