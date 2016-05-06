<?php
namespace Lucid\Container\Exception;

class InvalidBooleanException extends \Exception
{
    public function __construct($id, $value, $trueValues, $falseValues)
    {
        $this->message = 'Could not convert index '.$id.', value '.$value.' to a boolean. Valid true values are: '. implode(', ', $trueValues).'; valid false values are: '.implode(', ', $falseValues).'.';
    }
}