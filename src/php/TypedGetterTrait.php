<?php
namespace Lucid\Container;

trait TypedGetterTrait
{
    protected $dateTimeFormats = [\DateTime::ISO8601, \DateTime::W3C, 'Y-m-d H:i', 'U'];
    protected $boolTrueValues  = [true];
    protected $boolFalseValues = [false];

    public function setBoolTrueValues(...$newValues)
    {
        $this->boolTrueValues = $newValues;
    }

    public function setBoolFalseValues(...$newValues)
    {
        $this->boolFalseValues = $newValues;
    }

    public function setDateTimeFormats(...$newFormats)
    {
        $this->dateTimeStringFormats = $newFormats;
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
        throw new Exception\InvalidBooleanException($value, $this->boolTrueValues, $this->boolFalseValues);
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
        throw new Exception\DateTimeParseException($value, $this->dateTimeFormats);
    }

    public function array() : array
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
}
