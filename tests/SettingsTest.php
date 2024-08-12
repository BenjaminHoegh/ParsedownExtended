<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class ParsedownExtendedConfigTest extends TestCase
{
    /**
     * Test default configuration initialization
     */
    public function testDefaultConfigInitialization()
    {
        $parsedownExtended = new ParsedownExtended();

        // Retrieve the configuration object
        $config = $parsedownExtended->config();

        // Test some default settings
        $this->assertTrue($config->get('code.enabled'), 'Code blocks should be enabled by default');
        $this->assertTrue($config->get('emojis'), 'Emojis should be enabled by default');
        $this->assertFalse($config->get('diagrams.enabled'), 'Diagrams should be disabled by default');
    }

    /**
     * Test setting a configuration value
     */
    public function testSetConfigValue()
    {
        $parsedownExtended = new ParsedownExtended();

        // Set a new value
        $parsedownExtended->config()->set('emojis', false);

        // Assert the value has changed
        $this->assertFalse($parsedownExtended->config()->get('emojis'), 'Emojis should be disabled after setting to false');
    }

    /**
     * Test setting a nested configuration value
     */
    public function testSetNestedConfigValue()
    {
        $parsedownExtended = new ParsedownExtended();

        // Set a nested value
        $parsedownExtended->config()->set('headings.auto_anchors.lowercase', false);

        // Assert the value has changed
        $this->assertFalse($parsedownExtended->config()->get('headings.auto_anchors.lowercase'), 'Headings auto anchor lowercase should be false after setting');
    }

    /**
     * Test deprecated configuration paths
     */
    public function testDeprecatedConfigPaths()
    {
        $parsedownExtended = new ParsedownExtended();

        // Set and retrieve a deprecated path
        $parsedownExtended->config()->set('smarty.substitutions.left-double-quote', '“');
        $this->assertEquals('“', $parsedownExtended->config()->get('smarty.substitutions.left-double-quote'), 'Left double quote substitution should be set correctly');
    }

    /**
     * Test invalid configuration key path
     */
    public function testInvalidConfigKeyPath()
    {
        $this->expectException(InvalidArgumentException::class);

        $parsedownExtended = new ParsedownExtended();
        $parsedownExtended->config()->get('non.existent.path');
    }

    /**
     * Test multiple settings at once
     */
    public function testSetMultipleConfigValues()
    {
        $parsedownExtended = new ParsedownExtended();

        // Set multiple values
        $parsedownExtended->config()->set([
            'emojis' => false,
            'code.enabled' => false,
            'lists.tasks' => false,
        ]);

        // Assert the values have changed
        $this->assertFalse($parsedownExtended->config()->get('emojis'), 'Emojis should be disabled');
        $this->assertFalse($parsedownExtended->config()->get('code.enabled'), 'Code should be disabled');
        $this->assertFalse($parsedownExtended->config()->get('lists.tasks'), 'Task lists should be disabled');
    }
}
