<?php
use Lucid\Container\InjectorFactoryContainer;
use Lucid\Container\Constructor\Constructor;


class Model__TestModel
{
    protected $name;
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}


class PrefixClosureTest extends \PHPUnit_Framework_TestCase
{
    public $container = null;

    public function setup()
    {
        $this->container = new InjectorFactoryContainer();
        $this->container->addConstructor(new Constructor('Model__', 'Model__', false, function($constructor) {
            $type = $constructor->getType();
            $object = new $type($constructor->getType());
            return $object;
        }));
    }

    public function testClosure1()
    {
        $object = $this->container->get('Model__TestModel');
        $this->assertEquals('Model__TestModel', $object->getName());
    }
}











