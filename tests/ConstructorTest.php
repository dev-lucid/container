<?php
use Lucid\Component\Container\Container;

class ConstructorTest_a
{
    function __construct()
    {
        $this->testProperty = 'a';
    }
}

class ConstructorTest_b
{
    function __construct()
    {
        $this->testProperty = 'b';
    }
}

class ConstructorTest_c
{
    function __construct(string $testProperty)
    {
        $this->testProperty = $testProperty;
    }
}

class ConstructorTest_d
{
    function __construct(string $testProperty)
    {
        $this->testProperty = $testProperty;
    }
}

class ConstructorTest_e
{
    function __construct(ConstructorTest_d $testSubObject)
    {
        $this->testSubObject = $testSubObject;
    }
}

interface ConstructorTest_f_Interface
{
    public function testF_function();
}

class ConstructorTest_f implements ConstructorTest_f_Interface
{
    function __construct()
    {
    }

    public function testF_function()
    {
        return 'f';
    }
}

class ConstructorTest_g
{
    function __construct(ConstructorTest_f_Interface $testSubObject)
    {
        $this->testSubObject = $testSubObject;
    }
}

class View__ConstructorTest_h
{
    function __construct(ConstructorTest_f_Interface $testSubObject)
    {
        $this->testSubObject = $testSubObject;
    }
}


class ConstructorTest extends \PHPUnit_Framework_TestCase
{
    public $container = null;

    public function setup()
    {
        $this->container = new Container();
        $this->container->addConstructor('objectA', 'ConstructorTest_a');
        $this->container->addConstructor('objectB', 'ConstructorTest_b');

        $this->container->addConstructor('objectC', 'ConstructorTest_c');
        $this->container->addFixedParameter('testProperty', 'c');

        $this->container->addConstructor('objectD', 'ConstructorTest_d');
        $this->container->set('testPropertyForD', 'd');
        $this->container->addContainerParameter('testProperty', 'testPropertyForD');

        $this->container->addConstructor('objectE', 'ConstructorTest_e');
        $this->container->addConstructor('objectF', 'ConstructorTest_f');

        $this->container->addConstructor('objectG', 'ConstructorTest_g');

        $this->container->addPrefixedConstructor('view/', 'View__');
    }

    public function testTestConstructor()
    {
        $objA = $this->container->construct('objectA');
        $objB = $this->container->construct('objectB');
        $this->assertEquals('a', $objA->testProperty);
        $this->assertEquals('b', $objB->testProperty);
    }

    public function testTestConstructorFixedParameters()
    {
        $objC = $this->container->construct('objectC');
        $this->assertEquals('c', $objC->testProperty);
    }

    public function testTestConstructorContainerParameters()
    {
        $objC = $this->container->construct('objectD');
        $this->assertEquals('d', $objC->testProperty);
    }

    public function testTestConstructorFindMatchingObject()
    {
        $this->container->set('testObjectDforConstructE', $this->container->construct('objectD'));
        $objE = $this->container->construct('objectE');

        $this->assertEquals('d', $objE->testSubObject->testProperty);
    }

    public function testTestConstructorFindMatchingInterface()
    {
        $this->container->set('testObjectFforConstructG', $this->container->construct('objectF'));
        $objG = $this->container->construct('objectG');
        $this->assertEquals('f', $objG->testSubObject->testF_function());
    }

    public function testPrefixConstructors()
    {
        $objH = $this->container->construct('view/ConstructorTest_h');
        $this->assertEquals('f', $objH->testSubObject->testF_function());
    }
}