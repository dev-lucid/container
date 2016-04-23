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

    public function &get(string $id, $defaultValue = null)
    {
        $value =& $this->source[$id] ?? $defaultValue;
        return $value;
    }

    public function delete(string $id)
    {
        unset($this->source[$id]);
        return $this;
    }

    public function set(string $id, $newValue)
    {
        $this->source[$id] =& $newValue;
        return $this;
    }

    public function string(string $id, $defaultValue = null) : string
    {
        $value = $this->get($id, $defaultValue);
        return strval($value);
    }

    public function int(string $id, $defaultValue = null) : int
    {
        $value = $this->get($id, $defaultValue);
        return intval($value);
    }

    public function float(string $id, $defaultValue = null): float
    {
        $value = $this->get($id, $defaultValue);
        return floatval($value);
    }

    public function bool(string $id, $defaultValue=null) : bool
    {
        $value = $this->get($id, $defaultValue);
        return boolval($value);
    }

    public function DateTime(string $id, $defaultValue = null) : \DateTime
    {
        $val = null;
        $value = $this->get($id, $defaultValue);

        if (is_numeric($value) === true) {
            return \DateTime::createFromFormat('U', $value);
        } elseif (is_string($value) === true) {
            return \DateTime::createFromFormat($this->dateTimeStringFormat, $value);
        } else {
            throw new \Exception('Not sure how to convert to DateTime: '.$value);
        }
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
