<?php
require_once(__DIR__ . '/TestBase.php');

class ResourceTest extends TestBase
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
        try {
            $parseEmbed->invokeArgs($resource, array($fixture['_embedded']['orders'][0]));
            $this->fail('Exception about missing href not thrown');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\HalClient\RfcException', $e);
            $this->assertEquals('Embedded resource has no _links/self/href attribute', $e->getMessage());
        }

        try {
            $parseEmbed->invokeArgs($resource, array($fixture['_embedded']['orders'][1]));
            $this->fail('Exception about invalid embed not thrown');
        } catch (\Exception $e) {
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

    public function testHasLink()
    {
        $resource = \HalClient\Resource::fromJsonResponse($this->getFixture('links.json'));
        $this->assertTrue($resource->hasLink('self'));
        $this->assertTrue($resource->hasLink('next'));
        $this->assertTrue($resource->hasLink('find'));
        $this->assertTrue($resource->hasLink('widgets'));
        $this->assertTrue($resource->hasLink('find'));

        $this->assertTrue($resource->hasLink('curies'));
        $this->assertTrue($resource->hasLink('curies/ea'));
        $this->assertTrue($resource->hasLink('curies/acme'));
        $this->assertTrue($resource->hasLink('curies/0'));
        $this->assertTrue($resource->hasLink('curies/1'));

        $this->assertTrue($resource->hasLink('admin'));
        $this->assertTrue($resource->hasLink('admin/0'));
        $this->assertTrue($resource->hasLink('admin/1'));

        $this->assertFalse($resource->hasLink('admin/2'));
        $this->assertFalse($resource->hasLink('test'));
        $this->assertFalse($resource->hasLink('self/1'));

    }

    public function testGetUrl()
    {
        $resource = \HalClient\Resource::fromJsonResponse($this->getFixture('links.json'));
        $this->assertEquals('/orders', $resource->getUrl('self'));
        $this->assertEquals('/orders?page=2', $resource->getUrl('next'));
        $this->assertEquals('/orders?id=2', $resource->getUrl('find', array('id' => 2)));
        $this->assertEquals('http://docs.acme.com/relations/test', $resource->getUrl('curies/acme', array('rel' => 'test')));
        $this->assertEquals('http://example.com/docs/rels/test', $resource->getUrl('curies/ea', array('rel' => 'test')));

        try {
            $resource->getUrl('nonexistent');
            $this->fail('Exception for non-existent link not thrown');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('Link "nonexistent" does not exist.', $e->getMessage());
        }
    }

    public function testGetLink()
    {
        $resource = \HalClient\Resource::fromJsonResponse($this->getFixture('links.json'));
        /**
         * @var $link \HalClient\Link
         */
        $link = $resource->getLink('find');

        $this->assertEquals('find', $link->getRel());
        $this->assertEquals('ea', $link->getPrefix());
        $this->assertEquals('/orders{?id}', $link->getHref());
        $this->assertEquals('http://example.com/docs/rels/find', $link->getDocumentationUrl());
        $this->assertFalse($link->isDepricated());
        $this->assertTrue($link->isTemplate());

        $link = $resource->getLink('admin/0');

        $this->assertEquals('admin', $link->getRel());
        $this->assertEquals('ea', $link->getPrefix());
        $this->assertEquals('/admins/2', $link->getHref());
        $this->assertEquals('http://example.com/docs/rels/admin', $link->getDocumentationUrl());
        $this->assertEquals('Fred', $link->getTitle());
        $this->assertFalse($link->isDepricated());
        $this->assertFalse($link->isTemplate());
    }

    public function testParseUrlTemplate()
    {
        $resource = new \HalClient\Resource();

        $template = 'http://example.com/order/';
        $result = $resource->parseUrlTemplate($template, array());
        $this->assertEquals('http://example.com/order/', $result);

        $template = 'http://example.com/order/{order}/test/{test}';
        $result = $resource->parseUrlTemplate($template, array(
          'order' => 5,
          'test' => 42
        ));
        $this->assertEquals('http://example.com/order/5/test/42', $result);

        $template = 'http://example.com/order/{?id}';
        $result = $resource->parseUrlTemplate($template, array(
          'id' => 5,
        ));
        $this->assertEquals('http://example.com/order/?id=5', $result);

        $template = 'http://example.com/order/{?id,test,test2}';
        $result = $resource->parseUrlTemplate($template, array(
          'id' => 5,
          'test' => 'first',
          'test2' => 'second'
        ));
        $this->assertEquals('http://example.com/order/?id=5&test=first&test2=second', $result);
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