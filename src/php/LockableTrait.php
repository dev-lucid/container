<?php 
namespace Lucid\Component\Container;

trait LockableTrait 
{
    protected $locks = [];
    
    public function lock(string $id)
    {
        $this->locks[$id] = true;
        return $this;
    }

    public function unlock(string $id)
    {
        if (isset($this->locks[$id]) === true) {
            unset($this->locks[$id]);
        }
        return $this;
    }
}
