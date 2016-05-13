<?php
namespace Lucid\Container\Constructor\Parameter;

interface ParameterInjectorInterface
{
    public function determineParameterValue(\ReflectionParameter $parameter, ParameterInterface $configuredParameter = null, array $values = []);
    public function determineParameterValues(\ReflectionFunctionAbstract $function, array $values = []) : array;
}