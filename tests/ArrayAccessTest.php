<?php
use Lucid\Component\Container\Container;
use Lucid\Component\Container\PrefixDecorator;

class ArrayAccessTest extends \PHPUnit_Framework_TestCase
{
    public $container = null;
    public $decoratedContainer = null;
    public function setup()
    {
        $this->container = new Container();
        $this->decoratedContainer = new PrefixDecorator('test:', $this->container);
    }

    public function testBasic()
    {
        $this->container['value1'] = 'hi';
        $this->assertEquals($this->container['value1'], 'hi');
        $this->decoratedContainer->set('value1', 'hello');
        $this->assertEquals($this->container['value1'], 'hi');
        $this->assertEquals($this->container['test:value1'], 'hello');

        $this->decoratedContainer['valueInt'] = 256;
        $this->decoratedContainer['valueFloat'] = 256.256;
        $this->assertEquals($this->decoratedContainer['valueInt'], 256);
        $this->assertEquals($this->decoratedContainer['valueFloat'], 256.256);


        $val = $this->decoratedContainer['value1'];
        $this->assertEquals($val, 'hello');
    }

    public function testMoveUpInHierarchy()
    {
        $this->container['rootindex'] = 'rootvalue';
        $this->decoratedContainer['decindex'] = 'decvalue';
        $this->assertEquals($this->container['rootindex'], 'rootvalue');
        $this->assertEquals($this->decoratedContainer['decindex'], 'decvalue');
        $this->assertEquals($this->decoratedContainer['../rootindex'], 'rootvalue');
    }

    public function testDoubleDecorator()
    {
        $container =  new Container();
        $dec1 = new PrefixDecorator('dec1:', $container);
        $dec2 = new PrefixDecorator('dec2:', $dec1);
        $container['rootindex'] = 'rootvalue';
        $dec1['dec1index'] = 'dec1value';
        $dec2['dec2index'] = 'dec2value';


        $this->assertEquals('rootvalue', $container['rootindex']);
        $this->assertEquals('dec1value', $container['dec1:dec1index']);
        $this->assertEquals('dec2value', $container['dec1:dec2:dec2index']);

        $this->assertEquals('rootvalue', $dec1['../rootindex']);
        $this->assertEquals('dec1value', $dec1['dec1index']);
        $this->assertEquals('dec2value', $dec1['dec2:dec2index']);

        $this->assertEquals('rootvalue', $dec2['../../rootindex']);
        $this->assertEquals('dec1value', $dec2['../dec1index']);
        $this->assertEquals('dec2value', $dec2['dec2index']);
    }

    public function testArrays()
    {
        $container =  new Container();
        $decorator = new PrefixDecorator('dec:', $container);

        $container['dec:myarray'] = array();
        $container['dec:myarray']['testIndex1'] = 'testValue1';
        $decorator['myarray']['testIndex2'] = 'testValue2';

        $this->assertEquals('testValue1', $container['dec:myarray']['testIndex1']);
        $this->assertEquals('testValue2', $container['dec:myarray']['testIndex2']);

        $this->assertEquals('testValue1', $decorator['myarray']['testIndex1']);
        $this->assertEquals('testValue2', $decorator['myarray']['testIndex2']);

        $container['dec:myarray']['testIndex3'] = array();
        $container['dec:myarray']['testIndex3']['testIndex4'] = 'testValue4';

        $this->assertEquals('testValue4', $container['dec:myarray']['testIndex3']['testIndex4']);
        $this->assertEquals('testValue4', $decorator['myarray']['testIndex3']['testIndex4']);
    }
}