<?php
use Lucid\Component\Container\Container;

class ConstructorTest_a
{
    function __construct()
    {
        $this->testProperty = 'a';
    }
}

class ConstructorTest_b
{
    function __construct()
    {
        $this->testProperty = 'b';
    }
}

class ConstructorTest_c
{
    function __construct(string $testProperty)
    {
        $this->testProperty = $testProperty;
    }
}

class ConstructorTest_d
{
    function __construct(string $testProperty)
    {
        $this->testProperty = $testProperty;
    }
}

class ConstructorTest_e
{
    function __construct(ConstructorTest_d $testSubObject)
    {
        $this->testSubObject = $testSubObject;
    }
}

interface ConstructorTest_f_Interface
{
    public function testF_function();
}

class ConstructorTest_f implements ConstructorTest_f_Interface
{
    function __construct()
    {
    }

    public function testF_function()
    {
        return 'f';
    }
}

class ConstructorTest_g
{
    function __construct(ConstructorTest_f_Interface $testSubObject)
    {
        $this->testSubObject = $testSubObject;
    }
}

class View__ConstructorTest_h
{
    function __construct(ConstructorTest_f_Interface $testSubObject)
    {
        $this->testSubObject = $testSubObject;
    }
}

class ConstructorTest_i
{
    public $propertyA = null;
    public $propertyB = null;
    public function __construct()
    {
    }
    
    public function setPropertyA($newValue) 
    {
        $this->propertyA = $newValue;
    }

    public function setPropertyB($newValue) 
    {
        $this->propertyB = $newValue;
    }
}


class ConstructorTest extends \PHPUnit_Framework_TestCase
{
    public $container = null;

    public function setup()
    {
        $this->container = new Container();
        $this->container->registerConstructor('objectA', 'ConstructorTest_a', true);
        $this->container->registerConstructor('objectB', 'ConstructorTest_b', false);

        $this->container->registerConstructor('objectC', 'ConstructorTest_c');
        $this->container->addParameter('objectC', 'fixed', 'testProperty', 'c');

        $this->container->registerConstructor('objectD', 'ConstructorTest_d');
        $this->container->set('testPropertyForD', 'd');
        $this->container->addParameter('objectD', 'container', 'testProperty', 'testPropertyForD');

        $this->container->registerConstructor('objectE', 'ConstructorTest_e');
        $this->container->registerConstructor('objectF', 'ConstructorTest_f');

        $this->container->registerConstructor('objectG', 'ConstructorTest_g');

        $this->container->registerConstructor('view/', 'View__');
        
        $this->container->registerConstructor('objectI', 'ConstructorTest_i');
        $this->container->addInstantiationClosure('objectI', function($object, $container) {
            $object->setPropertyA(1);
            $object->setPropertyB(2);
        });
    }

    public function testTestConstructor()
    {
        $objA = $this->container->construct('objectA');
        $objB = $this->container->construct('objectB');
        $this->assertEquals('a', $objA->testProperty);
        $this->assertEquals('b', $objB->testProperty);
    }

    public function testTestConstructorFixedParameters()
    {
        $objC = $this->container->construct('objectC');
        $this->assertEquals('c', $objC->testProperty);
    }
    
    
    public function testTestConstructorContainerParameters()
    {
        $objC = $this->container->construct('objectD');
        $this->assertEquals('d', $objC->testProperty);
    }
    
    

    public function testTestConstructorFindMatchingObject()
    {
        $this->container->set('testObjectDforConstructE', $this->container->construct('objectD'));
        $objE = $this->container->construct('objectE');

        $this->assertEquals('d', $objE->testSubObject->testProperty);
    }
    

    public function testTestConstructorFindMatchingInterface()
    {
        $this->container->set('testObjectFforConstructG', $this->container->construct('objectF'));
        $objG = $this->container->construct('objectG');
        $this->assertEquals('f', $objG->testSubObject->testF_function());
    }
    
    
    public function testPrefixConstructors()
    {
        $objH = $this->container->construct('view/ConstructorTest_h');
        $this->assertEquals('f', $objH->testSubObject->testF_function());
    }
    
    public function testSingletonParameter()
    {
        # A is registered as a singleton, B is NOT
        $objA1 = $this->container->construct('objectA');
        $objA2 = $this->container->construct('objectA');
        $objA1->testProperty = 'c';
        
        # these should be the same object since it's registered as a singleton, so setting testProperty on one should set it on the other
        $this->assertEquals($objA1->testProperty, $objA2->testProperty); 
        
        
        $objB1 = $this->container->construct('objectB');
        $objB2 = $this->container->construct('objectB');
        $objB1->testProperty = 'c';
        
        # these are NOT the same object since it's NOT registered as a singleton, so setting testProperty 
        # on one should NOT set it on the other
        $this->assertNotEquals($objB1->testProperty, $objB2->testProperty); 
    }
    
    public function testUsingGetForConstructor()
    {
        $objA = $this->container->get('objectA');
        $this->assertEquals('a', $objA->testProperty);
        
        $objH = $this->container->get('view/ConstructorTest_h');
        $this->assertEquals('f', $objH->testSubObject->testF_function());
    }
    
    public function testInstantiationClosures()
    {
        $objI = $this->container->get('objectI');
        $this->assertEquals(1, $objI->propertyA);
        $this->assertEquals(2, $objI->propertyB);
    }
}
