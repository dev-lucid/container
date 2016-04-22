<?php
namespace Lucid\Component\Container;

class PrefixDecorator
{
    protected $prefix    = null;
    protected $container = null;

    function __construct(string $prefix = '', $container)
    {
        $this->prefix = $prefix;
        $this->container = $container;
    }

    public function has(string $id)
    {
        return $this->container->has($this->prefix.$id);
    }

    public function &get(string $id, $defaultValue = null)
    {
        $value = &$this->container->get($this->prefix.$id, $defaultValue = null);
        return $value;
    }

    public function set(string $id, $newValue)
    {
        return $this->container->set($this->prefix.$id, $newValue);
    }

    public function un_set(string $id)
    {
        return $this->container->un_set($this->prefix.$id);
    }

    public function string(string $id, $defaultValue = null)
    {
        return $this->container->string($this->prefix.$id, $defaultValue);
    }

    public function int(string $id, $defaultValue = null)
    {
        return $this->container->int($this->prefix.$id, $defaultValue);
    }

    public function float(string $id, $defaultValue = null)
    {
        return $this->container->float($this->prefix.$id, $defaultValue);
    }

    public function bool(string $id, $defaultValue = null)
    {
        return $this->container->bool($this->prefix.$id, $defaultValue);
    }

    public function DateTime(string $id, $defaultValue = null)
    {
        return $this->container->DateTime($this->prefix.$id, $defaultValue);
    }

    public function __call($method, $parameters)
    {
        return $this->container->$method(...$parameters);
    }
}