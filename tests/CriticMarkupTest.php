<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class CriticMarkupTest extends TestCase
{
    protected ParsedownExtended $parsedownExtended;

    protected function setUp(): void
    {
        $this->parsedownExtended = new ParsedownExtended();
        $this->parsedownExtended->setSafeMode(true);
    }

    protected function tearDown(): void
    {
        unset($this->parsedownExtended);
    }

    public function testAddition()
    {
        $markdown = 'This is a {++test++}.';
        $expected = '<p>This is a <ins>test</ins>.</p>';
        $this->assertEquals($expected, $this->parsedownExtended->text($markdown));
    }

    public function testDeletion()
    {
        $markdown = 'Delete {--this--} word.';
        $expected = '<p>Delete <del>this</del> word.</p>';
        $this->assertEquals($expected, $this->parsedownExtended->text($markdown));
    }

    public function testSubstitution()
    {
        $markdown = 'I like {~~apples~>oranges~~}.';
        $expected = '<p>I like <del>apples</del><ins>oranges</ins>.</p>';
        $this->assertEquals($expected, $this->parsedownExtended->text($markdown));
    }

    public function testComment()
    {
        $markdown = 'Do it. {>>now<<}';
        $expected = '<p>Do it. <span class="critic comment">now</span></p>';
        $this->assertEquals($expected, $this->parsedownExtended->text($markdown));
    }

    public function testHighlight()
    {
        $markdown = 'This is {==important==}.';
        $expected = '<p>This is <mark>important</mark>.</p>';
        $this->assertEquals($expected, $this->parsedownExtended->text($markdown));
    }
}
