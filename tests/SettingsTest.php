<?php

use PHPUnit\Framework\TestCase;

class SettingsTest extends TestCase
{
    protected $ParsedownExtended;

    protected function setUp(): void
    {
        $this->ParsedownExtended = new ParsedownExtended();
        $this->ParsedownExtended->setSafeMode(true); // As we always want to support safe mode
    }

    protected function tearDown(): void
    {
        unset($this->ParsedownExtended);
    }


    public function testGetSetting()
    {
        $setting = $this->ParsedownExtended->settings()->get('emphasis');
        $this->assertIsArray($setting);
        $this->assertArrayHasKey('italic', $setting);

        // test nested setting
        $setting = $this->ParsedownExtended->settings()->get('emphasis.italic');
        $this->assertIsBool($setting);

        // test on boolean value
        $setting = $this->ParsedownExtended->settings()->get('comments');
        $this->assertIsBool($setting);

        // test on string value
        $setting = $this->ParsedownExtended->settings()->get('toc.toc_tag');
        $this->assertIsString($setting);
    }


    public function testSetSetting()
    {
        // Test setting boolean value on top level setting (hidden enabled key)
        $this->ParsedownExtended->settings()->set('emphasis', false);
        $this->assertFalse($this->ParsedownExtended->settings()->isEnabled('emphasis'));

        $this->ParsedownExtended->settings()->set('emphasis', true);
        $this->assertTrue($this->ParsedownExtended->settings()->isEnabled('emphasis'));

        // Test setting boolean value on boolean value
        $this->ParsedownExtended->settings()->set('comments', true);
        $this->assertTrue($this->ParsedownExtended->settings()->isEnabled('comments'));

        // Test setting nested value
        $this->ParsedownExtended->settings()->set('emphasis.italic', true);
        $this->assertTrue($this->ParsedownExtended->settings()->isEnabled('emphasis.italic'));

        // Test setting string
        $this->ParsedownExtended->settings()->set('toc.toc_tag', '[[toc]]');
        $this->assertEquals('[[toc]]', $this->ParsedownExtended->settings()->get('toc.toc_tag'));

        // Don't have a test for setting an integer value as we don't have any settings that are integers
    }

    public function testIsEnabled()
    {
        // test top level setting (hidden enabled key)
        $this->ParsedownExtended->settings()->set('emphasis', true);
        $this->assertTrue($this->ParsedownExtended->settings()->isEnabled('emphasis'));

        // test boolean value
        $this->ParsedownExtended->settings()->set('comments', false);
        $this->assertFalse($this->ParsedownExtended->settings()->isEnabled('comments'));
    }

    public function testSetMultiple()
    {
        $this->ParsedownExtended->settings()->set([
            'diagrams' => true,
            'comments' => false,
            'emphasis.bold' => false,
            'headings.auto_anchors' => false,
        ]);
        $this->assertTrue($this->ParsedownExtended->settings()->isEnabled('diagrams'));
        $this->assertFalse($this->ParsedownExtended->settings()->isEnabled('comments'));
        $this->assertFalse($this->ParsedownExtended->settings()->isEnabled('emphasis.bold'));
        $this->assertFalse($this->ParsedownExtended->settings()->isEnabled('headings.auto_anchors'));
    }

    public function testSetArray()
    {
        $this->ParsedownExtended->settings()->set('math.inline.delimiters', [['left' => '$', 'right' => '$']]);
        $this->assertEquals([['left' => '$', 'right' => '$']], $this->ParsedownExtended->settings()->get('math.inline.delimiters'));
    }

    public function testNonExistentSetting()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->ParsedownExtended->settings()->set('invalid.setting', true);

        $this->expectException(InvalidArgumentException::class);
        $this->ParsedownExtended->settings()->get('invalid.setting');

        $this->expectException(InvalidArgumentException::class);
        $this->ParsedownExtended->settings()->isEnabled('invalid.setting');

        $this->expectException(InvalidArgumentException::class);
        $this->ParsedownExtended->settings()->set([
            'invalid.setting' => true,
            'another.invalid.setting' => false,
        ]);
    }

    public function testInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->ParsedownExtended->settings()->set('emphasis.bold', 123);

        $this->expectException(InvalidArgumentException::class);
        $this->ParsedownExtended->settings()->set([
            'emphasis.bold' => 123,
        ]);
    }

    public function testInvalidTypeParentKey()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->ParsedownExtended->settings()->set('emphasis', 123);

        $this->expectException(InvalidArgumentException::class);
        $this->ParsedownExtended->settings()->set([
            'emphasis' => 123,
        ]);
    }


    public function testSettingArray(): void
    {
        $this->parsedownExtended->setSetting('math', [
            'inline' => [
                'delimiters' => [
                    ['$', '$'],
                    ['\\(', '\\)']
                ],
            ],
            'block' => [
                'delimiters' => [
                    ['$$', '$$'],
                    ['\\[', '\\]']
                ],
            ]
        ]);
        $this->assertTrue($this->parsedownExtended->isEnabled('math'));
        $this->assertTrue($this->parsedownExtended->isEnabled('math.inline'));
    }
}
