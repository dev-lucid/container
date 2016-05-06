<?php
use Lucid\Container\Container;

class SourceTest extends \PHPUnit_Framework_TestCase
{
    public $container = null;


    public function testInvalidSource()
    {
        $container = new Container();
        $source = [];
        $container->setSource($source);

        $this->setExpectedException(\Lucid\Container\Exception\InvalidSourceException::class);
        $invalidSource = 'hi';
        $container->setSource($invalidSource);
    }
}