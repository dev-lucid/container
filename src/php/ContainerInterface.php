<?php
namespace Lucid\Component\Container;

interface ContainerInterface
{
    public function setSource(&$source);

    public function has(string $id);
    public function get(string $id, $defaultValue);

    public function delete(string $id);
    public function set(string $id, $defaultValue);

    public function string(string $id, $defaultValue);
    public function int(string $id, $defaultValue);
    public function float(string $id, $defaultValue);
    public function bool(string $id, $defaultValue);
    public function DateTime(string $id, $defaultValue);

    public function getArray();
    public function setValues(array $array);
}
