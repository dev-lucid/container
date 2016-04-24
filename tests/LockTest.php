<?php
use Lucid\Component\Container\Container;
use Lucid\Component\Container\PrefixDecorator;

class LockTest extends \PHPUnit_Framework_TestCase
{
    public $container = null;
    public function setup()
    {
        $this->container = new Container();
    }

    public function testLock()
    {
        $this->container->set('test1', 'value1');
        $this->assertEquals('value1', $this->container->get('test1'));
        $this->container->lock('test1');
        $this->assertEquals('value1', $this->container->get('test1'));
        $this->setExpectedException(\Lucid\Component\Container\LockedIndexException::class);
        $this->container->set('test1', 'value2');
    }

    public function testUnlock()
    {
        $this->container->set('unlocktest1', 'value1');
        $this->container->lock('unlocktest1');
        $this->container->unlock('unlocktest1');
        $this->container->set('unlocktest1', 'value2');
        $this->assertEquals('value2', $this->container->get('unlocktest1'));
    }

}