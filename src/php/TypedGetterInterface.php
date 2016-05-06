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

/**
 * Functions used to get data out of the container and cast them to a particular type.
 *
 * @author Mike Thorn <mthorn@devlucid.com>
 */
interface TypedGetterInterface
{
    public function string(string $id, string $defaultValue);
    public function int(string $id, int $defaultValue);
    public function float(string $id, float $defaultValue);
    public function bool(string $id, bool $defaultValue);
    public function DateTime(string $id, DateTime $defaultValue);
    public function array(string $id, array $defaultValue, string $delimiter);
}