<?php
use Lucid\Component\Container\Container;
use Lucid\Component\Container\PrefixDecorator;

class CountableTest extends \PHPUnit_Framework_TestCase
{
    public $container = null;
    public $decoratedContainer = null;

    public function setup()
    {
        $this->container = new Container();
        $this->decoratedContainer = new PrefixDecorator('dec:', $this->container);
    }

    public function testCount()
    {
        $this->container->set('testval1', 1);
        $this->container->set('testval2', 2);
        $this->decoratedContainer->set('testval3', 3);
        $this->decoratedContainer->set('testval4', 4);

        $this->assertEquals(4, count($this->container));
        $this->assertEquals(2, count($this->decoratedContainer));
    }
}