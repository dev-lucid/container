<?php
namespace Lucid\Container\Exception;

class RequiredInterfaceException extends \Exception
{
    public function __construct(string $id, array $interfaces)
    {
        $this->message = 'Container index '.$id.' does not contain an object, but this index is required to be an object that implements the following interfaces: '.implode(', ', $interfaces);
    }
}
