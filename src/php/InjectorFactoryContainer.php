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
 * A container that can also construct objects with dependency injection.
 *
 * @author Mike Thorn <mthorn@devlucid.com>
 */
class InjectorFactoryContainer extends Container implements InjectorFactoryInterface, Constructor\Parameter\ParameterInjectorInterface
{
    protected $constructors = [];

    protected function getFromAdditionalSources(string $id)
    {
        $constructor = $this->findConstructor($id);
        if ($constructor !== false) {
            $object = $this->construct($id, null, $constructor);
            return $object;
        }
        return false;
    }

    public function findConstructor(string $id = '', string $type = null)
    {
        # we always *always* have an id
        # we do NOT always have a type
        if (class_exists($id) === true && ($type == '' || is_null($type) === true)) {
            $type = $id;
        }

        $this->_debug("Trying to construct id=$id,type=$type\n");
        if ($id != '' && isset($this->constructors[$id]) === true) {
            #echo("Checking by constructor id: $id\n");
            if ($type == '' || is_null($type) === true) {
                $this->_debug("Found a match by id = $id, but could not perform type checking because no type was specified. Returning match by id");
                # can't perform type check, so just assume this id will work
                return $this->constructors[$id];
            } else {
                $constructorType = $this->constructors[$id]->getType();
                if ($type === $constructorType){
                    $this->_debug("Found a match by id and type: $id/$type");
                    return $this->constructors[$id];
                } else if (class_exists($type) === true && in_array($type, class_parents($constructorType)) === true){
                    $this->_debug("Found a match by id=$id, Constructor for $id is class $constructorType, which inherits from $type!");
                    return $this->constructors[$id];
                } else if (interface_exists($type) === true  && in_array($type, class_implements($constructorType)) === true){
                    $this->_debug("Found a match by id=$id, and type is actually an interface. Constructor for $id is class $constructorType, which implements this interface!");
                    return $this->constructors[$id];
                } else {
                    $this->_debug("Found a match by id=$id, but type did not match. Wanted $type, found $constructorType");
                }
            }
        }


        # if we're trying to instantiate a specific class,
        # then we check if we've got a constructor for that id and type,
        # and if we don't, check to see if we've got one for that specific type
        #echo("Checking for real class: $type\n");
        foreach ($this->constructors as $constructor) {
            if ($constructor->canConstructByIdAndType($id, $type) === true) {
                $this->_debug("found a match for id=$id,type=$type by id and type\n");
                return $constructor;
            }
        }

        foreach ($this->constructors as $constructor) {
            if ($constructor->canConstructByClass((string) $type) === true) {
                $this->_debug("found a match for type=$type by class\n");
                return $constructor;
            }
        }


        # Next, check our constructors by interface
        if (interface_exists($type) === true) {
            #echo("Checking for real interface: $type\n");
            foreach ($this->constructors as $constructor) {
                if ($constructor->canConstructByInterface((string) $type) === true) {
                    $this->_debug("found a match for type=$type by interface\n");
                    return $constructor;
                }
            }
            return false;
        }

        # At this point, the 'type' doesn't represent a real class or interface, but it
        # could still be a class prefix. So, see if we have a constructor configured
        # that can construct this object by prefix.
        foreach ($this->constructors as $constructor) {
            $this->_debug("Checking for class prefix: $type\n");
            if ($constructor->canConstructByClassPrefix((string) $id, (string) $type) === true) {
                $newConstructor = $constructor->copyFromPrefix($id, $type);
                $this->addConstructor($newConstructor);
                $this->_debug("found a match for id=$id,type=$type by class prefix\n");
                # print_r($this->constructors);
                return $newConstructor;
            }
        }

        foreach ($this->children as $childContainer) {
            $constructor = $childContainer->findConstructor($id, $type);
            if ($constructor !== false) {
                return $constructor;
            }
        }

        $this->_debug("Could not find a match for $id/$type\n");
        return false;
    }

    public function addConstructor(Constructor\ConstructorInterface $newConstructor)
    {
        $newConstructor->setConstructorParameterInjector($this);
        $this->constructors[$newConstructor->getKey()] = $newConstructor;
    }

    public function construct(string $id = '', string $type = null, Constructor\ConstructorInterface $constructor = null)
    {
        $this->_debug("Constructing $id / $type. Using passed constructor: " . is_object($constructor));
        if (is_null($constructor) === true) {
            $constructor = $this->findRootContainer()->findConstructor($id, $type);
            #echo("Condition 1: /".($constructor === false)."/\n");
            #echo("Condition 2: /".(class_exists($id))."/\n");
            #echo("need to create an arbitrary constructor for id=$id,type=$type\n");
            if ($constructor === false && class_exists($id) === true) {
                $this->_debug('Could not locate constructor internally, creating new one for '.$type);
                $constructor = new Constructor\Constructor($id, $id);
                $this->addConstructor($constructor);
            }
        }

        if ($constructor->isSingleton() === true) {
            $this->_debug("$id/$type is a singleton, will store new instance as ".$constructor->getKey());
            if ($this->has($constructor->getKey()) === true) {
                return $this->get($constructor->getKey());
            }
            $object = $constructor->construct();
            $this->set($constructor->getKey(), $object);
        } else {
            $this->_debug("$id/$type is NOT a singleton, constructing a new instance");
            $object = $constructor->construct();
        }

        return $object;
    }

    public function determineParameterValue(\ReflectionParameter $parameter, Constructor\Parameter\ParameterInterface $configuredParameter = null, array $values = [])
    {
        # extract relevant info from the ReflectionParameter.
        $name     = $parameter->getName();
        $type     = ($parameter->hasType() === true)?$parameter->getType()->__toString():'string';
        $isConstructable = (class_exists($type) || interface_exists($type));
        $default  = ($parameter->isDefaultValueAvailable() === true)?$parameter->getDefaultValue():null;
        $position  = $parameter->getPosition();

        # if there's a configured parameter, use this first
        if (is_null($configuredParameter) === false) {
            return $configuredParameter->getValue($this);
        }

        # if a value was passed in by ordinal position in the values array, use this next
        if (isset($values[$position]) === true) {
            return $values[$position];
        }
        # if a value was passed in by name in the values array, use this next
        if (isset($values[$name]) === true) {
            return $values[$name];
        }

        if ($this->has($name) === true) {
            return $this->get($name);
        }

        # if this is not a constructable value and it's in the container by name, use that value
        if ($isConstructable === false && $this->has($name) === true) {
            return $this->get($name);
        }

        # if it's constructable, see if we have a constructor defined for it.
        if ($isConstructable === true) {
            $constructor = $this->findConstructor($name, $type);
            # if we still haven't found a way to construct it, but it *is* in theory
            # constructable, add a new constructor for it and hope everything works out.
            if ($constructor === false ){
                #echo("Constructing a new name=$name,type=$type\n");
                $constructor = new Constructor\Constructor($type, $type, false);
                $this->addConstructor($constructor);
            }
            
            $this->_debug("Constructing parameter value $name/$type.");
            return $this->construct($name, $type, $constructor);
        }

        return $default;
    }

    public function determineParameterValues(\ReflectionFunctionAbstract $function, array $values = []) : array
    {
        $finalValues = [];
        foreach ($function->getParameters() as $parameter) {
            $finalValues[] = $this->determineParameterValue(
                $parameter,
                ((isset($this->parameters[$parameter->getName()]) === true)?$this->parameters[$parameter->getName()]:null),
                $values
            );
        }
        return $finalValues;
    }

    public function call($idOrObjectOrClosure, string $method = '', array $parameters = [])
    {

        if (is_callable($idOrObjectOrClosure) === true) {
            $parameters = $this->determineParameterValues(new \ReflectionFunction($idOrObjectOrClosure), $parameters);
            return $idOrObjectOrClosure(...$parameters);
        } elseif (is_object($idOrObjectOrClosure) === true) {
            $object = $idOrObjectOrClosure;
        } else {
            $object = $this->construct($idOrObjectOrClosure);
        }
        $parameters = $this->determineParameterValues(new \ReflectionMethod($object, $method), $parameters);
        return $object->$method(...$parameters);
    }
}
