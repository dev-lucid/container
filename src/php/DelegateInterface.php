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
 * Interface used to to implement delegate parent/child relationships. Note that implementing this
 * alone is likely not enough to change a simple container into one that fully implements the delegate lookup
 * functionality as outlined in the container interop proposal (https://github.com/container-interop/fig-standards/blob/master/proposed/container-meta.md)
 * as the proposal also details how dependency injection behavior is modified in a hierarchy.
 *
 * @author Mike Thorn <mthorn@devlucid.com>
 */
interface DelegateInterface
{
    /**
     * returns boolean true if this container has a parent container, false if not
     */
    public function hasParent();

    /**
     * returns the container's parent if it has one, throw an exception if it does not.
     */
    public function getParent();

    /**
     * Moves up in the delegate hierarchy until it finds a container wihout a parent, then returns that container
     */
    public function findRootContainer();

    /**
     * Sets the current container to be a child container of a container
     *
     * @param DelegateInterface $parentContainer The container to be set as the parent of the container
     */
    public function setAsChildContainerOf(DelegateInterface $parentContainer);

    /**
     * Sets the current container to be the parent container of the container passed.
     *
     * @param DelegateInterface $childContainer The container to be set as the child of the container
     */
    public function setAsParentContainerOf(DelegateInterface $childContainer);

    /**
     * Actually sets the protected ->parent property. Not intended to be called directly, but has to remain
     * public so that it can be called from another class.
     */
    public function setParentContainer(DelegateInterface $parentContainer);

    /**
     * Appends to the  protected ->children property. Not intended to be called directly, but has to remain
     * public so that it can be called from another class.
     */
    public function addChildContainer(DelegateInterface $childContainer);
}
