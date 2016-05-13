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

/*
This should probably be deprecated once Psr-11's version is available via packagist, but it does
define a set and delete method that at the moment their interface does not. So, we'll see.
https://github.com/container-interop/fig-standards/blob/master/proposed/container.md
*/
interface ContainerInterface extends \Interop\Container\ContainerInterface
{
    public function set($id, $newValue);
    public function delete($id);
}
