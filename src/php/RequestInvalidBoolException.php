<?php
namespace Lucid\Component\Container;

class RequestInvalidBoolException extends \Exception
{
    public function __construct($value, $trueValues, $falseValues)
    {
        $this->message = 'Could not convert value to bool: '.$value.'. Valid true values are: '. implode(', ', $trueValues).'; valid false values are: '.implode(', ', $falseValues).'.';
    }
}