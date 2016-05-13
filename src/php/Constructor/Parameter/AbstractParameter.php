<?php
namespace Lucid\Container\Constructor\Parameter;

abstract class AbstractParameter implements ParameterInterface
{
    protected $name;
    public function getName() : string
    {
        return $this->name;
    }
}
