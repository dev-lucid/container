<?php
/*
 * This file is part of the Lucid Container package.
 *
 * (c) Mike Thorn <mthorn@devlucid.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Lucid\Container\Constructor;

class Constructor implements ConstructorInterface
{
    protected $id;
    protected $type;
    protected $isSingleton;
    protected $closure;
    protected $parameters = [];
    protected $postInstantiationClosures = [];
    protected $constructorParameterInjector;

    public function __construct(string $id, string $type = null, bool $isSingleton = false, callable $closure = null)
    {
        $this->id          = $id;
        $this->type        = (is_null($type) === true)?$id:$type;
        $this->isSingleton = $isSingleton;
        $this->closure     = $closure;
    }

    public function addParameter(Parameter\ParameterInterface $newParameter)
    {
        $this->parameters[] = $newParameter;
    }

    public function addPostInstantiationClosure(callable $newClosure)
    {
        $this->postInstantiationClosures[] = $newClosure;
    }

    public function getKey() : string
    {
        return (($this->id == '')?$this->type:$this->id);
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function isSingleton() : bool
    {
         return $this->isSingleton;
    }

    public function setConstructorParameterInjector(Parameter\ParameterInjectorInterface $constructorParameterInjector)
    {
        $this->constructorParameterInjector = $constructorParameterInjector;
    }

    public function copyFromPrefix(string $id = '', string $type = null)
    {
        $finalClass = str_replace($this->id, $this->type, $id);
        $newConstructor = new Constructor($id, $finalClass, $this->isSingleton, $this->closure);
        return $newConstructor;
    }

    public function canConstructByIdAndType(string $idToCheck = null, string $typeToCheck = null) : bool
    {
        return (($this->id == $idToCheck || is_null($idToCheck) === true) && ($this->type == $typeToCheck || is_null($typeToCheck) === true));
    }

    public function canConstructByClass(string $classToCheck) : bool
    {
        return ($this->type == $classToCheck);
    }

    public function canConstructByClassPrefix(string $id, string $classToCheck) : bool
    {
        #echo("checking class prefix. id=$id,type=$classToCheck, thisid=".$this->id.",thistype=".$this->type."\n");
        return (strpos($id, $this->id) === 0);
    }

    public function canConstructByInterface(string $interfaceToCheck) : bool
    {
        #echo('checking construct by interface:'.$interfaceToCheck.', '. print_r(class_implements($this->type), true)."\n");
        return (class_exists($this->type) === true && in_array($interfaceToCheck, class_implements($this->type)) === true);
    }

    public function construct(array $values = [])
    {
        foreach ($this->parameters as $parameter) {
            $values[$parameter->getName()] = $parameter->getValue($this->constructorParameterInjector);
        }

        if (is_callable($this->closure) === true) {
            $closure  = $this->closure;
            $function = new \ReflectionFunction($closure);
            $finalParameters = $this->constructorParameterInjector->determineParameterValues($function, $values);
            $object = $closure(...$finalParameters);
        } else {
            if (method_exists($this->type, '__construct') === true) {
                $function = new \ReflectionMethod($this->type, '__construct');
                $finalParameters = $this->constructorParameterInjector->determineParameterValues($function, $values);
                $class = $this->type;
                $object = new $class(...$finalParameters);
            } else {
                $class = $this->type;
                $object = new $class();
            }
        }

        foreach ($this->postInstantiationClosures as $closure) {
            $parameters = $this->constructorParameterInjector->determineParameterValues(
                new \ReflectionFunction($closure),
                [$object, $this->constructorParameterInjector]
            );
            $closure(...$parameters);
        }

        return $object;
    }
}