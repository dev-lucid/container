<?php
namespace Lucid\Component\Container;

class NotFoundException extends \Exception
{
    public function __construct(string $index, array $existingIndexes)
    {
        $this->message = 'Could not find index '.$index.' in container. Valid indexes are: '.implode(', ', $existingIndexes);
    }
}
