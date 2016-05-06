<?php
namespace Lucid\Container;

interface InjectorFactoryInterface
{
    public function construct(string $id);
    public function execute($idOrObject, string $method);
}