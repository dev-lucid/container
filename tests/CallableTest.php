<?php
use Lucid\Container\InjectorFactoryContainer;
use Lucid\Container\Constructor\Constructor;

class CallableTest_a
{
    public function testMethod1()
    {
        return 'a';
    }
}

class CallableTest_b
{
    public function testMethod1()
    {
        return 'b';
    }

    public function testMethod2(CallableTest_a $param1)
    {
        return $param1->testMethod1();
    }
}


class CallableTest extends \PHPUnit_Framework_TestCase
{
    public $container = null;

    public function setup()
    {
        $this->container = new InjectorFactoryContainer();
        $this->container->set('callable1', function(string $testParam1) {
            return $testParam1;
        });
        $this->container->addConstructor(new Constructor('objectA', CallableTest_a::class, false, function(){
            return new CallableTest_a();
        }));


        $this->container->addConstructor(new Constructor('objectB', CallableTest_b::class, false, function(){
            return new CallableTest_b();
        }));

        # this is necessary to use testConstructorClosureExecute
        $this->container->addConstructor(new Constructor('objectAnotClosure', 'CallableTest_a', true));

    }

    public function testCallable1()
    {
        $this->assertEquals('callable 1 was called', $this->container->callable1('callable 1 was called'));
    }

    public function testConstructorClosure()
    {
        $this->assertEquals('a', $this->container->get('objectA')->testMethod1());
        $this->assertEquals('b', $this->container->get('objectB')->testMethod1());
    }

    public function testConstructorClosureExecute()
    {
        $this->assertEquals('a', $this->container->call('objectB', 'testMethod2'));
    }
}











