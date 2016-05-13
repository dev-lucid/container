<?php
use Lucid\Container\InjectorFactoryContainer;
use Lucid\Container\Constructor\Constructor;

class ConstructArbitraryClass_Class_a
{
    public function testMethod1()
    {
        return 'a';
    }
}

class ConstructArbitraryClass_Class_b
{
    public function __construct(ConstructArbitrary_Class_c $subobject)
    {
        $this->subobject = $subobject;
    }

    public function testMethod3()
    {
        return 'b';
    }
}


class ConstructArbitraryClass_Class_c
{
    public function testMethod3()
    {
        return 'c';
    }
}

class ConstructArbitraryClassTest extends \PHPUnit_Framework_TestCase
{
    public $container = null;

    public function setup()
    {
        $this->container = new InjectorFactoryContainer();
    }

    public function testArbitrary1()
    {
        $objectA = $this->container->construct(ConstructArbitraryClass_Class_a::class);
        $this->assertEquals(ConstructArbitraryClass_Class_a::class, get_class($objectA));
    }

}
