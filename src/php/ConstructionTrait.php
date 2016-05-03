<?php
namespace Lucid\Component\Container;

trait ConstructionTrait
{
    protected $constructors = [];

    public function findConstructor(string $id)
    {
        $has = false;

        if (isset($this->constructors[$id]) === true) {
            $has = $this->constructors[$id];
        }

        if ($has === false) {
            foreach ($this->constructors as $constructorId=>$constructor) {
                if (strpos($id, $constructorId) === 0) {
                    $this->constructors[$id] = [
                        'className' => $constructor['className']. substr($id, strlen($constructorId)),
                        'isSingleton' => &$constructor['isSingleton'],
                        'parameters' => &$constructor['parameters'],
                        'instantiationClosures' => &$constructor['instantiationClosures'],
                    ];
                    $has = $this->constructors[$id];
                }
            }
        }

        return $has;
    }

    public function registerConstructor(string $id, string $className, bool $isSingleton = false)
    {
        $this->constructors[$id] = [
            'className'=>$className,
            'isSingleton'=>$isSingleton,
            'parameters'=>[],
            'instantiationClosures' =>[],
        ];
        return $this;
    }

    public function addParameter(string $id, string $type, string $name, string $value = null)
    {
        if ($type != 'container' && $type != 'fixed') {
            throw new \Exception('Container->addParameter parameter $type may only contain values \'container\' or \'fixed\'');
        }
        $this->constructors[$id]['parameters'][$name] = [
            'type'=>$type,
            'value'=>$value,
        ];
        return $this;
    }

    public function addInstantiationClosure(string $id, callable $closure)
    {
        $this->constructors[$id]['instantiationClosures'][] = $closure;
        return $this;
    }

    public function findContainerForDelegateParameter(string $name, string $type, bool $isScalar, string $scalarContainerId='')
    {
        #echo("searching for delegate parameter: name=$name,type=$type,isScalar=$isScalar, scalarContainerId=$scalarContainerId\n");
        if ($isScalar === false) {
            # first, look through source for a matching class
            foreach($this->source as $id => $value) {
                if (is_object($value) === true && get_class($value) == $type) {
                    return $this;
                }
            }

            # if we didn't find a match, look through source for an object whose class implements $name as an interface
            foreach($this->source as $id => $value) {
                if (is_object($value) === true && in_array($type, class_implements($value)) === true) {
                    return $this;
                }
            }

            # first, check the constructors
            foreach($this->constructors as $id => $constructor) {
                if (class_exists($constructor['className']) === true) {
                    if ($constructor['className'] == $type) {
                        return $this;
                    }
                }
            }

            # if we didn't find a match, look through constructors for a class that implements $name as an I18nInterface
            foreach($this->constructors as $id => $constructor) {
                if (class_exists($constructor['className']) === true) {
                    $implements = class_implements($constructor['className']);

                    if (in_array($type, $implements) === true) {
                        return $this;
                    }
                }
            }
        } elseif (isset($this->source[$scalarContainerId]) === true) {
            return $this;
        } elseif (isset($this->source[$name]) === true) {
            return $this;
        }

        foreach ($this->children as $child) {
            $gotIt = $child->findContainerForDelegateParameter($name, $type, $isScalar, $scalarContainerId);
            if ($gotIt !== false) {
                return $gotIt;
            }
        }
        return false;
    }

    public function buildDelegateParameter(string $name, string $type, bool $isScalar, string $scalarContainerId='')
    {
        if ($isScalar === false) {
            # first, look through source for an object with the same class
            foreach($this->source as $id => $value) {
                if (is_object($value) === true && get_class($value) == $type) {
                    return $value;
                }
            }

            # if we didn't find a match, look through source for an object whose class implements $name as an interface
            foreach($this->source as $id => $value) {
                if (is_object($value) === true && in_array($type, class_implements($value)) === true) {
                    return $value;
                }
            }

            # next, check the constructors
            foreach($this->constructors as $id => $constructor) {
                if (class_exists($constructor['className']) === true) {
                    if ($constructor['className'] == $type) {
                        return $this->construct($id);
                    }
                }
            }

            # if we didn't find a match, look through constructors for a class that implements $name as an I18nInterface
            foreach($this->constructors as $id => $constructor) {
                if (class_exists($constructor['className']) === true) {
                    $implements = class_implements($constructor['className']);
                    if (in_array($type, $implements) === true) {
                        return $this->construct($id);
                    }
                }
            }


        } elseif (isset($this->source[$scalarContainerId]) === true) {
            return $this->get($scalarContainerId);
        } elseif (isset($this->source[$name]) === true) {
            return $this->get($name);
        }
    }
    
    public function buildInjectableParameters(array $reflectionParameters, array $configuredParameters) : array
    {
        $parameters = [];
        foreach ($reflectionParameters as $reflectionParameter) {
            $found = false;

            $name     = $reflectionParameter->getName();
            $type     = $reflectionParameter->getType();
            $isScalar = $type->isBuiltin();

            if (isset($configuredParameters[$name]) === true) {
                if ($configuredParameters[$name]['type'] == 'fixed') {
                    $parameters[$reflectionParameter->getPosition()] = $configuredParameters[$name]['value'];
                    $found = true;
                } elseif ($configuredParameters[$name]['type'] == 'container') {
                    $scalarContainerId = $configuredParameters[$name]['value'];
                    $container = $this->findRootContainer()->findContainerForDelegateParameter($name, $type, $isScalar, $scalarContainerId);
                    if ($container !== false) {
                        $parameters[$reflectionParameter->getPosition()] = $container->buildDelegateParameter($name, $type, $isScalar, $scalarContainerId);
                        $found = true;
                    }
                }
            } else {
        
                #echo("trying to find a container for parameter, name=$name, type=$type, isScalar=$isScalar\n");
                $container = $this->findRootContainer()->findContainerForDelegateParameter($name, $type, $isScalar);
                if ($container !== false) {
                    #echo("Found a container for parameter, name=$name, type=$type, isScalar=$isScalar\n");
                    $parameters[$reflectionParameter->getPosition()] = $container->buildDelegateParameter($name, $type, $isScalar);
                    $found = true;
                }
            }

            if ($found === false) {
                if ($reflectionParameter->isDefaultValueAvailable() === true) {
                    $parameters[$reflectionParameter->getPosition()] = $reflectionParameter->getDefaultValue();
                } else {
                    $parameters[$reflectionParameter->getPosition()] = null;
                }
            }
        }
        return $parameters;
    }

    public function construct(string $id, array $constructor=null)
    {
        if (is_null($constructor) === true) {
            $constructor = $this->findConstructor($id);
        }

        if ($constructor === false) {
            throw new NotFoundException($id, array_keys($this->constructors));
        }

        if ($constructor['isSingleton'] === true && isset($this->source[$id]) === true) {
            return $this->source[$id];
        }

        $class      = $constructor['className'];
        if (method_exists($class, '__construct') === true) {
            $parameters = $this->buildInjectableParameters((new \ReflectionMethod($class, '__construct'))->getParameters(), $constructor['parameters']);
        } else {
            $parameters = [];
        }

        $object = new $class(...$parameters);
        foreach ($constructor['instantiationClosures'] as $closure) {
            $closure($object, $this);
        }

        if ($constructor['isSingleton'] === true) {
            $this->source[$id] = $object;
        }

        return $object;
    }
    
    public function execute(string $id, string $method) 
    {
        $object = $this->get($id);
        if (is_object($object) === false) {
            throw new \Exception("Tried to execute a method on container index $id, but that index did not contain an object or a constructor");
        }
        
        $parameters = $this->buildInjectableParameters((new \ReflectionMethod($object, $method))->getParameters(), []);
        
        return $object->$method(...$parameters);
    }
}