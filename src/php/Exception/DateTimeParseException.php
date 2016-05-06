<?php
namespace Lucid\Container\Exception;

class DateTimeParseException extends \Exception
{
    public function __construct($id, $value, $formats)
    {
        $this->message = 'Container was unable to create DateTime object from index '.$id.', value '.$value.'. This container supported the following DateTime formats: '.implode(', ', $formats);
    }
}