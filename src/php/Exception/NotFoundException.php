<?php
namespace Lucid\Container\Exception;

class NotFoundException extends \Exception implements \Interop\Container\Exception\NotFoundException
{
    public function __construct($id, $availableIndices)
    {
        $this->message = "Unable to locate index $id in container. Valid indices are: ".implode(', ', $availableIndices);
    }
}