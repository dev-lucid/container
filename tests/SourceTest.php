<?php
use Lucid\Component\Container\Container;

class SourceTest extends \PHPUnit_Framework_TestCase
{
    public $container = null;


    public function testInvalidSource()
    {
        $container = new Container();
        $source = [];
        $container->setSource($source);

        $this->setExpectedException(\Lucid\Component\Container\InvalidSourceException::class);
        $invalidSource = 'hi';
        $container->setSource($invalidSource);
    }
}