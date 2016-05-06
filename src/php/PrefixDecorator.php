<?php
namespace Lucid\Container;

class PrefixDecorator implements \Interop\Container\ContainerInterface, ContainerInterface, \ArrayAccess, \Iterator, \Countable
{
    protected $prefix    = null;
    protected $container = null;
    protected $iteratorTempArray = null;

    protected $methodsWithIdParameter = [
        'construct'=>true,
        'bool'=>true,
        'int'=>true,
        'float'=>true,
        'DateTime'=>true,
        'lock'=>true,
        'unlock'=>true,
        'setSource'=>false,
        'setDateTimeFormats'=>false,
    ];
    protected $parameterNamesToPrefix = ['id', 'name', 'index'];

    function __construct(string $prefix = '', \Interop\Container\ContainerInterface $container)
    {
        $this->prefix = $prefix;
        $this->container = $container;
    }

    public function getContainer() : ContainerInterface
    {
        return $this->container;
    }

    public function &__call(string $method, array $parameters)
    {
        if (isset($this->methodsWithIdParameter[$method]) === false) {

            # first, find the actual container by repeatedly calling->getContainer until the object returnd no longer
            # has this method. This is to account for the possibility of multiple decorators
            $actualContainer = $this->container;
            while(method_exists($actualContainer, 'getContainer') === true) {
                $actualContainer = $actualContainer->getContainer();
            }

            # then look up the method's parametres
            if (method_exists($actualContainer, $method) === false) {
                # attempting to call a method that doesn't exist on the container. Likely trying to use __call method of accessing
                # indexes.
                $result =& $this->get($method);
                return $result;
            } else {
                $ref = new \ReflectionMethod($actualContainer, $method);
                $methodParameters = $ref->getParameters();

                # if the method has more than one parameter and the name of the first parameter is on the list of parameter
                # names that most likely need a prefix (id, name, index), then set to true
                if (count($methodParameters) === 0) {
                    $this->methodsWithIdParameter[$method] = false;
                } else {
                    $this->methodsWithIdParameter[$method] = in_array($methodParameters[0]->name, $this->parameterNamesToPrefix);
                }
            }
        }

        # if this method needs its first parameter prefixed, do so.
        if ($this->methodsWithIdParameter[$method] === true) {
            $parameters[0] = $this->buildFinalId($parameters[0]);
        }

        # Call the method on our actual container, and move on.
        $result = $this->container->$method(...$parameters);
        return $result;
    }

    protected function buildFinalId(string $id) : string
    {
        $finalId = null;
        if (strpos($id, '../') === 0) {
            return substr($id, 3);
        } else {
            return $this->prefix . $id;
        }
    }

    public function has($id) : bool
    {
        return $this->container->has($this->buildFinalId($id));
    }

    public function &get($id)
    {
        if ($this->has($id) === false) {
            throw new Exception\NotFoundException($id, array_keys($this->array()));
        }
        $value =& $this->container->get($this->buildFinalId($id));
        return $value;
    }

    public function set($id, $newValue) : ContainerInterface
    {
        return $this->container->set($this->buildFinalId($id), $newValue);
    }

    public function delete($id) : ContainerInterface
    {
        return $this->container->delete($this->buildFinalId($id));
    }

    public function setValues(array $array) : ContainerInterface
    {
        foreach ($array as $key=>$value) {
            $this->set($this->prefix . $key, $value);
        }
        return $this;
    }

    /* ArrayAccess methods: start */
    public function offsetExists($id) : bool
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
        return $this->set($id, $newValue);
    }

    public function offsetUnset($id)
    {
        return $this->delete($id);
    }
    /* ArrayAccess methods: end */


    protected function setupIteratorTempArray()
    {
        $this->iteratorTempArray = [];
        foreach ($this->container as $id=>$value) {
            if (strpos($id, $this->prefix) === 0) {
                $this->iteratorTempArray[substr($id, strlen($this->prefix))] = $value;
            }
        }
    }

    /* Iterator methods: start */
    function rewind() {
        $this->setupIteratorTempArray();
        reset($this->iteratorTempArray);
    }

    function current() {
        return current($this->iteratorTempArray);
    }

    function key() {
        return key($this->iteratorTempArray);
    }

    function next() {
        next($this->iteratorTempArray);
    }

    function valid() {
        return key($this->iteratorTempArray) !== null;
    }
    /* Iterator methods: end */

    /* Countable methods: start */
    function count() : int
    {
        $this->setupIteratorTempArray();
        return count(array_keys($this->iteratorTempArray));
    }
    /* Countable methods: end */
}
