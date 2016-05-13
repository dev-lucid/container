<?php
namespace Lucid\Container\Constructor\Parameter;

class Container extends AbstractParameter
{
    protected $value;
    protected $containerIndex;
    protected $type;
    public function __construct(string $name, string $containerIndex, $type = null)
    {
        $this->name  = $name;
        $this->containerIndex = $containerIndex;
        $this->type = $type;
    }

    public function getValue(ParameterInjectorInterface $injector)
    {
        $isConstructable = (class_exists($this->type) || interface_exists($this->type));
        if ($isConstructable === true) {
            return $injector->construct($this->containerIndex, $this->type);
        } else {
            return $injector->get($this->containerIndex);
        }
    }
}
