<?php
use Lucid\Container\RequestContainer;

/*
The only difference between a RequestContainer and Container is how booleans are handled,
so this unit test only  Addresses boolean values.
*/
class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testBoolTrue()
    {
        $container = new RequestContainer();
        $array = [
            'BoolTrueReal'=>true,
            'BoolTrueOn'=>'on',
            'BoolTrue1'=>1,
            'BoolTrueString'=>'true',
            'BoolTrueYes'=>'yes',
            'InvalidValue'=>'Yep',
        ];
        $container->setSource($array);

        $this->assertTrue($container->bool('BoolTrueReal'));
        $this->assertTrue($container->bool('BoolTrueOn'));
        $this->assertTrue($container->bool('BoolTrue1'));
        $this->assertTrue($container->bool('BoolTrueString'));
        $this->assertTrue($container->bool('BoolTrueYes'));

        $this->assertFalse($container->bool('nullValue'));
        $this->setExpectedException(Lucid\Container\Exception\InvalidBooleanException::class);
        $this->assertFalse($container->bool('InvalidValue'));
    }

    public function testBoolFalse()
    {
        $container = new RequestContainer();
        $array = [
            'BoolFalseReal'=>false,
            'BoolFalseEmpty'=>'',
            'BoolFalse0'=>0,
            'BoolFalseString'=>'true',
            'BoolFalseString'=>'no',
            'BoolFalseNull'=>null,
        ];
        $container->setSource($array);

        $this->assertFalse($container->bool('BoolFalseReal'));
        $this->assertFalse($container->bool('BoolFalseEmpty'));
        $this->assertFalse($container->bool('BoolFalse0'));
        $this->assertFalse($container->bool('BoolFalseString'));
        $this->assertFalse($container->bool('BoolFalseString'));
        $this->assertFalse($container->bool('BoolFalseNull'));
    }
}