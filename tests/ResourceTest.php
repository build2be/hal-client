<?php

class ResourceTest extends PHPUnit_Framework_TestCase
{
    public function testParseEmbed()
    {
        $resource = new \HalClient\Resource();
        $parseEmbed = $this->getPrivateMethod('\\HalClient\\Resource', 'parseEmbed');
        $fixture = $this->getFixture('rfc_example.json');

        $parseEmbed->invokeArgs($resource, array($fixture['_embedded']['orders'][0]));

        $this->assertArrayHasKey('/orders/123', PHPUnit_Framework_Assert::readAttribute($resource, 'cache'));
        $parsedEmbed = PHPUnit_Framework_Assert::readAttribute($resource, 'cache')['/orders/123'];

        $this->assertTrue($parsedEmbed->isFromEmbed());

        $properties = $parsedEmbed->getProperties();

        $this->assertArrayNotHasKey('_links', $properties);
        $this->assertArrayNotHasKey('_embedded', $properties);

        $this->assertArrayHasKey('total', $properties);
        $this->assertEquals(30.00, $properties['total']);
        $this->assertArrayHasKey('currency', $properties);
        $this->assertEquals('USD', $properties['currency']);
        $this->assertArrayHasKey('status', $properties);
        $this->assertEquals('shipped', $properties['status']);
    }

    public function testGetProperties()
    {
        $resource = \HalClient\Resource::fromJsonResponse($this->getFixture('rfc_example.json'));

        $this->assertFalse($resource->isFromEmbed());
        $properties = $resource->getProperties();

        $this->assertArrayNotHasKey('_links', $properties);
        $this->assertArrayNotHasKey('_embedded', $properties);
    }

    protected function getPrivateMethod($className, $name)
    {
        $class = new ReflectionClass($className);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    protected function getFixture($filename)
    {
        return json_decode(file_get_contents(__DIR__ . '/fixtures/' . $filename), true);
    }
}