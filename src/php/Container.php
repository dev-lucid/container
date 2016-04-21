<?php
namespace Lucid\Component\Container;

class Container implements ContainerInterface
{
    protected $source = [];
    protected $dateTimeStringFormat = \DateTime::ISO8601;

    public function __construct()
    {
    }

    public function setDateTimeStringFormat(string $newFormat)
    {
        $this->dateTimeStringFormat = $newFormat;
        return $this;
    }

    public function setSource(&$newSource)
    {

        $invalidSourceMessage = 'Source for store must either be an array, or an object whose class implements the ArrayAccess and Iterator interfaces.';
        if (is_array($newSource) === false ) {
            if (is_object($newSource) === true) {
                $classImplements = class_implements($newSource);
                if (in_array('ArrayAccess', $classImplements) === false && in_array('Iterator', $classImplements) === false) {
                    throw new \Exception($invalidSourceMessage);
                }
            } else {
                throw new \Exception($invalidSourceMessage);
            }
        }
        $this->source =& $newSource;
        return $this;
    }

    public function has(string $id) : bool
    {
        return isset($this->source[$id]);
    }

    public function get(string $id, $defaultValue = null)
    {
        return $this->source[$id] ?? $defaultValue;
    }

    public function un_set(string $id)
    {
        unset($this->source[$id]);
        return $this;
    }

    public function set(string $id, $newValue)
    {
        $this->source[$id] = $newValue;
        return $this;
    }

    public function string(string $id, $defaultValue = null) : string
    {
        return strval($this->source[$id] ?? $defaultValue);
    }

    public function int(string $id, $defaultValue = null) : int
    {
        if (isset($this->source[$id]) === true) {
            return intval($this->source[$id]);
        } else {
            return $defaultValue;
        }
    }

    public function float(string $id, $defaultValue = null): float
    {
        if (isset($this->source[$id]) === true) {
            return floatval($this->source[$id]);
        } else {
            return $defaultValue;
        }
    }

    public function bool(string $id, $defaultValue=null) : bool
    {
        if (isset($this->source[$id]) === true) {
            return boolval($this->source[$id]);
        } else {
            return $defaultValue;
        }
    }

    public function DateTime(string $id, $defaultValue = null) : \DateTime
    {
        $val = null;
        if (isset($this->source[$id]) === true) {
            if (is_numeric($this->source[$id]) === true) {
                return \DateTime::createFromFormat('U', $this->source[$id]);
            } elseif (is_string($this->source[$id]) === true) {
                return \DateTime::createFromFormat($this->dateTimeStringFormat, $this->source[$id]);
            } else {
                throw new \Exception('Not sure how to convert to DateTime: '.$this->source[$id]);
            }
        }
        return $defaultValue;
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
}
