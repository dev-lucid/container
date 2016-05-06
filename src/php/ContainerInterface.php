<?php
namespace Lucid\Container;

/*
This should be deprecated once Psr-11's version is available via packagist:
https://github.com/container-interop/fig-standards/blob/master/proposed/container.md
*/

interface ContainerInterface extends \Interop\Container\ContainerInterface
{
    public function set($id, $newValue);
    public function delete($id);
}
