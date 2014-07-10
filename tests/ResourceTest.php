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

        $fixture = $this->getFixture('invalid_embeds.json');
        try{
            $parseEmbed->invokeArgs($resource, array($fixture['_embedded']['orders'][0]));
            $this->fail('Exception about missing href not thrown');
        }catch(\Exception $e){
            $this->assertInstanceOf('\HalClient\RfcException', $e);
            $this->assertEquals('Embedded resource has no _links/self/href attribute', $e->getMessage());
        }

        try{
            $parseEmbed->invokeArgs($resource, array($fixture['_embedded']['orders'][1]));
            $this->fail('Exception about invalid embed not thrown');
        }catch(\Exception $e){
            $this->assertInstanceOf('\HalClient\RfcException', $e);
            $this->assertEquals('_links/self/href cannot be a template', $e->getMessage());
        }
    }

    public function testGetProperties()
    {
        $resource = \HalClient\Resource::fromJsonResponse($this->getFixture('rfc_example.json'));

        $this->assertFalse($resource->isFromEmbed());
        $properties = $resource->getProperties();

        $this->assertArrayNotHasKey('_links', $properties);
        $this->assertArrayNotHasKey('_embedded', $properties);

        $this->assertArrayHasKey('currentlyProcessing', $properties);
        $this->assertEquals(14, $properties['currentlyProcessing']);
        $this->assertArrayHasKey('shippedToday', $properties);
        $this->assertEquals(20, $properties['shippedToday']);
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