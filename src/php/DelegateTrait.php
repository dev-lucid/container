<?php
namespace Lucid\Container;

trait DelegateTrait
{
    protected $parent   = null;
    protected $children = [];

    public function hasParent() : bool
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

    public function setAsChildContainerOf(DelegateInterface $parentContainer)
    {
        $this->setParentContainer($parentContainer);
        $parentContainer->addChildContainer($this);
        return $this;
    }

    public function setAsParentContainerOf(DelegateInterface $childContainer)
    {
        $this->addChildContainer($childContainer);
        $childContainer->setParentContainer($this);
        return $this;
    }

    public function setParentContainer(DelegateInterface $parentContainer)
    {
        $this->parent = $parentContainer;
    }

    public function addChildContainer(DelegateInterface $childContainer)
    {
        $this->children[] = $childContainer;
    }
}
