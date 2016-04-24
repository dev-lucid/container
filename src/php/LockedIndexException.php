<?php
namespace Lucid\Component\Container;

class LockedIndexException extends \Exception
{
    protected $message = 'The index you tried to set was locked. You must unlock it first, and do consider why it was locked in the first place.';
}