<?php
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

    public function setExpiresOffset(int $newValue)
    {
        $this->expiresOffset = time() + $newValue;
        return $this;
    }

    public function setPath(string $path = '/')
    {
        $this->path = $path;
        return $this;
    }

    public function setDomain(string $domain='')
    {
        $this->domain = $domain;
        return $this;
    }

    public function setSecureOnly(bool $secureOnly)
    {
        $this->secureOnly = $secureOnly;
        return $this;
    }

    public function setHttpOnly(bool $httpOnly)
    {
        $this->httpOnly = $httpOnly;
        return $this;
    }

    public function delete(string $id)
    {
        unset($this->source[$id]);
        setcookie($id, '', time() - 3600, $this->path, $this->domain, $this->secureOnly, $this->httpOnly);
        return $this;
    }

    public function set(string $id, $newValue)
    {
        setcookie($id, $newValue, (time() + $this->expiresOffset), $this->path, $this->domain, $this->secureOnly, $this->httpOnly);
        return $this;
    }
}
