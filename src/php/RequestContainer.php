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

    public function bool(string $id, bool $defaultValue = false) : bool
    {
        if ($this->has($id) === false) {
            return $defaultValue;
        }
        $value = $this->get($id);

        if (in_array($value, $this->boolTrueValues, true) === true) {
            return true;
        }

        if (in_array($value, $this->boolFalseValues, true) === true) {
            return false;
        }
        throw new RequestInvalidBoolException($value, $this->boolTrueValues, $this->boolFalseValues);
    }
}
