<?php
namespace Lucid\Container;

interface DelegateInterface
{
    public function hasParent();
    public function getParent();
    public function findRootContainer();
    public function setAsChildContainerOf(DelegateInterface $parentContainer);
    public function setAsParentContainerOf(DelegateInterface $childContainer);
    public function setParentContainer(DelegateInterface $parentContainer);
    public function addChildContainer(DelegateInterface $childContainer);
}
