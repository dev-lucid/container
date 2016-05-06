<?php
use Lucid\Container\Container;

Interface TestInterface
{
    public function TestInterfaceFunction1();
}

class TestClass1 implements TestInterface
{
    public function TestInterfaceFunction1()
    {
        echo('good');
    }
}

class TestClass2
{
}

class InterfaceTest extends \PHPUnit_Framework_TestCase
{
    public $container = null;
    public function setup()
    {
        $this->container = new Container();
        $this->container->requireInterfacesForIndex('requiresTestInterface', 'TestInterface');
    }

    public function testRequireInterface()
    {
        $this->container->set('requiresTestInterface', new TestClass1());
        $this->container->set('doesNotRequireTestInterface', new TestClass2());
        $this->setExpectedException(\Lucid\Container\Exception\RequiredInterfaceException::class);
        $this->container->set('requiresTestInterface', new TestClass2());
    }

    public function testRequireInterfaceExistingIndex()
    {
        $this->container->set('willRequireTestInterface1', new TestClass1());
        $this->container->set('willRequireTestInterface2', new TestClass2());

        $this->container->requireInterfacesForIndex('willRequireTestInterface1', 'TestInterface');
        $this->setExpectedException(\Lucid\Container\Exception\RequiredInterfaceException::class);
        $this->container->requireInterfacesForIndex('willRequireTestInterface2', 'TestInterface');
    }
}