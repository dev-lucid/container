<?php
use Lucid\Container\InjectorFactoryContainer;

class ExecuteTest_a
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

    function testMethod3(ExecuteTest_b $testMethod3param)
    {
        return get_class($testMethod3param);
    }

    function testMethod4(ExecuteTest_c_Interface $testMethod4param)
    {
        return get_class($testMethod4param);
    }
}

class ExecuteTest_b
{
}

interface ExecuteTest_c_Interface
{
}

class ExecuteTest_c implements ExecuteTest_c_Interface
{
}

class ExecuteTest extends \PHPUnit_Framework_TestCase
{
    public $container = null;

    public function setup()
    {
        $this->container = new InjectorFactoryContainer();
        $this->container->registerConstructor('objectA', 'ExecuteTest_a', true);
        $this->container->registerConstructor('objectB', 'ExecuteTest_b', true);
        $this->container->registerConstructor('objectC', 'ExecuteTest_c', true);
    }

    public function testExecute()
    {
        $this->assertEquals('a', $this->container->execute('objectA', 'testMethod1'));
    }

    public function testExecuteFindParameterByName()
    {
        $this->container->set('testMethod2param', 'test2');
        $this->assertEquals('test2', $this->container->execute('objectA', 'testMethod2'));
    }

    public function testExecuteFindParameterByClass()
    {
        $this->assertEquals('ExecuteTest_b', $this->container->execute('objectA', 'testMethod3'));
    }

    public function testExecuteFindParameterByInterface()
    {
        $this->assertEquals('ExecuteTest_c', $this->container->execute('objectA', 'testMethod4'));
    }

    public function testExecuteAfterInstantiation()
    {
        $objectA = $this->container->construct('objectA');
        $this->assertEquals('ExecuteTest_c', $this->container->execute($objectA, 'testMethod4'));
    }
}
