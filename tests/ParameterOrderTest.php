<?php
use Lucid\Component\Container\Container;



class ParameterOrderTest_classA
{
    public function __construct(Container $request, Container $session, Container $cookie)
    {
        $this->request = $request;
        $this->session = $session;
        $this->cookie  = $cookie;
    }
}


class ParameterOrderTest extends \PHPUnit_Framework_TestCase
{
    public $container = null;

    public function setup()
    {
        $this->container = new Container();
        $this->container->set('request', new Container());
        $this->container->set('session', new Container());
        $this->container->set('cookie', new Container());
        
        $this->container->get('request')->set('name', 'request');
        $this->container->get('session')->set('name', 'session');
        $this->container->get('cookie')->set('name', 'cookie');
        
        $this->container->registerConstructor('objectA', 'ParameterOrderTest_classA');
    }
    
    public function testContainerNameSanityCheck()
    {
        $this->assertEquals('request', $this->container->get('request')->string('name'));
        $this->assertEquals('session', $this->container->get('session')->string('name'));
        $this->assertEquals('cookie', $this->container->get('cookie')->string('name'));
    }

    public function testOrder()
    {
        $objectA = $this->container->get('objectA');
        $this->assertEquals('request', $objectA->request->string('name'));
        $this->assertEquals('session', $objectA->session->string('name'));
        $this->assertEquals('cookie',  $objectA->cookie->string('name'));
    }
}
