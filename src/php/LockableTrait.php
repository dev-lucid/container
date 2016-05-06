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
 * Used to lock and unlock indexes. Locked indexes are read only, until unlocked.
 *
 * @author Mike Thorn <mthorn@devlucid.com>
 */
trait LockableTrait
{
     /**
     * Associative array of locks. The key for the array is the index inside the container.
     *
     * @var array
     */
    protected $locks = [];

    /**
     * Locks an index
     *
     * @param string $id The index to lock
     */
    public function lock(string $id) : LockableInterface
    {
        $this->locks[$id] = true;
        return $this;
    }

    /**
     * Unlocks an index
     *
     * @param string $id The index to unlock
     */
    public function unlock(string $id) : LockableInterface
    {
        if (isset($this->locks[$id]) === true) {
            unset($this->locks[$id]);
        }
        return $this;
    }
}
