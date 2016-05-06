<?php
namespace Lucid\Container;

class ServerContainer extends Container
{
    public function __construct()
    {
        $this->source =& $_SERVER;
    }
}
