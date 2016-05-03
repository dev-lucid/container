<?php
namespace Lucid\Component\Container;

class Container implements ContainerInterface, \ArrayAccess, \Iterator, \Countable
{
    protected $source             = [];
    protected $dateTimeFormats    = [\DateTime::ISO8601, \DateTime::W3C, 'Y-m-d H:i', 'U'];
    protected $requiredInterfaces = [];

    protected $locks              = [];

    protected $parent             = null;
    protected $children           = [];

    protected $constructors = [];

    public function __construct()
    {
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
            throw new Exception('Container->addParameter parameter $type may only contain values \'container\' or \'fixed\'');
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

    public function requireInterfacesForIndex(string $id, ...$interfaces)
    {
        if (isset($this->requiredInterfaces[$id]) === false) {
            $this->requiredInterfaces[$id] = [];
        }
        $this->requiredInterfaces[$id] = array_merge($this->requiredInterfaces[$id], $interfaces);
        if ($this->has($id) === true) {
            $this->checkRequiredInterfaces($id);
        }
    }

    protected function checkRequiredInterfaces(string $id)
    {
        if (isset($this->requiredInterfaces[$id]) === false) {
            return;
        }

        if (is_object($this->source[$id]) === false) {
            throw new RequiredInterfaceException('Container index '.$id.' does not contain an object, but this index is required to be an object that implements the following interfaces: '.implode(', ', $this->requiredInterfaces[$id]));
            # throw new \Exception('Container index '.$id.' does not contain an object, but this index is required to be an object that implements the following interfaces: '.implode(', ', $this->requiredInterfaces[$id]));
        }

        $implements = class_implements($this->source[$id]);
        foreach ($this->requiredInterfaces[$id] as $interface) {
            if (in_array($interface, $implements) === false) {
                throw new RequiredInterfaceException('Container index '.$id.' does not implement a required interface. This index must implement the following interfaces: '.implode(', ', $this->requiredInterfaces[$id]));
                # throw new \Exception();
            }
        }
    }

    public function lock(string $id)
    {
        $this->locks[$id] = true;
        return $this;
    }

    public function unlock(string $id)
    {
        if (isset($this->locks[$id]) === true) {
            unset($this->locks[$id]);
        }
        return $this;
    }

    public function setDateTimeFormats(...$newFormats)
    {
        $this->dateTimeStringFormats = $newFormats;
        return $this;
    }

    public function setSource(&$newSource)
    {
        if (is_array($newSource) === true ) {
            $this->source =& $newSource;
        } elseif (is_object($newSource) === true) {
            $classImplements = class_implements($newSource);

            if (in_array('ArrayAccess', $classImplements) === false || in_array('Iterator', $classImplements) === false) {
                throw new InvalidSourceException();
            }
            $this->source =& $newSource;
        } else {
            throw new InvalidSourceException();
        }

        return $this;
    }

    public function has(string $id) : bool
    {

        $has = (isset($this->source[$id]) === true);


        return $has;
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

    public function &get(string $id)
    {
        if (is_null($this->parent) === false){
            if ($this->parent->has($id) === true) {
                return $this->parent->get($id);
            }
        }

        if ($this->has($id) === false) {
            foreach ($this->children as $child) {
                if ($child->has($id) === true) {
                    return $child->get($id);
                }
            }
            
            $constructor = $this->findConstructor($id);
            if ($constructor !== false) {
                $object = $this->construct($id, $constructor);
                return $object;
            }
            throw new NotFoundException($id, array_keys($this->getArray()));
        }

        $value =& $this->source[$id];
        return $value;
    }

    public function hasParent()
    {
        return (is_null($this->parent) === false);
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function findRootContainer()
    {
        $obj = $this;
        while($obj->hasParent() === true) {
            $obj = $obj->getParent();
        }
        return $obj;
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
        }
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

        $class = $constructor['className'];
        $reflectionMethod = new \ReflectionMethod($class, '__construct');
        $reflectionParameters = $reflectionMethod->getParameters();
        $parameters = [];
        foreach ($reflectionParameters as $reflectionParameter) {
            $found = false;
            
            $name     = $reflectionParameter->getName();
            $type     = $reflectionParameter->getType();
            $isScalar = $type->isBuiltin();

            if (isset($constructor['parameters'][$name]) === true) {
                if ($constructor['parameters'][$name]['type'] == 'fixed') {
                    $parameters[$reflectionParameter->getPosition()] = $constructor['parameters'][$name]['value'];
                    $found = true;
                } elseif ($constructor['parameters'][$name]['type'] == 'container') { 
                    $scalarContainerId = $constructor['parameters'][$name]['value'];
                    $container = $this->findRootContainer()->findContainerForDelegateParameter($name, $type, $isScalar, $scalarContainerId);
                    if ($container !== false) {
                        $parameters[$reflectionParameter->getPosition()] = $container->buildDelegateParameter($name, $type, $isScalar, $scalarContainerId);
                        $found = true;
                    }
                }
            } else {
                $container = $this->findRootContainer()->findContainerForDelegateParameter($name, $type, $isScalar);
                if ($container !== false) {
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
        
        #echo('ready to construct '.$id.', parameters=='.print_r($parameters, true)."\n");
        
        $object = new $class(...$parameters);
        foreach ($constructor['instantiationClosures'] as $closure) {
            $closure($object, $this);
        }
        
        if ($constructor['isSingleton'] === true) {
            $this->source[$id] = $object;
        }

        return $object;
    }

    public function delete(string $id)
    {
        unset($this->source[$id]);
        return $this;
    }

    public function set(string $id, $newValue)
    {
        if (isset($this->locks[$id]) === true) {
            throw new LockedIndexException();
        }
        $this->source[$id] =& $newValue;
        $this->checkRequiredInterfaces($id);
        return $this;
    }

    public function string(string $id, string $defaultValue = '') : string
    {
        if ($this->has($id) === false) {
            return $defaultValue;
        }
        $value = $this->get($id);
        return strval($value);
    }

    public function int(string $id, int $defaultValue = -1) : int
    {
        if ($this->has($id) === false) {
            return $defaultValue;
        }
        $value = $this->get($id);
        return intval($value);
    }

    public function float(string $id, float $defaultValue = -1): float
    {
        if ($this->has($id) === false) {
            return $defaultValue;
        }
        $value = $this->get($id, $defaultValue);
        return floatval($value);
    }

    public function bool(string $id, bool $defaultValue=false) : bool
    {
        if ($this->has($id) === false) {
            return $defaultValue;
        }
        $value = $this->get($id);
        return boolval($value);
    }

    public function DateTime(string $id, DateTime $defaultValue = null) : \DateTime
    {
        if ($this->has($id) === false) {
            return $defaultValue;
        }
        $val = null;
        $value = $this->get($id, $defaultValue);

        if (is_numeric($value) === true) {
            return \DateTime::createFromFormat('U', $value);
        } elseif (is_string($value) === true) {
            foreach ($this->dateTimeFormats as $format) {
                $parseResult = \DateTime::createFromFormat($format, $value);
                if ($parseResult !== false) {
                    return $parseResult;
                }
            }
        }
        throw new DateTimeParseException($value, $this->dateTimeFormats);
    }

    public function getArray() : array
    {
        if (is_array($this->source) === true) {
            return $this->source;
        } else {
            $returnArray = [];
            foreach ($this->source as $key=>$value) {
                $returnArray[$key] = $value;
            }
            return $returnArray;
        }
    }

    public function setValues(array $array)
    {
        foreach ($array as $key=>$value) {
            $this->set($key, $value);
        }
        return $this;
    }

    public function &__call($method, $parameters)
    {
        if (isset($parameters[0])) {
            $this->set($method, $parameters[0]);
            return $this;
        }
        $value =& $this->get($method);
        return $value;
    }

    /* ArrayAccess methods: start */
    public function offsetExists($id)
    {
        return $this->has($id);
    }

    public function &offsetGet($id)
    {
        $value =& $this->get($id);
        return $value;
    }

    public function offsetSet($id, $newValue)
    {
        $this->set($id, $newValue);
    }

    public function offsetUnset($id)
    {
        $this->delete($id);
    }
    /* ArrayAccess methods: end */

    /* Iterator methods: start */
    function rewind() {
        reset($this->source);
    }

    function current() {
        return current($this->source);
    }

    function key() {
        return key($this->source);
    }

    function next() {
        next($this->source);
    }

    function valid() {
        return key($this->source) !== null;
    }
    /* Iterator methods: end */

    /* Countable methods: start */
    function count()
    {
        return count(array_keys($this->getArray()));
    }
    /* Countable methods: end */

    /* Delegate functions: start */
    public function setAsChildContainerOf($parentContainer)
    {
        $this->setParentContainer($parentContainer);
        $parentContainer->addChildContainer($this);
        return $this;
    }

    public function setAsParentContainerOf($childContainer)
    {
        $this->addChildContainer($childContainer);
        $childContainer->setParentContainer($this);
        return $this;
    }

    public function setParentContainer($parentContainer)
    {
        $this->parent = $parentContainer;
    }

    public function addChildContainer($childContainer)
    {
        $this->children[] = $childContainer;
    }
    /* Delegate functions: end */
}
