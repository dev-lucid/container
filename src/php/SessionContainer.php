<?php
/*
 * This file is part of the Lucid Container package.
 *
 * (c) Mike Thorn <mthorn@devlucid.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lucid\Container;

class SessionContainer extends Container
{
    public function __construct()
    {
        session_start();
        $this->source =& $_SESSION;
    }
}
