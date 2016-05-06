<?php
use Lucid\Container\Container;

class BasicTest extends \PHPUnit_Framework_TestCase
{
    public function testSource()
    {
        $container = new Container();
        $this->assertTrue(is_array($container->getValues()));
    }

    public function testIssetUnset()
    {
        $container = new Container();
        $this->assertFalse($container->has('testKey'));
        $container->set('testKey', 'testValue');
        $this->assertTrue($container->has('testKey'));
        $container->delete('testKey');
        $this->assertFalse($container->has('testKey'));
    }

    public function testString()
    {
        $container = new Container();
        $container->set('testKey', 'testValue');
        $this->assertTrue($container->has('testKey'));
        $this->assertTrue($container->string('testKey') == 'testValue');
        $container->delete('testKey');
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
        $container->delete('testKey');
        $this->assertFalse($container->has('testKey'));

        $container->set('testKey', 1);
        $this->assertTrue($container->has('testKey'));
        $this->assertFalse(is_integer($container->string('testKey')));
        $this->assertTrue(is_string($container->string('testKey')));
        $this->assertFalse($container->string('testKey') === 1);
        $container->delete('testKey');
        $this->assertFalse($container->has('testKey'));
    }

    public function testInteger()
    {
        $container = new Container();
        $container->set('testKey', 1);
        $this->assertTrue($container->has('testKey'));
        $this->assertTrue($container->int('testKey') == 1);
        $container->delete('testKey');
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
        $container->delete('testKey');
        $this->assertFalse($container->has('testKey'));

        $container->set('testKey', 1);
        $this->assertTrue($container->has('testKey'));
        $this->assertTrue(is_integer($container->int('testKey')));
        $this->assertTrue($container->int('testKey') === 1);
        $container->delete('testKey');
        $this->assertFalse($container->has('testKey'));
    }

    public function testArray()
    {
        $container = new Container();
        $container->set('testKey', '1,2,3');
        $this->assertTrue($container->has('testKey'));
        $this->assertTrue(count($container->array('testKey')) == 3);
        $this->assertTrue($container->array('testKey')[1] == 2);
        $container->delete('testKey');
        $this->assertFalse($container->has('testKey'));

        $container->set('testKey', '1|2|3');
        $this->assertTrue($container->array('testKey', [], '|')[1] == 2);
    }
}