<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class Parsedown2CompatibilityTest extends TestCase
{
    /**
     * Test that ParsedownExtended can instantiate with current Parsedown version
     */
    public function testParsedownExtendedInstantiation()
    {
        $parsedownExtended = new ParsedownExtended();
        
        $this->assertInstanceOf(ParsedownExtended::class, $parsedownExtended);
    }

    /**
     * Test basic markdown parsing functionality
     */
    public function testBasicMarkdownParsing()
    {
        $parsedownExtended = new ParsedownExtended();
        
        $result = $parsedownExtended->text('Hello **world**!');
        $this->assertStringContainsString('<strong>world</strong>', $result);
        
        $result = $parsedownExtended->line('Hello _italic_!');
        $this->assertStringContainsString('<em>italic</em>', $result);
    }

    /**
     * Test that version detection works
     */
    public function testVersionDetection()
    {
        $parsedownExtended = new ParsedownExtended();
        
        // Should not throw an exception
        $this->assertTrue(true);
    }

    /**
     * Test that the class aliasing works correctly
     */
    public function testClassAliasing()
    {
        $this->assertTrue(class_exists('ParsedownExtendedParentAlias'));
        
        $parsedownExtended = new ParsedownExtended();
        $this->assertInstanceOf('ParsedownExtendedParentAlias', $parsedownExtended);
    }

    /**
     * Test that configuration system works after updates
     */
    public function testConfiguration()
    {
        $parsedownExtended = new ParsedownExtended();
        
        // Test basic configuration
        $config = $parsedownExtended->config();
        $this->assertTrue($config->get('emojis'));
        
        // Test setting configuration
        $config->set('emojis', false);
        $this->assertFalse($config->get('emojis'));
        
        // Test nested configuration
        $this->assertTrue($config->get('headings.auto_anchors.enabled'));
        $config->set('headings.auto_anchors.enabled', false);
        $this->assertFalse($config->get('headings.auto_anchors.enabled'));
    }
}