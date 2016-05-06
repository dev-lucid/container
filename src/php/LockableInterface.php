<?php
namespace Lucid\Container;

interface LockableInterface
{
    public function lock(string $id);
    public function unlock(string $id);
}
