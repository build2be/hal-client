<?php

class TestBase extends PHPUnit_Framework_TestCase
{
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