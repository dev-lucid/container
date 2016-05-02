<?php
use Lucid\Component\Container\Container;

class DelegateTest extends \PHPUnit_Framework_TestCase
{
    public $parent = null;
    public $child1 = null;
    public $child2 = null;

    public function setup()
    {
        $this->parent = new Container();
        $this->parent->set('parent1', 'value1');
        $this->parent->set('parent2', 'value2');

        $this->child1 = new Container();
        $this->child1->set('child1', 'value3');
        $this->child1->set('child2', 'value4');

        $this->child2 = new Container();
        $this->child2->set('child2', 'value5');
        $this->child2->set('child3', 'value6');

        $this->parent->setAsParentContainerOf($this->child1);
        $this->child2->setAsChildContainerOf($this->parent);
    }

    public function testParentDelegate()
    {
        $this->assertEquals($this->parent->get('parent1'), 'value1');
        $this->assertEquals($this->parent->get('parent2'), 'value2');
        $this->assertEquals($this->parent->get('child1'), 'value3');
        $this->assertEquals($this->parent->get('child2'), 'value4');
        $this->assertEquals($this->parent->get('child3'), 'value6');
    }

    public function testChildDelegate()
    {
        $this->assertEquals($this->child1->get('parent1'), 'value1');
        $this->assertEquals($this->child1->get('parent2'), 'value2');
        $this->assertEquals($this->child1->get('child1'), 'value3');
        $this->assertEquals($this->child1->get('child2'), 'value4');

        $this->assertEquals($this->child2->get('parent1'), 'value1');
        $this->assertEquals($this->child2->get('parent2'), 'value2');
        $this->assertEquals($this->child2->get('child2'), 'value5');
        $this->assertEquals($this->child2->get('child3'), 'value6');
    }
}