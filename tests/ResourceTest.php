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
    }

    protected function getPrivateMethod($className, $name) {
        $class = new ReflectionClass($className);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    protected function getFixture($filename){
        return json_decode(file_get_contents(__DIR__ . '/fixtures/' . $filename), true);
    }
}