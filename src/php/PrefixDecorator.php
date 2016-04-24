<?php
namespace Lucid\Component\Container;

class PrefixDecorator
{
    protected $prefix    = null;
    protected $container = null;

    protected $methodsWithIdParameter = [
        'set'=>true,
        'has'=>true,
        'bool'=>true,
        'int'=>true,
        'float'=>true,
        'DateTime'=>true,
        'lock'=>true,
        'unlock'=>true,
        'delete'=>true,
        'setSource'=>false,
        'setDateTimeFormats'=>false,
    ];
    protected $parameterNamesToPrefix = ['id', 'name', 'index'];

    function __construct(string $prefix = '', $container)
    {
        $this->prefix = $prefix;
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function __call(string $method, array $parameters)
    {
        if (isset($this->methodsWithIdParameter[$method]) === false) {

            # first, find the actual container by repeatedly calling->getContainer until the object returnd no longer
            # has this method. This is to account for the possibility of multiple decorators
            $actualContainer = $this->container;
            while(method_exists($actualContainer, 'getContainer') === true) {
                $actualContainer = $actualContainer->getContainer();
            }

            # then look up the method's parametres
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

        # if this method needs its first parameter prefixed, do so.
        if ($this->methodsWithIdParameter[$method] === true) {
            $parameters[0] = $this->buildFinalId($parameters[0]);
        }

        # Call the method on our actual container, and move on.
        return $this->container->$method(...$parameters);
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

    public function &get(string $id, $defaultValue = null)
    {
        $value =& $this->container->get($this->buildFinalId($id), $defaultValue = null);
        return $value;
    }

    public function setValues(array $array)
    {
        foreach ($array as $key=>$value) {
            $this->set($this->prefix . $key, $value);
        }
        return $this;
    }
}