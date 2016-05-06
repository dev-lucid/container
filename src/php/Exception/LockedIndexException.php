<?php
namespace Lucid\Container\Exception;

class LockedIndexException extends \Exception
{
    public function __construct($id)
    {
        $this->message = 'Tried to set locked index '.$id.'. You must unlock it first, and do consider why it was locked in the first place.';
    }
}