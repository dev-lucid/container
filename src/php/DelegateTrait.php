<?php
/*
 * This file is part of the Lucid Container package.
 *
 * (c) Mike Thorn <mthorn@devlucid.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lucid\Container;

/**
 * Used to create parent/child relationships between containers.
 * Info on delegate lookup features here: https://github.com/container-interop/fig-standards/blob/master/proposed/container-meta.md
 *
 * @author Mike Thorn <mthorn@devlucid.com>
 */
trait DelegateTrait
{
    /**
     * Stores the parent container
     *
     * @var \Lucid\Container\DelegateInterface
     */
    protected $parent   = null;

    /**
     * Stores list of child containers
     *
     * @var array of \Lucid\Container\DelegateInterface
     */
    protected $children = [];

    /**
     * returns boolean true if this container has a parent container, false if not
     */
    public function hasParent() : bool
    {
        return (is_null($this->parent) === false);
    }

    /**
     * returns the container's parent if it has one, throw an exception if it does not.
     */
    public function getParent() : DelegateInterface
    {
        if (is_null($this->parent) === true) {
            throw new \Exception('Container->getParent() was called, but this container does not have a parent. Call ->hasParent():bool first, or catch this exception');
        }
        return $this->parent;
    }

    /**
     * Moves up in the delegate hierarchy until it finds a container wihout a parent, then returns that container
     */
    public function findRootContainer() : DelegateInterface
    {
        $obj = $this;
        while($obj->hasParent() === true) {
            $obj = $obj->getParent();
        }
        return $obj;
    }

    /**
     * Sets the current container to be a child container of a container
     *
     * @param DelegateInterface $parentContainer The container to be set as the parent of the container
     */
    public function setAsChildContainerOf(DelegateInterface $parentContainer) : DelegateInterface
    {
        $this->setParentContainer($parentContainer);
        $parentContainer->addChildContainer($this);
        return $this;
    }

    /**
     * Sets the current container to be the parent container of the container passed.
     *
     * @param DelegateInterface $childContainer The container to be set as the child of the container
     */
    public function setAsParentContainerOf(DelegateInterface $childContainer) : DelegateInterface
    {
        $this->addChildContainer($childContainer);
        $childContainer->setParentContainer($this);
        return $this;
    }

    /**
     * Actually sets the protected ->parent property. Not intended to be called directly, but has to remain
     * public so that it can be called from another class.
     */
    public function setParentContainer(DelegateInterface $parentContainer)
    {
        $this->parent = $parentContainer;
    }

    /**
     * Appends to the  protected ->children property. Not intended to be called directly, but has to remain
     * public so that it can be called from another class.
     */
    public function addChildContainer(DelegateInterface $childContainer)
    {
        $this->children[] = $childContainer;
    }
}
