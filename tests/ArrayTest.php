<?php
use Lucid\Component\Container\Container;

class ArrayTest extends \PHPUnit_Framework_TestCase
{
    public function testSource()
    {
        $container = new Container();
        $this->assertTrue(is_array($container->getArray()));
    }

    public function testIssetUnset()
    {
        $container = new Container();
        $this->assertFalse($container->has('testKey'));
        $container->set('testKey', 'testValue');
        $this->assertTrue($container->has('testKey'));
        $container->un_set('testKey');
        $this->assertFalse($container->has('testKey'));
    }

    public function testString()
    {
        $container = new Container();
        $container->set('testKey', 'testValue');
        $this->assertTrue($container->has('testKey'));
        $this->assertTrue($container->string('testKey') == 'testValue');
        $container->un_set('testKey');
        $this->assertFalse($container->has('testKey'));
    }

    public function testStringNoCasting()
    {
        $container = new Container();

        $container->set('testKey', '1');
        $this->assertTrue($container->has('testKey'));
        $this->assertFalse(is_integer($container->string('testKey')));
        $this->assertTrue(is_string($container->string('testKey')));
        $this->assertFalse($container->string('testKey') === 1);
        $container->un_set('testKey');
        $this->assertFalse($container->has('testKey'));

        $container->set('testKey', 1);
        $this->assertTrue($container->has('testKey'));
        $this->assertFalse(is_integer($container->string('testKey')));
        $this->assertTrue(is_string($container->string('testKey')));
        $this->assertFalse($container->string('testKey') === 1);
        $container->un_set('testKey');
        $this->assertFalse($container->has('testKey'));
    }

    public function testInteger()
    {
        $container = new Container();
        $container->set('testKey', 1);
        $this->assertTrue($container->has('testKey'));
        $this->assertTrue($container->int('testKey') == 1);
        $container->un_set('testKey');
        $this->assertFalse($container->has('testKey'));
    }

    public function testIntegerNoCasting()
    {
        $container = new Container();
        $container->set('testKey', '1');
        $this->assertTrue($container->has('testKey'));
        $this->assertTrue(is_integer($container->int('testKey')));
        $this->assertTrue($container->int('testKey') === 1);
        $this->assertFalse($container->int('testKey') === '1');
        $container->un_set('testKey');
        $this->assertFalse($container->has('testKey'));

        $container->set('testKey', 1);
        $this->assertTrue($container->has('testKey'));
        $this->assertTrue(is_integer($container->int('testKey')));
        $this->assertTrue($container->int('testKey') === 1);
        $container->un_set('testKey');
        $this->assertFalse($container->has('testKey'));
    }
}