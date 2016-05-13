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

class CookieContainer extends Container
{
    protected $expiresOffset = 2592000;
    protected $path          = '/';
    protected $domain        = '';
    protected $secureOnly    = false;
    protected $httpOnly      = false;

    public function __construct()
    {
        $this->source =& $_COOKIE;
    }

    public function setExpiresOffset(int $newValue) : ContainerInterface
    {
        $this->expiresOffset = time() + $newValue;
        return $this;
    }

    public function setPath(string $path = '/') : ContainerInterface
    {
        $this->path = $path;
        return $this;
    }

    public function setDomain(string $domain='') : ContainerInterface
    {
        $this->domain = $domain;
        return $this;
    }

    public function setSecureOnly(bool $secureOnly) : ContainerInterface
    {
        $this->secureOnly = $secureOnly;
        return $this;
    }

    public function setHttpOnly(bool $httpOnly) : ContainerInterface
    {
        $this->httpOnly = $httpOnly;
        return $this;
    }

    public function delete($id) : ContainerInterface
    {
        unset($this->source[$id]);
        setcookie($id, '', time() - 3600, $this->path, $this->domain, $this->secureOnly, $this->httpOnly);
        return $this;
    }

    public function set($id, $newValue) : ContainerInterface
    {
        setcookie($id, $newValue, (time() + $this->expiresOffset), $this->path, $this->domain, $this->secureOnly, $this->httpOnly);
        return $this;
    }
}
