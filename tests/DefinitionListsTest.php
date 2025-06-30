<?php

use Erusev\Parsedown\State;
use Erusev\Parsedown\Parsedown;
use Erusev\ParsedownExtra\ParsedownExtra;
use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class DefinitionListsTest extends TestCase
{
    protected Parsedown $parsedownExtended;

    protected function setUp(): void
    {
        $this->parsedownExtended = new Parsedown(ParsedownExtended::from(new State()));

    }

    protected function tearDown(): void
    {
        unset($this->parsedownExtended);
    }

    public function testEnableDefinitionList()
    {
        $this->parsedownExtended->config()->set('definition_lists', true);

        $markdown = "Term\n: Definition";
        $expectedHtml = "<dl>\n<dt>Term</dt>\n<dd>Definition</dd>\n</dl>";

        $result = $this->parsedownExtended->toHtml($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }

    public function testDisableDefinitionList()
    {
        $this->parsedownExtended->config()->set('definition_lists', false);

        $markdown = "Term\n: Definition";
        $expectedHtml = "<p>Term\n: Definition</p>";

        $result = $this->parsedownExtended->toHtml($markdown);

        $this->assertEquals($expectedHtml, trim($result));
    }
}
