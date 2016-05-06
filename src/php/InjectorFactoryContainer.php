<?php
namespace Lucid\Container;

class InjectorFactoryContainer extends Container implements InjectorFactoryInterface
{
    protected $constructors = [];

    protected function getFromAdditionalSources(string $id)
    {
        $constructor = $this->findConstructor($id);
        if ($constructor !== false) {
            $object = $this->construct($id, $constructor);
            return $object;
        }
        return false;
    }

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
                        'className' =>   $constructor['className']. substr($id, strlen($constructorId)),
                        'closure'=>      &$constructor['closure'],
                        'isSingleton' => &$constructor['isSingleton'],
                        'parameters' =>  &$constructor['parameters'],
                        'instantiationClosures' => &$constructor['instantiationClosures'],
                    ];
                    $this->addParameter($id, 'fixed', 'name', substr($id, strlen($constructorId)));
                    $has = $this->constructors[$id];
                }
            }
        }

        return $has;
    }

    public function registerConstructor(string $id, string $className = '', bool $isSingleton = false, callable $closure = null)
    {
        if (is_callable($closure) === true) {
            $this->constructors[$id] = [
                'className'=>$className,
                'closure'=>$closure,
                'isSingleton'=>$isSingleton,
                'parameters'=>[],
                'instantiationClosures' =>[],
            ];
        } else {
            $this->constructors[$id] = [
                'className'=>$className,
                'closure'=>$closure,
                'isSingleton'=>$isSingleton,
                'parameters'=>[],
                'instantiationClosures' =>[],
            ];
        }
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
        /* Order to check things:
         *
         * Exact match in ->source by name, and type is scalar OR the object in ->source is the same class as $type / same interface as $type
         * Exact match in ->source by scalarContainerId, and type is scalar OR the object in ->source is the same class as $type / same interface as $type
         * Exact match in ->constructors by name, and class name is same class as $type / same interface as $type
         * Match by class in source
         * Match by Interface in source
         * Match by class in constructors
         * Match by Interface in constructors
        */
        # echo("searching for delegate parameter: name=$name,type=$type,isScalar=$isScalar, scalarContainerId=$scalarContainerId\n");
        if (isset($this->source[$name]) === true) {
            if ($isScalar === true) {
                return $this;
            } else {
                if (is_object($this->source[$name]) === true) {
                    if (get_class($this->source[$name]) == $type) {
                        return $this;
                    }
                    if (in_array($type, class_implements($this->source[$name])) === true) {
                        return $this;
                    }
                }
            }
        }

        if (isset($this->source[$scalarContainerId]) === true) {
            if ($isScalar === true) {
                return $this;
            } else {
                if (is_object($this->source[$scalarContainerId]) === true) {
                    if (get_class($this->source[$scalarContainerId]) == $type) {
                        return $this;
                    }
                    if (in_array($type, class_implements($this->source[$scalarContainerId])) === true) {
                        return $this;
                    }
                }
            }
        }

        if (isset($this->constructors[$name]) === true) {
            if (is_callable($this->constructors[$name]['closure']) === true) {
                return $this;
            } elseif ($isScalar === false) {
                if ($this->constructors[$name]['className'] == $type) {
                    return $this;
                }
                if (in_array($type, class_implements($this->constructors[$name]['className'])) === true) {
                    return $this;
                }
            }
        }

        # at this point, the only options left are objects/constructors, so if we've been
        # looking for a scalar, return false now.
        if ($isScalar === false) {
            foreach($this->source as $id => $value) {
                if (is_object($value) === true) {
                    if (get_class($value) == $type) {
                        return $this;
                    }
                    if (in_array($type, class_implements($value)) === true) {
                        return $this;
                    }
                }
            }

            foreach($this->constructors as $id => $constructor) {
                if (class_exists($constructor['className']) === true) {
                    if ($constructor['className'] == $type) {
                        return $this;
                    }
                    if (in_array($type, class_implements($constructor['className'])) === true) {
                        return $this;
                    }
                }
            }
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
        /* Order to check things:
         *
         * Exact match in ->source by name, and type is scalar OR the object in ->source is the same class as $type / same interface as $type
         * Exact match in ->source by scalarContainerId, and type is scalar OR the object in ->source is the same class as $type / same interface as $type
         * Exact match in ->constructors by name, and class name is same class as $type / same interface as $type
         * Match by class in source
         * Match by Interface in source
         * Match by class in constructors
         * Match by Interface in constructors
        */
        # echo("searching for delegate parameter: name=$name,type=$type,isScalar=$isScalar, scalarContainerId=$scalarContainerId\n");
        if (isset($this->source[$name]) === true) {
            if ($isScalar === true) {
                return $this->source[$name];
            } else {
                if (is_object($this->source[$name]) === true) {
                    if (get_class($this->source[$name]) == $type) {
                        return $this->source[$name];
                    }
                    if (in_array($type, class_implements($this->source[$name])) === true) {
                        return $this->source[$name];
                    }
                }
            }
        }

        if (isset($this->source[$scalarContainerId]) === true) {
            if ($isScalar === true) {
                return $this->source[$scalarContainerId];
            } else {
                if (is_object($this->source[$scalarContainerId]) === true) {
                    if (get_class($this->source[$scalarContainerId]) == $type) {
                        return $this->source[$scalarContainerId];
                    }
                    if (in_array($type, class_implements($this->source[$scalarContainerId])) === true) {
                        return $this->source[$scalarContainerId];
                    }
                }
            }
        }

        if (isset($this->constructors[$name]) === true) {
            if (is_callable($this->constructors[$name]['closure']) === true) {
                return $this->construct($name);
            } elseif ($isScalar === false) {
                if ($this->constructors[$name]['className'] == $type) {
                    return $this->construct($name);
                }
                if (in_array($type, class_implements($this->constructors[$name]['className'])) === true) {
                    return $this->construct($name);
                }
            }
        }

        # at this point, the only options left are objects/constructors, so if we've been
        # looking for a scalar, return false now.
        if ($isScalar === false) {
            foreach($this->source as $id => $value) {
                if (is_object($value) === true) {
                    if (get_class($value) == $type) {
                        return $value;
                    }
                    if (in_array($type, class_implements($value)) == true) {
                        return $value;
                    }
                }
            }

            foreach($this->constructors as $id => $constructor) {
                if (class_exists($constructor['className']) === true) {
                    if ($constructor['className'] == $type) {
                        return $this->construct($id);
                    }

                    if (in_array($type, class_implements($constructor['className'])) == true) {
                        return $this->construct($id);
                    }
                }
            }
        }

        return false;
    }

    public function buildInjectableParameters(array $reflectionParameters, array $configuredParameters) : array
    {
        $parameters = [];
        foreach ($reflectionParameters as $reflectionParameter) {
            $found = false;

            $name     = $reflectionParameter->getName();
            $type     = $reflectionParameter->getType();
            if (is_null($type) === true) {
                $type = 'string';
                $isScalar = true;
            } else {
                $isScalar = $type->isBuiltin();
            }

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
            if (class_exists($id) === true) {
                $this->registerConstructor($id, $id);
                $constructor = $this->constructors[$id];
            } else {
                throw new Exception\NotFoundException($id, array_keys($this->constructors));
            }
        }

        if ($constructor['isSingleton'] === true && isset($this->source[$id]) === true) {
            return $this->source[$id];
        }

        if (is_callable($constructor['closure']) === true) {

            $parameters = $this->buildInjectableParameters((new \ReflectionFunction($constructor['closure']))->getParameters(), $constructor['parameters']);
            $object = $constructor['closure'](...$parameters);
        } else {
            $class = $constructor['className'];
            if (method_exists($class, '__construct') === true) {
                $parameters = $this->buildInjectableParameters((new \ReflectionMethod($class, '__construct'))->getParameters(), $constructor['parameters']);
            } else {
                $parameters = [];
            }

            $object = new $class(...$parameters);
        }

        foreach ($constructor['instantiationClosures'] as $closure) {
            $closure($object, $this);
        }

        if ($constructor['isSingleton'] === true) {
            $this->source[$id] = $object;
        }

        return $object;
    }

    public function execute($idOrObject, string $method)
    {
        if (is_object($idOrObject) === true) {
            $object = $idOrObject;
        } else {
            if ($this->has($idOrObject) === true) {
                $object = $this->get($idOrObject);
            } else {
                $object = $this->construct($idOrObject);
            }
            if (is_object($object) === false) {
                throw new \Exception("Tried to execute a method on container index $idOrObject, but that index did not contain an object or a constructor");
            }
        }

        $parameters = $this->buildInjectableParameters((new \ReflectionMethod($object, $method))->getParameters(), []);

        return $object->$method(...$parameters);
    }
}
