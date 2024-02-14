<?php

use PHPUnit\Framework\TestCase;

class InitializeSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        $this->parsedownExtended = new ParsedownExtended();
        $this->parsedownExtended->setSafeMode(true); // As we always want to support safe mode
    }

    protected function tearDown(): void
    {
        unset($this->parsedownExtended);
    }

    /**
     * Invokes a protected or private method of an object using reflection.
     *
     * @param object $object The object whose method needs to be invoked.
     * @param string $methodName The name of the method to be invoked.
     * @param array $parameters An array of parameters to be passed to the method.
     * @return mixed The result of the method invocation.
     */
    protected function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }


    /**
     * Accesses a protected or private property of an object using reflection.
     *
     * @param object $object The object whose property needs to be accessed.
     * @param string $propertyName The name of the property to be accessed.
     * @return ReflectionProperty The accessed property.
     */
    protected function accessProperty(&$object, $propertyName)
    {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }


    public function testSetSettingSingleValue(): void
    {
        $this->parsedownExtended->setSetting('emphasis', false);
        $this->assertEquals(false, $this->parsedownExtended->isEnabled('emphasis'));
    }

    public function testSetSettingNestedValue(): void
    {
        $this->parsedownExtended->setSetting('emphasis.italic', false);
        $this->assertEquals(false, $this->parsedownExtended->isEnabled('emphasis.italic'));
    }

    public function testSetSettingBooleanValue(): void
    {
        $this->parsedownExtended->setSetting('emphasis', true);
        $this->assertEquals(true, $this->parsedownExtended->isEnabled('emphasis'));
    }

    public function testSetSettingArrayValue(): void
    {
        // Get the default setting
        $defaultSetting = $this->accessProperty($this->parsedownExtended, 'defaultSettings');

        // setting the new value
        $newSetting = ['italic' => false, 'bold' => false];

        // expected setting should be the default setting with the new value merged
        $expectedSetting = array_merge($defaultSetting['emphasis'], $newSetting);

        $this->parsedownExtended->setSetting('emphasis', $newSetting);
        $this->assertEquals($expectedSetting, $this->parsedownExtended->getSetting('emphasis'));
    }
}
