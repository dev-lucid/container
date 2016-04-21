<?php
namespace Lucid\Component\Container;

class RequestContainer extends Container
{
    protected $boolTrueValues  = ['on', '1', 1, 'yes', 'true', true];
    protected $boolFalseValues = ['', '0', 0, 'no', 'false', false, null];

    public function __construct()
    {
        $this->source =& $_REQUEST;
    }

    public function setBoolTrueValues(...$newValues)
    {
        $this->boolTrueValues = $newValues;
    }

    public function setBoolFalseValues(...$newValues)
    {
        $this->boolFalseValues = $newValues;
    }

    public function bool(string $id, $defaultValue = false) : bool
    {
        if (isset($this->source[$id]) === false) {
            return $defaultValue;
        }

        if (in_array($this->source[$id], $this->boolTrueValues, true) === true) {
            return true;
        }

        if (in_array($this->source[$id], $this->boolFalseValues, true) === true) {
            return false;
        }
        return $defaultValue;
    }
}
