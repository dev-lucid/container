<?php
namespace Lucid\Container;

class RequestContainer extends Container
{
    public function __construct()
    {
        $this->source =& $_REQUEST;
        $this->setBoolTrueValues('on', '1', 1, 'yes', 'true', true);
        $this->setBoolFalseValues('', '0', 0, 'no', 'false', false, null);
    }
}
