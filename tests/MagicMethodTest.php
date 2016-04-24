<?php
use Lucid\Component\Container\Container;
use Lucid\Component\Container\PrefixDecorator;

class MagicMethodTest extends \PHPUnit_Framework_TestCase
{
    public $container = null;
    public $decoratedContainer = null;
    public function setup()
    {
        $this->container = new Container();
        $this->decoratedContainer = new PrefixDecorator('test:', $this->container);
    }

    public function testArraySetGet()
    {
        $this->container->set('testArray', []);
        $this->container->testArray()['index1'] = 'value1';
        $this->assertEquals($this->container->testArray()['index1'], 'value1');
        $this->container->testArray()['index2'] = ['index3'=>'value2'];
        $this->assertEquals($this->container->testArray()['index2']['index3'], 'value2');

    }

    public function testDecoratedArraySetGet()
    {
        $this->container = new Container();
        $this->decoratedContainer = new PrefixDecorator('test:', $this->container);

        $array = [];
        $this->decoratedContainer->set('decIndex1', $array);
        $this->decoratedContainer->decIndex1()['index2'] = 'decValue1';
        $this->assertEquals($this->decoratedContainer->decIndex1()['index2'], 'decValue1');
    }
}