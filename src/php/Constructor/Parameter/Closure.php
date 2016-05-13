<?php
namespace Lucid\Container\Constructor\Parameter;

class Closure extends AbstractParameter
{
    protected $closure;
    public function __construct(string $name, callable $closure)
    {
        $this->name  = $name;
        $this->closure = $closure;
    }

    public function getValue(ParameterInjectorInterface $injector)
    {
        $closure = $this->closure;
        $parameters = $injector->determineParameterValues(new \ReflectionFunction($closure));
        return $closure(...$parameters);
    }
}
