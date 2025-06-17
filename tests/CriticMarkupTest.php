<?php

use Erusev\Parsedown\Parsedown;
use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class CriticMarkupTest extends TestCase
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

    public function testAddition()
    {
        $markdown = 'This is a {++test++}.';
        $expected = '<p>This is a <ins>test</ins>.</p>';
        $this->assertEquals($expected, $this->parsedown->toHtml($markdown));
    }

    public function testDeletion()
    {
        $markdown = 'Delete {--this--} word.';
        $expected = '<p>Delete <del>this</del> word.</p>';
        $this->assertEquals($expected, $this->parsedown->toHtml($markdown));
    }

    public function testSubstitution()
    {
        $markdown = 'I like {~~apples~>oranges~~}.';
        $expected = '<p>I like <del>apples</del><ins>oranges</ins>.</p>';
        $this->assertEquals($expected, $this->parsedown->toHtml($markdown));
    }

    public function testComment()
    {
        $markdown = 'Do it. {>>now<<}';
        $expected = '<p>Do it. <span class="critic comment">now</span></p>';
        $this->assertEquals($expected, $this->parsedown->toHtml($markdown));
    }

    public function testHighlight()
    {
        $markdown = 'This is {==important==}.';
        $expected = '<p>This is <mark>important</mark>.</p>';
        $this->assertEquals($expected, $this->parsedown->toHtml($markdown));
    }
}
