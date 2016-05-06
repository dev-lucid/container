<?php
namespace Lucid\Container;

interface TypedGetterInterface
{
    public function string(string $id, string $defaultValue);
    public function int(string $id, int $defaultValue);
    public function float(string $id, float $defaultValue);
    public function bool(string $id, bool $defaultValue);
    public function DateTime(string $id, DateTime $defaultValue);
    public function array();
}