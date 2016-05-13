<?php
namespace Lucid\Container\Constructor\Parameter;

class Fixed extends AbstractParameter
{
    protected $value;
    public function __construct(string $name, $value)
    {
        $this->name  = $name;
        $this->value = $value;
    }

    public function getValue(ParameterInjectorInterface $injector)
    {
        return $this->value;
    }
}
