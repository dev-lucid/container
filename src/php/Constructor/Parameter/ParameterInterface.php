<?php
namespace Lucid\Container\Constructor\Parameter;

interface ParameterInterface
{
    public function getName() : string;
    public function getValue(ParameterInjectorInterface $injector);
}
