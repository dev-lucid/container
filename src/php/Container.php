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

class Container implements \Interop\Container\ContainerInterface, ContainerInterface, TypedGetterInterface, LockableInterface, DelegateInterface, \ArrayAccess, \Iterator, \Countable
{
    use TypedGetterTrait, LockableTrait, DelegateTrait, ArrayIteratorCountableTrait;

    protected $source             = [];
    protected $requiredInterfaces = [];

    public function __construct()
    {
    }

    public function requireInterfacesForIndex(string $id, ...$interfaces) : ContainerInterface
    {
        if (isset($this->requiredInterfaces[$id]) === false) {
            $this->requiredInterfaces[$id] = [];
        }
        $this->requiredInterfaces[$id] = array_merge($this->requiredInterfaces[$id], $interfaces);
        if ($this->has($id) === true) {
            $this->checkRequiredInterfaces($id);
        }
        return $this;
    }

    protected function checkRequiredInterfaces(string $id) : ContainerInterface
    {
        if (isset($this->requiredInterfaces[$id]) === false) {
            return $this;
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
        return $this;
    }

    public function setSource(&$newSource) : ContainerInterface
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

    public function has($id) : bool
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

            throw new Exception\NotFoundException($id, array_keys($this->getValues()));
        }

        $value =& $this->source[$id];
        return $value;
    }

    protected function getFromAdditionalSources(string $id)
    {
        return false;
    }

    public function delete($id)  : ContainerInterface
    {
        unset($this->source[$id]);
        return $this;
    }

    public function set($id, $newValue) : ContainerInterface
    {
        if (isset($this->locks[$id]) === true) {
            throw new Exception\LockedIndexException($id);
        }
        $this->source[$id] =& $newValue;
        $this->checkRequiredInterfaces($id);
        return $this;
    }

    public function setValues(array $newValues) : ContainerInterface
    {
        foreach ($newValues as $id=>$newValue) {
            $this->set($id, $newValue);
        }
        return $this;
    }

    public function getValues() : array
    {
        if (is_array($this->source) === true) {
            return $this->source;
        } else {
            $returnArray = [];
            foreach ($this->source as $id=>$value) {
                $returnArray[$id] = $value;
            }
            return $returnArray;
        }
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
