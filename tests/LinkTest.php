<?php
require_once(__DIR__ . '/TestBase.php');

class LinkTest extends TestBase
{
    public function testGettersSetters()
    {
        $link = new \HalClient\Link();
        $link->setTitle('title');
        $link->setDocumentationUrl('http://example.com/docs/rel');
        $link->setRel('rel');
        $link->setPrefix('prefix');
        $link->setDeprication('http://example.com/news/rel-function-depricated');
        $link->setHref('/rel/{id}');
        $link->setLang('en');
        $link->setName('name');
        $link->setProfile('profile');
        $link->setType('type');

        $this->assertEquals('title', $link->getTitle());
        $this->assertEquals('http://example.com/docs/rel', $link->getDocumentationUrl());
        $this->assertEquals('rel', $link->getRel());
        $this->assertEquals('prefix', $link->getPrefix());
        $this->assertTrue($link->isDepricated());
        $this->assertEquals('http://example.com/news/rel-function-depricated', $link->getDeprication());
        $this->assertEquals('/rel/{id}', $link->getHref());
        $this->assertEquals('en', $link->getLang());
        $this->assertEquals('name', $link->getName());
        $this->assertEquals('profile', $link->getProfile());
        $this->assertEquals('type', $link->getType());
    }

    public function testParse()
    {
        $link = \HalClient\Link::parse('prefix:rel', $this->getFixture('link.json'));
        $this->assertEquals('title', $link->getTitle());
        $this->assertEquals('rel', $link->getRel());
        $this->assertEquals('prefix', $link->getPrefix());
        $this->assertTrue($link->isDepricated());
        $this->assertEquals('http://example.com/news/rel-function-depricated', $link->getDeprication());
        $this->assertEquals('/rel/{id}', $link->getHref());
        $this->assertEquals('en', $link->getLang());
        $this->assertEquals('name', $link->getName());
        $this->assertEquals('profile', $link->getProfile());
        $this->assertEquals('type', $link->getType());
    }
} 