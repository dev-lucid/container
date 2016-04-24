<?php
namespace Lucid\Component\Container;

class DateTimeParseException extends \Exception
{
    public function __construct($value, $formats)
    {
        $this->message = 'Container was unable to create DateTime object from value '.$value.'. This container supported the following DateTime formats: '.implode(', ', $formats);
    }
}