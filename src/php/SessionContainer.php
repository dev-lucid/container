<?php
namespace Lucid\Component\Container;

class SessionContainer extends Container
{
    public function __construct()
    {
        session_start();
        $this->source =& $_SESSION;
    }
}