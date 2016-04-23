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

    protected function buildFinalId(string $id)
    {
        $finalId = null;
        if (strpos($id, '../') === 0) {
            return substr($id, 3);
        } else {
            return $this->prefix . $id;
        }
    }

    public function has(string $id)
    {
        return $this->container->has($this->buildFinalId($id));
    }

    public function &get(string $id, $defaultValue = null)
    {
        $value =& $this->container->get($this->buildFinalId($id), $defaultValue = null);
        return $value;
    }

    public function set(string $id, $newValue)
    {
        return $this->container->set($this->buildFinalId($id), $newValue);
    }

    public function delete(string $id)
    {
        return $this->container->delete($this->buildFinalId($id));
    }

    public function string(string $id, $defaultValue = null)
    {
        return $this->container->string($this->buildFinalId($id), $defaultValue);
    }

    public function int(string $id, $defaultValue = null)
    {
        return $this->container->int($this->buildFinalId($id), $defaultValue);
    }

    public function float(string $id, $defaultValue = null)
    {
        return $this->container->float($this->buildFinalId($id), $defaultValue);
    }

    public function bool(string $id, $defaultValue = null)
    {
        return $this->container->bool($this->buildFinalId($id), $defaultValue);
    }

    public function DateTime(string $id, $defaultValue = null)
    {
        return $this->container->DateTime($this->buildFinalId($id), $defaultValue);
    }

    public function __call($method, $parameters)
    {
        return $this->container->$method(...$parameters);
    }
}