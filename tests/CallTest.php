<?php
use Lucid\Container\InjectorFactoryContainer;
use Lucid\Container\Constructor\Constructor;

class CallTest_a
{
    function __construct()
    {
        $this->testProperty = 'a';
    }

    function testMethod1()
    {
        return $this->testProperty;
    }

    function testMethod2(string $testMethod2param)
    {
        return $testMethod2param;
    }

    function testMethod3(CallTest_b $testMethod3param)
    {
        return get_class($testMethod3param);
    }

    function testMethod4(CallTest_c_Interface $testMethod4param)
    {
        return get_class($testMethod4param);
    }
}

class CallTest_b
{
}

interface CallTest_c_Interface
{
}

class CallTest_c implements CallTest_c_Interface
{
}

class CallTest extends \PHPUnit_Framework_TestCase
{
    public $container = null;

    public function setup()
    {
        $this->container = new InjectorFactoryContainer();
        $const1 = new Constructor('objectA', 'CallTest_a', true);
        $const2 = new Constructor('objectB', 'CallTest_b', true);
        $const3 = new Constructor('objectC', 'CallTest_c', true);
        $this->container->addConstructor($const1);
        $this->container->addConstructor($const2);
        $this->container->addConstructor($const3);
    }

    public function testExecute()
    {
        $this->assertEquals('a', $this->container->call('objectA', 'testMethod1'));
    }


    public function testExecuteFindParameterByName()
    {
        $this->container->set('testMethod2param', 'test2');
        $this->assertEquals('test2', $this->container->call('objectA', 'testMethod2'));
    }

    public function testExecuteFindParameterByClass()
    {
        $this->assertEquals('CallTest_b', $this->container->call('objectA', 'testMethod3'));
    }


    public function testExecuteFindParameterByInterface()
    {
        $this->assertEquals('CallTest_c', $this->container->call('objectA', 'testMethod4'));
    }


    public function testExecuteAfterInstantiation()
    {
        $objectA = $this->container->construct('objectA');
        $this->assertEquals('CallTest_c', $this->container->call($objectA, 'testMethod4'));
    }
}
