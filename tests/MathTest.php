<?php

use Erusev\Parsedown\Parsedown;
use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class MathTest extends TestCase
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

    public function testInlineMath()
    {
        $markdown = '$E=mc^2$';
        $expectedHtml = '<p>$E=mc^2$</p>';

        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testBlockMath()
    {
        $markdown = '$$E=mc^2$$';
        $expectedHtml = 'E=mc^2';

        $result = $this->parsedown->toHtml($markdown);

        $this->assertEquals($expectedHtml, $result);
    }

    public function testInlineMathWithPunctuation()
    {
        // Test that inline math works correctly when followed by various punctuation
        // This addresses the issue where certain punctuation would prevent math detection

        $testCases = [
            'Math with semicolon: $F=ma$; force formula' => '<p>Math with semicolon: $F=ma$; force formula</p>',
            'Math with colon: $E=mc^2$: energy formula' => '<p>Math with colon: $E=mc^2$: energy formula</p>',
            'Math with exclamation: $x=3$! Amazing!' => '<p>Math with exclamation: $x=3$! Amazing!</p>',
            'Math with question: $x^2=1$? Solutions exist.' => '<p>Math with question: $x^2=1$? Solutions exist.</p>',
            'Math with parenthesis: The value ($x=5$) is constant.' => '<p>Math with parenthesis: The value ($x=5$) is constant.</p>',
            'Multiple math: $a=1$, $b=2$; $c=3$!' => '<p>Multiple math: $a=1$, $b=2$; $c=3$!</p>',
        ];

        foreach ($testCases as $markdown => $expectedHtml) {
            $result = $this->parsedown->toHtml($markdown);
            $this->assertEquals($expectedHtml, $result, "Failed for: $markdown");
        }
    }

    public function testInlineMathWithFollowingElements()
    {
        // Test that inline math doesn't interfere with subsequent markdown elements

        $markdown = '$E=mc^2$

> This is a blockquote';
        
        $result = $this->parsedown->toHtml($markdown);
        
        // Should contain both the math expression and the blockquote
        $this->assertStringContainsString('$E=mc^2$', $result);
        $this->assertStringContainsString('<blockquote>', $result);
        $this->assertStringContainsString('This is a blockquote', $result);
    }

    public function testOriginalIssueCase()
    {
        // Test the specific case mentioned in GitHub issue #65

        $markdown = '$C=(1,0,1,0;)$, $CA=(0,1,0,1;)$, $CA^2=(b,0,a,0;)$, $CA^3=(0,b,0,a;)$

> La matrice d\'observabilité s\'écrit donc :';

        $result = $this->parsedown->toHtml($markdown);
        
        // Should preserve all 4 math expressions
        $mathCount = substr_count($result, '$') / 2;
        $this->assertEquals(4, $mathCount, 'All math expressions should be preserved');
        
        // Should render the blockquote correctly
        $this->assertStringContainsString('<blockquote>', $result);
        $this->assertStringContainsString('La matrice', $result);
    }
}
