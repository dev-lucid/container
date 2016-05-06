<?php
namespace Lucid\Container\Exception;

class InvalidSourceException extends \Exception
{
    protected $message = 'Source for store must either be an array, or an object whose class implements the ArrayAccess and Iterator interfaces.';
}