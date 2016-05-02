<?php
namespace Lucid\Component\Container;

class DelegateContainer extends Container
{
    protected $parent = null;
    protected $children = [];

    public function &get(string $id)
    {
        if (is_null($this->parent) === false){
            if ($this->parent->has($id) === true) {
                return $this->parent->get($id);
            }
        }

        if ($this->has($id) === false) {
            foreach ($this->children as $child) {
                if ($child->has($id) === true) {
                    return $child->get($id);
                }
            }
            throw new NotFoundException($id, array_keys($this->getArray()));
        }
        $value =& $this->source[$id];
        return $value;
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
