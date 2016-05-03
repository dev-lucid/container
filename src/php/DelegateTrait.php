<?php 
namespace Lucid\Component\Container;

trait DelegateTrait
{
    protected $parent   = null;
    protected $children = [];
    
    public function hasParent()
    {
        return (is_null($this->parent) === false);
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function findRootContainer()
    {
        $obj = $this;
        while($obj->hasParent() === true) {
            $obj = $obj->getParent();
        }
        return $obj;
    }
    
    public function setAsChildContainerOf($parentContainer)
    {
        $this->setParentContainer($parentContainer);
        $parentContainer->addChildContainer($this);
        return $this;
    }

    public function setAsParentContainerOf($childContainer)
    {
        $this->addChildContainer($childContainer);
        $childContainer->setParentContainer($this);
        return $this;
    }

    public function setParentContainer($parentContainer)
    {
        $this->parent = $parentContainer;
    }

    public function addChildContainer($childContainer)
    {
        $this->children[] = $childContainer;
    }
}
