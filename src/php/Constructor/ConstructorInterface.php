<?php
namespace Lucid\Container\Constructor;

interface ConstructorInterface
{
    public function getKey() : string;
    public function getType() : string;
    public function isSingleton() : bool;
    public function setConstructorParameterInjector(Parameter\ParameterInjectorInterface $constructorParameterInjector);
    public function copyFromPrefix(string $id = '', string $type = null);

    public function canConstructByIdAndType(string $idToCheck = null, string $typeToCheck = null) : bool;
    public function canConstructByClass(string $classToCheck) : bool;
    public function canConstructByClassPrefix(string $id, string $classToCheck) : bool;
    public function canConstructByInterface(string $interfaceToCheck) : bool;
    public function construct(array $values = []);
}