<?php
use Lucid\Container\Container;
use Lucid\Container\PrefixDecorator;

class IteratorTest extends \PHPUnit_Framework_TestCase
{



    public function testArrays()
    {
        $container =  new Container();
        $decorator = new PrefixDecorator('dec:', $container);

        $container['value1'] = 1;
        $container['value2'] = 2;

        $testOutput = '';
        foreach ($container as $key=>$value) {
            $testOutput .= $key.':'.$value.';';
        }
        $this->assertEquals('value1:1;value2:2;', $testOutput);

        $testOutput = '';
        $decorator['value3'] = 3;
        $decorator['value4'] = 4;
        foreach ($decorator as $key=>$value) {
            $testOutput .= $key.':'.$value.';';
        }
        $this->assertEquals('value3:3;value4:4;', $testOutput);

        $testOutput = '';
        foreach ($container as $key=>$value) {
            $testOutput .= $key.':'.$value.';';
        }
        $this->assertEquals('value1:1;value2:2;dec:value3:3;dec:value4:4;', $testOutput);
    }
}