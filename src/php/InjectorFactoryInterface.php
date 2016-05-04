<?php
namespace Lucid\Component\Container;

interface InjectorFactoryInterface
{
    public function construct(string $name);
    public function execute(string $name, string $method);
}