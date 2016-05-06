<?php
namespace Lucid\Container;

class Container implements \Interop\Container\ContainerInterface, ContainerInterface, TypedGetterInterface, LockableInterface, DelegateInterface, \ArrayAccess, \Iterator, \Countable
{
    use TypedGetterTrait, LockableTrait, DelegateTrait, ArrayIteratorCountableTrait;

    protected $source             = [];
    protected $requiredInterfaces = [];

    public function __construct()
    {
    }

    public function requireInterfacesForIndex(string $id, ...$interfaces)
    {
        if (isset($this->requiredInterfaces[$id]) === false) {
            $this->requiredInterfaces[$id] = [];
        }
        $this->requiredInterfaces[$id] = array_merge($this->requiredInterfaces[$id], $interfaces);
        if ($this->has($id) === true) {
            $this->checkRequiredInterfaces($id);
        }
    }

    protected function checkRequiredInterfaces(string $id)
    {
        if (isset($this->requiredInterfaces[$id]) === false) {
            return;
        }

        if (is_object($this->source[$id]) === false) {
            throw new Exception\RequiredInterfaceException($id, $this->requiredInterfaces[$id]);
        }

        $implements = class_implements($this->source[$id]);
        foreach ($this->requiredInterfaces[$id] as $interface) {
            if (in_array($interface, $implements) === false) {
                throw new Exception\RequiredInterfaceException($id, $this->requiredInterfaces[$id]);
            }
        }
    }

    public function setSource(&$newSource)
    {
        if (is_array($newSource) === true ) {
            $this->source =& $newSource;
        } elseif (is_object($newSource) === true) {
            $classImplements = class_implements($newSource);

            if (in_array('ArrayAccess', $classImplements) === false || in_array('Iterator', $classImplements) === false) {
                throw new Exception\InvalidSourceException();
            }
            $this->source =& $newSource;
        } else {
            throw new Exception\InvalidSourceException();
        }

        return $this;
    }

    public function has($id)
    {
        $has = (isset($this->source[$id]) === true);
        return $has;
    }

    public function &get($id)
    {
        if (is_null($this->parent) === false){
            if ($this->parent->has($id) === true) {
                $value =& $this->parent->get($id);
                return $value;
            }
        }

        if ($this->has($id) === false) {
            foreach ($this->children as $child) {
                if ($child->has($id) === true) {
                    $value =& $child->get($id);
                    return $value;
                }
            }

            $additionalSource = $this->getFromAdditionalSources($id);
            if ($additionalSource !== false) {
                return $additionalSource;
            }

            throw new Exception\NotFoundException($id, array_keys($this->array()));
        }

        $value =& $this->source[$id];
        return $value;
    }

    protected function getFromAdditionalSources(string $id)
    {
        return false;
    }

    public function delete($id)
    {
        unset($this->source[$id]);
        return $this;
    }

    public function set($id, $newValue)
    {
        if (isset($this->locks[$id]) === true) {
            throw new Exception\LockedIndexException($id);
        }
        $this->source[$id] =& $newValue;
        $this->checkRequiredInterfaces($id);
        return $this;
    }

    public function setValues(array $array)
    {
        foreach ($array as $key=>$value) {
            $this->set($key, $value);
        }
        return $this;
    }

    public function &__call($method, $parameters)
    {
        $value =& $this->get($method);
        if (is_callable($value) === true) {
            $return = $value(...$parameters);
            return $return;
        }
        return $value;
    }
}
