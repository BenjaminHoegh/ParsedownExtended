<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;
use PHPUnit\Framework\TestCase;

class SettingsTest extends TestCase
{
    protected $ParsedownExtended;

    protected function setUp(): void
    {
        $this->ParsedownExtended = new ParsedownExtended();
        // $this->ParsedownExtended->setSafeMode(true); // As we always want to support safe mode
    }

    protected function tearDown(): void
    {
        # Ensure that the ParsedownExtended instance is destroyed after each test
        unset($this->ParsedownExtended);
    }


    public function testSetTopLevelSimpleKey()
    {
        # Enable
        $this->ParsedownExtended->config()->set('comments', true);
        $result = $this->ParsedownExtended->config()->get('comments');
        $this->assertTrue($result);
        $this->assertIsBool($result);

        # Disable
        $this->ParsedownExtended->config()->set('comments', false);
        $result = $this->ParsedownExtended->config()->get('comments');
        $this->assertFalse($result);
        $this->assertIsBool($result);
    }

    public function testSetTopLevelAdvanceKey()
    {
        # Enable
        $this->ParsedownExtended->config()->set('abbreviations', true);
        $result = $this->ParsedownExtended->config()->get('abbreviations');
        $this->assertTrue($result);
        $this->assertIsBool($result);

        # Disable
        $this->ParsedownExtended->config()->set('abbreviations', false);
        $result = $this->ParsedownExtended->config()->get('abbreviations');
        $this->assertFalse($result);
        $this->assertIsBool($result);
    }

    public function testSetNestedSimpleKey()
    {
        # Enable
        $this->ParsedownExtended->config()->set('tables.tablespan', true);
        $result = $this->ParsedownExtended->config()->get('tables.tablespan');
        $this->assertTrue($result);
        $this->assertIsBool($result);

        # Disable
        $this->ParsedownExtended->config()->set('tables.tablespan', false);
        $result = $this->ParsedownExtended->config()->get('tables.tablespan');
        $this->assertFalse($result);
        $this->assertIsBool($result);
    }

    public function testSetNestedAdvanceKey()
    {
        # Enable
        $this->ParsedownExtended->config()->set('math.block', true);
        $result = $this->ParsedownExtended->config()->get('math.block');
        $this->assertTrue($result);
        $this->assertIsBool($result);

        # Disable
        $this->ParsedownExtended->config()->set('math.block', false);
        $result = $this->ParsedownExtended->config()->get('math.block');
        $this->assertFalse($result);
        $this->assertIsBool($result);
    }

    public function testSetSection()
    {
        // Enable
        $this->ParsedownExtended->config()->set('math', true);
        $result = $this->ParsedownExtended->config()->get('math');
        $this->assertTrue($result);
        $this->assertIsBool($result);

        // Disable
        $this->ParsedownExtended->config()->set('math', false);
        $result = $this->ParsedownExtended->config()->get('math');
        $this->assertFalse($result);
        $this->assertIsBool($result);
    }

    public function testSetNestedSection()
    {
        // Enable
        $this->ParsedownExtended->config()->set('math.block', true);
        $result = $this->ParsedownExtended->config()->get('math.block');
        $this->assertTrue($result);
        $this->assertIsBool($result);

        // Disable
        $this->ParsedownExtended->config()->set('math.block', false);
        $result = $this->ParsedownExtended->config()->get('math.block');
        $this->assertFalse($result);
        $this->assertIsBool($result);
    }


    public function testInvalidKeyPath()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->ParsedownExtended->config()->get('nonexistent.key');
    }

    public function testInvalidTypeSet()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->ParsedownExtended->config()->set('comments', 'not-a-boolean');
    }
}
