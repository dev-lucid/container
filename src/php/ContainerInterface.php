<?php
namespace Lucid\Component\Container;

interface ContainerInterface
{
    public function setSource(&$source);

    public function has(string $id);
    public function get(string $id);

    public function delete(string $id);
    public function set(string $id, $newValue);

    public function string(string $id, string $defaultValue);
    public function int(string $id, int $defaultValue);
    public function float(string $id, float $defaultValue);
    public function bool(string $id, bool $defaultValue);
    public function DateTime(string $id, DateTime $defaultValue);

    public function getArray();
    public function setValues(array $array);

    public function requireInterfacesForIndex(string $id, ...$interfaces);
}
