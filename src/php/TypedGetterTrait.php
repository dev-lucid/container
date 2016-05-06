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

/**
 * Functions used to get data out of the container and cast them to a particular type.
 *
 * @author Mike Thorn <mthorn@devlucid.com>
 */
trait TypedGetterTrait
{
    /**
     * List of formats that ->DateTime() will try to convert values using \DateTime::createFromFormat
     * See: http://php.net/manual/en/datetime.createfromformat.php
     *
     * @var array
     */
    protected $dateTimeFormats = [\DateTime::ISO8601, \DateTime::W3C, 'Y-m-d H:i', 'U'];

    /**
     * List of values that will result in true when ->bool is called.
     *
     * @var array
     */
    protected $boolTrueValues  = [true];

    /**
     * List of values that will result in false when ->bool is called.
     *
     * @var array
     */
    protected $boolFalseValues = [false];

    /**
     * List of formats that ->DateTime() will try to convert values using \DateTime::createFromFormat
     * See: http://php.net/manual/en/datetime.createfromformat.php
     *
     * @param string $appName
     */
    public function setDateTimeFormats(...$newFormats) : TypedGetterInterface
    {
        $this->dateTimeStringFormats = $newFormats;
        return $this;
    }

    /**
     * Sets the list of values that will result in true when ->bool is called.
     *
     * @var array
     */
    public function setBoolTrueValues(...$newValues) : TypedGetterInterface
    {
        $this->boolTrueValues = $newValues;
        return $this;
    }

    /**
     * Sets the list of values that will result in false when ->bool is called.
     *
     * @var array
     */
    public function setBoolFalseValues(...$newValues) : TypedGetterInterface
    {
        $this->boolFalseValues = $newValues;
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
        throw new Exception\InvalidBooleanException($id, $value, $this->boolTrueValues, $this->boolFalseValues);
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
        throw new Exception\DateTimeParseException($id, $value, $this->dateTimeFormats);
    }

    public function array(string $id, array $defaultValue = [], string $delimiter=',') : array
    {
        if ($this->has($id) === false) {
            return $defaultValue;
        }
        $stringValue = $this->string($id);
        return explode($delimiter, $stringValue);
    }
}
