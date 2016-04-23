<?php
use Lucid\Component\Container\Container;
use Lucid\Component\Container\PrefixDecorator;

class PrefixDecoratorTest extends \PHPUnit_Framework_TestCase
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
        $this->container->set('value1', 'hi');
        $this->assertEquals($this->container->get('value1'), 'hi');
        $this->decoratedContainer->set('value1', 'hello');
        $this->assertEquals($this->container->get('value1'), 'hi');
        $this->assertEquals($this->container->get('test:value1'), 'hello');

        $val = $this->decoratedContainer->get('value1');
        $this->assertEquals($val, 'hello');
    }

    public function testMoveUpInHierarchy()
    {
        $this->container->set('rootindex', 'rootvalue');
        $this->decoratedContainer->set('decindex', 'decvalue');
        $this->assertEquals($this->container->get('rootindex'), 'rootvalue');
        $this->assertEquals($this->decoratedContainer->get('decindex'), 'decvalue');
        $this->assertEquals($this->decoratedContainer->get('../rootindex'), 'rootvalue');
    }

    public function testDoubleDecorator()
    {
        $container =  new Container();
        $dec1 = new PrefixDecorator('dec1:', $container);
        $dec2 = new PrefixDecorator('dec2:', $dec1);
        $container->set('rootindex', 'rootvalue');
        $dec1->set('dec1index', 'dec1value');
        $dec2->set('dec2index', 'dec2value');


        $this->assertEquals('rootvalue', $container->get('rootindex'));
        $this->assertEquals('dec1value', $container->get('dec1:dec1index'));
        $this->assertEquals('dec2value', $container->get('dec1:dec2:dec2index'));

        $this->assertEquals('rootvalue', $dec1->get('../rootindex'));
        $this->assertEquals('dec1value', $dec1->get('dec1index'));
        $this->assertEquals('dec2value', $dec1->get('dec2:dec2index'));

        $this->assertEquals('rootvalue', $dec2->get('../../rootindex'));
        $this->assertEquals('dec1value', $dec2->get('../dec1index'));
        $this->assertEquals('dec2value', $dec2->get('dec2index'));
    }
}