<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
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
        $this->assertTrue($config->get('code'), 'Code should resolve to code.enabled by default');
        $this->assertTrue($config->get('code.inline'), 'Inline code should be enabled by default');
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
     * Test setting a payload configuration value
     */
    public function testSetPayloadConfigValue()
    {
        $parsedownExtended = new ParsedownExtended();

        $parsedownExtended->config()->set('toc.id', 'contents');

        $this->assertEquals('contents', $parsedownExtended->config()->get('toc.id'), 'TOC ID payload should be updated');
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
     * Test setting nested configuration values with an array payload
     */
    public function testSetNestedConfigArrayValue()
    {
        $parsedownExtended = new ParsedownExtended();

        $parsedownExtended->config()->set('links.external_links', [
            'nofollow' => false,
            'noopener' => false,
        ]);

        $this->assertFalse($parsedownExtended->config()->get('links.external_links.nofollow'), 'Nested nofollow config should be false');
        $this->assertFalse($parsedownExtended->config()->get('links.external_links.noopener'), 'Nested noopener config should be false');
    }

    public function testExactArrayOptionIsStoredAsALeafValue(): void
    {
        $parsedownExtended = new ParsedownExtended();
        $delimiters = [
            ['left' => '$', 'right' => '$'],
            ['left' => '\\(', 'right' => '\\)'],
        ];

        $parsedownExtended->config()->set('math.inline.delimiters', $delimiters);

        $this->assertSame($delimiters, $parsedownExtended->config()->get('math.inline.delimiters'));
    }

    public function testGroupedConfigCanContainAnArrayLeafValue(): void
    {
        $parsedownExtended = new ParsedownExtended();
        $delimiters = [['left' => '\\(', 'right' => '\\)']];

        $parsedownExtended->config()->set('math.inline', [
            'enabled' => false,
            'delimiters' => $delimiters,
        ]);

        $this->assertFalse($parsedownExtended->config()->get('math.inline'));
        $this->assertSame($delimiters, $parsedownExtended->config()->get('math.inline.delimiters'));
    }

    public function testValueOnlyGroupCanBeSetWithoutBecomingAFeature(): void
    {
        $parsedownExtended = new ParsedownExtended();

        $parsedownExtended->config()->set('smartypants.substitutions', [
            'mdash' => '[em-dash]',
            'ndash' => '[en-dash]',
        ]);

        $this->assertSame('[em-dash]', $parsedownExtended->config()->get('smartypants.substitutions.mdash'));
        $this->assertSame('[en-dash]', $parsedownExtended->config()->get('smartypants.substitutions.ndash'));
        $this->assertArrayNotHasKey('smartypants.substitutions.enabled', $parsedownExtended->getFlatSchema());
    }

    public function testValueOnlyGroupCannotBeReadAsAFeature(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid config path: smartypants.substitutions');

        (new ParsedownExtended())->config()->get('smartypants.substitutions');
    }

    /**
     * Test constructor override application
     */
    public function testConstructorOverrides()
    {
        $parsedownExtended = new ParsedownExtended([
            'links' => [
                'external_links' => [
                    'nofollow' => false,
                ],
            ],
            'toc' => [
                'id' => 'contents',
            ],
        ]);

        $this->assertFalse($parsedownExtended->config()->get('links.external_links.nofollow'), 'Constructor override should update nested boolean config');
        $this->assertEquals('contents', $parsedownExtended->config()->get('toc.id'), 'Constructor override should update nested payload config');
    }

    public function testConstructorOverridesPreserveNestedArrayLeaves(): void
    {
        $delimiters = [['left' => '\\[', 'right' => '\\]']];
        $parsedownExtended = new ParsedownExtended([
            'math' => [
                'inline' => [
                    'delimiters' => $delimiters,
                ],
            ],
        ]);

        $this->assertSame($delimiters, $parsedownExtended->config()->get('math.inline.delimiters'));
    }

    /**
     * Test deprecated configuration paths
     */
    public function testDeprecatedConfigPaths()
    {
        $parsedownExtended = new ParsedownExtended();

        // Set and retrieve a deprecated path
        $parsedownExtended->config()->set('smartypants.substitutions.left_double_quote', '“');
        $this->assertEquals('“', $parsedownExtended->config()->get('smartypants.substitutions.left_double_quote'), 'Left double quote substitution should be set correctly');
    }

    /**
     * Test invalid configuration key path
     */
    public function testInvalidConfigKeyPath()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid config path: non.existent.path');

        $parsedownExtended = new ParsedownExtended();
        $parsedownExtended->config()->get('non.existent.path');
    }

    /**
     * Test invalid value type validation
     */
    public function testInvalidConfigValueType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected boolean, got string');

        $parsedownExtended = new ParsedownExtended();
        $parsedownExtended->config()->set('code.inline', 'false');
    }

    public function testInvalidHeadingReplacementRegexIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid config value for headings.auto_anchors.replacements'
        );

        $parsedownExtended = new ParsedownExtended();
        $parsedownExtended->config()->set('headings.auto_anchors.replacements', [
            '[' => 'replacement',
        ]);
    }

    public function testMalformedMathDelimiterIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid config value for math.block.delimiters'
        );

        $parsedownExtended = new ParsedownExtended();
        $parsedownExtended->config()->set('math.block.delimiters', [
            ['left' => '$$'],
        ]);
    }

    public function testInvalidHeadingLevelIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid config value for toc.levels'
        );

        $parsedownExtended = new ParsedownExtended();
        $parsedownExtended->config()->set('toc.levels', ['h1', 'h7']);
    }

    public function testEmptyAnchorDelimiterIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid config value for headings.auto_anchors.delimiter'
        );

        $parsedownExtended = new ParsedownExtended();
        $parsedownExtended->config()->set('headings.auto_anchors.delimiter', '');
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

    /**
     * Test flat schema exposure
     */
    public function testGetFlatSchema()
    {
        $parsedownExtended = new ParsedownExtended();
        $schema = $parsedownExtended->getFlatSchema();

        $this->assertIsArray($schema);
        $this->assertArrayHasKey('code.enabled', $schema);
        $this->assertArrayHasKey('toc.id', $schema);
        $this->assertSame('boolean', $schema['code.enabled']['type']);
        $this->assertTrue($schema['code.enabled']['default']);
        $this->assertSame('Enables code parsing.', $schema['code.enabled']['description']);
        $this->assertNull($schema['code.enabled']['validationRule']);
        $this->assertSame('code', $schema['code.enabled']['alias']);

        $this->assertSame('string', $schema['toc.id']['type']);
        $this->assertSame('toc', $schema['toc.id']['default']);
        $this->assertNull($schema['toc.id']['alias']);

        $this->assertSame('delimiter_pairs', $schema['math.inline.delimiters']['validationRule']);
        $this->assertArrayNotHasKey('smartypants.substitutions.enabled', $schema);
    }

    /**
     * Test configuration export
     */
    public function testConfigExport()
    {
        $parsedownExtended = new ParsedownExtended();
        $parsedownExtended->config()->set([
            'code.inline' => false,
            'toc.id' => 'contents',
        ]);

        $export = $parsedownExtended->config()->export();

        $this->assertFalse($export['code.inline']);
        $this->assertTrue($export['code.enabled']);
        $this->assertEquals('contents', $export['toc.id']);
    }

    /**
     * Test that config handlers remain isolated between ParsedownExtended instances.
     */
    public function testConfigHandlerIsolationBetweenInstances()
    {
        $first = new ParsedownExtended();
        $second = new ParsedownExtended();

        $firstConfig = $first->config();
        $secondConfig = $second->config();

        $this->assertNotSame($firstConfig, $secondConfig, 'Each instance should keep its own config handler');

        $firstConfig->set('emojis', false);

        $this->assertFalse($first->config()->get('emojis'), 'First instance should reflect its own config change');
        $this->assertTrue($second->config()->get('emojis'), 'Second instance should not be affected by first instance changes');
    }

    public function testRuntimeConfigSetAffectsLaterParses()
    {
        $parsedownExtended = new ParsedownExtended();

        $this->assertEquals('<p>Use <code>code</code> here</p>', $parsedownExtended->text('Use `code` here'));

        $parsedownExtended->config()->set('code.inline', false);

        $this->assertEquals('<p>Use `code` here</p>', $parsedownExtended->text('Use `code` here'));
    }

}
