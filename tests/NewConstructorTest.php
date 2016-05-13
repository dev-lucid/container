<?php
use Lucid\Container\InjectorFactoryContainer;
use Lucid\Container\Constructor\Constructor;
use Lucid\Container\Constructor\Parameter\Fixed;
use Lucid\Container\Constructor\Parameter\Container;
use Lucid\Container\Constructor\Parameter\Closure;

class NewConstructorTest_class1
{
    public function testMethod1()
    {
        return 'NewConstructorTest_class1->testMethod1()';
    }
}

class NewConstructorTest_class2
{
    protected $parameter1;
    public function __construct(string $parameter1)
    {
        $this->parameter1 = $parameter1;
    }
    public function testMethod2()
    {
        return 'parameter1='.$this->parameter1;
    }
}

class NewConstructorTest_prefix_class1
{
    public function testMethod1()
    {
        return 'value1';
    }
}

class NewConstructorTest extends \PHPUnit_Framework_TestCase
{
    public $container = null;

    public function setup()
    {
        $this->container = new InjectorFactoryContainer();

        $constructor = new Constructor('class1', 'NewConstructorTest_class1');
        $this->container->addConstructor($constructor);

        $constructor = new Constructor('class2', 'NewConstructorTest_class2');
        $constructor->addParameter(new Fixed('parameter1', 'value1'));
        $this->container->addConstructor($constructor);

        $constructor = new Constructor('class2b', 'NewConstructorTest_class2');
        $constructor->addParameter(new Container('parameter1', 'NewConstructorTest_class2b-parameter1'));
        $this->container->addConstructor($constructor);
        $this->container->set('NewConstructorTest_class2b-parameter1', 'value2');

        $constructor = new Constructor('class2c', 'NewConstructorTest_class2');
        $constructor->addParameter(new Closure('parameter1', function(){
            return 'value3';
        }));
        $this->container->addConstructor($constructor);

        $constructor = new Constructor('NewConstructorTest_prefix_');
        $this->container->addConstructor($constructor);
    }


    public function testBasicConstruct()
    {
        $class1 = $this->container->construct('class1');
        $this->assertEquals('NewConstructorTest_class1->testMethod1()', $class1->testMethod1());
    }

    public function testFixedParameter()
    {
        $class2 = $this->container->construct('class2');
        $this->assertEquals('parameter1=value1', $class2->testMethod2());
    }

    public function testContainerParameter()
    {
        $class2b = $this->container->construct('class2b');
        $this->assertEquals('parameter1=value2', $class2b->testMethod2());
    }

    public function testClosureParameter()
    {
        $class2c = $this->container->construct('class2c');
        $this->assertEquals('parameter1=value3', $class2c->testMethod2());
    }


    public function testPrefixMatch1()
    {
        $prefixObj1= $this->container->construct('NewConstructorTest_prefix_class1');
        $this->assertEquals('value1', $prefixObj1->testMethod1());
    }
}
