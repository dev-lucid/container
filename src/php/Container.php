<?php
namespace Lucid\Component\Container;

class Container implements ContainerInterface, \ArrayAccess, \Iterator, \Countable
{
    use LockableTrait, ConstructionTrait, DelegateTrait, ArrayIteratorCountableTrait;
    
    protected $source             = [];
    protected $dateTimeFormats    = [\DateTime::ISO8601, \DateTime::W3C, 'Y-m-d H:i', 'U'];
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
            throw new RequiredInterfaceException('Container index '.$id.' does not contain an object, but this index is required to be an object that implements the following interfaces: '.implode(', ', $this->requiredInterfaces[$id]));
            # throw new \Exception('Container index '.$id.' does not contain an object, but this index is required to be an object that implements the following interfaces: '.implode(', ', $this->requiredInterfaces[$id]));
        }

        $implements = class_implements($this->source[$id]);
        foreach ($this->requiredInterfaces[$id] as $interface) {
            if (in_array($interface, $implements) === false) {
                throw new RequiredInterfaceException('Container index '.$id.' does not implement a required interface. This index must implement the following interfaces: '.implode(', ', $this->requiredInterfaces[$id]));
                # throw new \Exception();
            }
        }
    }

    public function setDateTimeFormats(...$newFormats)
    {
        $this->dateTimeStringFormats = $newFormats;
        return $this;
    }

    public function setSource(&$newSource)
    {
        if (is_array($newSource) === true ) {
            $this->source =& $newSource;
        } elseif (is_object($newSource) === true) {
            $classImplements = class_implements($newSource);

            if (in_array('ArrayAccess', $classImplements) === false || in_array('Iterator', $classImplements) === false) {
                throw new InvalidSourceException();
            }
            $this->source =& $newSource;
        } else {
            throw new InvalidSourceException();
        }

        return $this;
    }

    public function has(string $id) : bool
    {

        $has = (isset($this->source[$id]) === true);


        return $has;
    }

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
            
            $constructor = $this->findConstructor($id);
            if ($constructor !== false) {
                $object = $this->construct($id, $constructor);
                return $object;
            }
            throw new NotFoundException($id, array_keys($this->getArray()));
        }

        $value =& $this->source[$id];
        return $value;
    }    

    public function delete(string $id)
    {
        unset($this->source[$id]);
        return $this;
    }

    public function set(string $id, $newValue)
    {
        if (isset($this->locks[$id]) === true) {
            throw new LockedIndexException();
        }
        $this->source[$id] =& $newValue;
        $this->checkRequiredInterfaces($id);
        return $this;
    }

    public function string(string $id, string $defaultValue = '') : string
    {
        if ($this->has($id) === false) {
            return $defaultValue;
        }
        $value = $this->get($id);
        return strval($value);
    }

    public function int(string $id, int $defaultValue = -1) : int
    {
        if ($this->has($id) === false) {
            return $defaultValue;
        }
        $value = $this->get($id);
        return intval($value);
    }

    public function float(string $id, float $defaultValue = -1): float
    {
        if ($this->has($id) === false) {
            return $defaultValue;
        }
        $value = $this->get($id, $defaultValue);
        return floatval($value);
    }

    public function bool(string $id, bool $defaultValue=false) : bool
    {
        if ($this->has($id) === false) {
            return $defaultValue;
        }
        $value = $this->get($id);
        return boolval($value);
    }

    public function DateTime(string $id, DateTime $defaultValue = null) : \DateTime
    {
        if ($this->has($id) === false) {
            return $defaultValue;
        }
        $value = $this->get($id, $defaultValue);

        if (is_numeric($value) === true) {
            return \DateTime::createFromFormat('U', $value);
        } elseif (is_string($value) === true) {
            foreach ($this->dateTimeFormats as $format) {
                $parseResult = \DateTime::createFromFormat($format, $value);
                if ($parseResult !== false) {
                    return $parseResult;
                }
            }
        }
        throw new DateTimeParseException($value, $this->dateTimeFormats);
    }

    public function getArray() : array
    {
        if (is_array($this->source) === true) {
            return $this->source;
        } else {
            $returnArray = [];
            foreach ($this->source as $key=>$value) {
                $returnArray[$key] = $value;
            }
            return $returnArray;
        }
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
        if (isset($parameters[0])) {
            $this->set($method, $parameters[0]);
            return $this;
        }
        $value =& $this->get($method);
        return $value;
    }
}
