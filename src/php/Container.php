<?php
namespace Lucid\Component\Container;

class Container implements ContainerInterface, \ArrayAccess, \Iterator, \Countable
{
    protected $source = [];
    protected $dateTimeFormats = [\DateTime::ISO8601, \DateTime::W3C, 'U'];
    protected $requiredInterfaces = [];
    protected $locks = [];

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

    public function setDateTimeFormats(...$newFormats)
    {
        $this->dateTimeStringFormats = $newFormats;
        return $this;
    }

    public function setSource(&$newSource)
    {
        if (is_array($newSource) === true ) {
            $this->source =& $newSource;
        } else if (is_object($newSource) === true) {
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
        return isset($this->source[$id]);
    }

    public function &get(string $id)
    {
        if ($this->has($id) === false) {
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
        $val = null;
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
        $defaultValue = $parameters[0] ?? null;
        $value =& $this->get($method, $defaultValue);
        return $value;
    }

    /* ArrayAccess methods: start */
    public function offsetExists($id)
    {
        return $this->has($id);
    }

    public function &offsetGet($id)
    {
        $value =& $this->get($id);
        return $value;
    }

    public function offsetSet($id, $newValue)
    {
        $this->set($id, $newValue);
    }

    public function offsetUnset($id)
    {
        $this->delete($id);
    }
    /* ArrayAccess methods: end */

    /* Iterator methods: start */
    function rewind() {
        reset($this->source);
    }

    function current() {
        return current($this->source);
    }

    function key() {
        return key($this->source);
    }

    function next() {
        next($this->source);
    }

    function valid() {
        return key($this->source) !== null;
    }
    /* Iterator methods: end */

    /* Countable methods: start */
    function count()
    {
        return count(array_keys($this->getArray()));
    }
    /* Countable methods: end */
}
