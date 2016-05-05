# Container

A data container that implements the PSR-11 interface, plus quite a few extra features:

* You can lock / unlock indexes. Locking an index makes it read-only, until unlocked
* You can require indexes to implement interfaces (which also requires them to be objects). Attempting to set an index to any value that is not an object that implements the required interface will throw an exception
* You can store / retrieve arrays correctly
* You can return DateTime objects and set which formats your container can try to convert from
* You can access indexes via __call. ->get will be called, and the method name will be used as the index to return
* You can register constructors instead of instances, and use the container to instantiate new objects. Parameters for the class's __construct function can be assigned in a number of ways, such as:
   * using fixed values (ex: always pass smtp.gmail.com to parameter named '$smtp_host')
   * using container values (ex: look in the container for an index named 'smtp-host' when a constructor has a parameter named '$smtp_host')
* Supports Delegate functionality proposed by the container interop: https://github.com/container-interop/container-interop/blob/master/docs/Delegate-lookup-meta.md  
* A related class is included in this project (PrefixDecorator) that can decorate an existing container so as to allow an area of your code to use the same set/get API, but have the indexes be automatically prefixed inside one master container.
* Container implements ArrayAccess, Iterator, and Countable

I wrote this class for several purposes:

* To act as a front end to $\_COOKIE, $\_SESSION, $\_REQUEST, and a config array while providing a consistent API for retrieving indexes as particular types
* To ease writing unit tests that were previously using $\_COOKIE, $\_SESSION, $\_REQUEST, etc so that they could use a mocked up container that was stored in the same location instead and use the same API.
* As a way of exploring framework approaches using a pure view of containers as  dependency injectors versus service locators (I think this container could function as either).


There are 7 functions for getting your data out:

* ->get($id), which performs no type casting at all. This is based on PSR-11's ContainerInterface (https://github.com/container-interop/container-interop/blob/master/docs/ContainerInterface.md)
* ->string(string $id, string $defaultValue), which calls strval on the data first
* ->int($id, int $defaultValue), which calls intval on the data first
* ->float($id float $defaultValue), which calls floatval on the data first
* ->bool($id, bool $defaultValue), which calls boolval on the data first
* ->DateTime($id, DateTime $defaultValue), which attempts to convert the data into a DateTime object
* ->construct($id), which constructs a new object. Note that this does NOT get a value that has been set via ->set (caveat: technically you could register a constructor as a singleton, then set the index where that class *would* be stored if it were constructed).

Here's some examples of some basic functionality:

```php
$container = new \Lucid\Component\Container\Container();

$container->set('mystring', 'my value');
echo($container->get('mystring'));    # echos with no conversion at all
echo($container->string('mystring')); # calls strval() on the data first

$container->set('myint', 1);
echo($container->string('myint')); # calls strval() on the data first, you get back '1'
echo($container->int('myint')); # calls intval on your data first, so you should get back 1


$container->set('mydate', '2010-01-01T12:00:00+00:00');
print_r($container->DateTime('mydate'));
# In this case, the function will return a DateTime object. print_r
# should print out something like this:
#
# DateTime Object
#(
#    [date] => 2010-01-01 12:00:00.000000
#    [timezone_type] => 1
#    [timezone] => +00:00
#)

```


This package also includes several related classes that may be useful in specific circumstances.

## RequestContainer

The RequestContainer class is almost identical to Container (which it extends), with two important and useful differences:

* It uses $_REQUEST as its source, rather than its own array
* It adds some functionality around the ->bool function that allows for different values to return boolean true/false.

The second part is meant to handle html checkboxes, which may submit their value in different formats depending on how the form was built. By default, if the data in the RequestContainer's index contains any of the following values, the ->bool function will return true: 'on', '1', 1, 'yes', 'true', true

Inversely, if the data in the RequestContainer's index contains any of the following values, the ->bool function will return false: '', '0', 0, 'no', 'false', false, null.

You can change the list of acceptable values for true or false by using the ->setBoolTrueValues(...$values) function, and the corresponding ->setBoolFalseValues(...$values) function. Note that both of these functions completely replace the default values, they are NOT additive.

## CookieContainer

The CookieContainer class functions identically to Container (which it extends), but uses $_COOKIE to to store its values rather than an internal array. This requires a few extra properties and setters:

* ->setExpiresOffset(int $nbrOfSeconds)
* ->setPath(string $newPath = '/')
* ->setDomain(string $newDomain = '')
* ->setSecureOnly(bool $secureOnly)
* ->setHttpOnly(bool $httpOnly)

Internally, the cookie container calls set\_cookie rather than setting indexes of an array. Each of the setters is used to set the values passed to set_cookie's parameters.  Note that the value passed to setExpiresOffset will be added to now(). The default value is 2592000 (30 days).

## SessionContainer

The SessionContainer class extends Container and uses $_SESSION to to store its values rather than an internal array. It also calls session_start(). That's really the only difference.

## Storing Arrays

Container will let you store / access arrays. So, this *should* work:

```
$container = new \Lucid\Component\Container\Container();
$container->set('myarray', []);
$container->get('myarray')['testindex'] = 'testvalue';
echo($container->get('myarray')['testindex']); # should echo 'testvalue'
```

## Using the PrefixDecorator class

The PrefixDecorator class allows you to create an object based on your container that has the same api, but automagically prefixes all indexes. One might use this to have one master container that contains all of your config options, and hand off decorated copies of the container to sub components, each of which stores their own config in the master container with prefixes that the sub container is not even aware of.

How about an example?

```php
$masterConfig = new \Lucid\Component\Container\Container();
$emailConfig = new \Lucid\Component\Container\PrefixDecorator('email/', $masterConfig);

$masterConfig->set('root-path', __DIR__);
$emailConfig->set('smtp-host', 'smtp.gmail.com');

print_r($masterConfig->getArray());
```

You *should* get something that looks like this:

```
(
  [root-path] => /var/www/myprojectorsomething
  [email/smtp-host] => smtp.gmail.com
)
```

Importantly, anything that uses the $emailConfig version doesn't have to be aware of the email: prefix. For example:

```
echo($emailConfig->string('smtp-host'));
 # This should echo smtp.gmail.com
echo($emailConfig->string('root-path','none found'));
 # this should echo 'none found'
```

But, anything that uses the $masterConfig version *can* access indexes prefixed by the decorator:

```
echo($masterConfig->string('email/smtp-host'));
 # This should echo smtp.gmail.com
echo($masterConfig->string('root-path'));
 # this should echo something like  /var/www/myproject
```


Notably, if you think of the decorators / prefixed indexes as a hierarchy, you can actually access data higher up in the hierarchy by calling ->get and using ../ in front of an index you want. So, based on our previous example:

```
echo($emailConfig->string('root-path','none found'));
 # this should echo 'none found'
echo($emailConfig->string('../root-path','none found'));
 # this should actually echo something like  /var/www/myproject!
```

## Requiring Interfaces for Indexes

You can also require the values in a particular index to implement one or more interfaces. Let's say you've defined something named MailerInterface, and you've defined a global variable named $app that is supposed to contain an index named 'mailer'. You want to make sure that anything that goes into 'mailer' implements MailerInterface so that if someone decides to replace 'mailer', all the rest of your code will still work. Here's how you go about that:

```php
$app = new \Lucid\Component\Container\Container();
$app->requireInterfacesForIndex('mailer', 'MailerInterface');
$app->set('mailer', new MyMailer());
 # assuming your class MyMailer implements MailerInterface, this should work!
 # If it doesn't implement it, get ready to catch RequiredInterfaceException.

 # Now this should NOT work as its not an object. get ready to catch RequiredInterfaceException.
$app->set('mailer', 'totally a string, not an object');
```
## Locking Indexes

You can lock indexes to prevent accidental overwriting by using the ->lock($index) and ->unlock($index) methods. For example:

```php
$app = new \Lucid\Component\Container\Container();
$app->set('admin-username', 'admin');
$app->lock('admin-username');
$app->set('admin-username', 'user1234'); # Throws a glorious exception!
$app->unlock('admin-username');
$app->set('admin-username', 'user1234'); # Will ungloriously succeed
```
Note: basically nothing prevents an index from being unlocked, so if you're really worried about malicious activity, locking the index is not going to stop some other code from changing values. This is really just there to prevent you from accidentally overwriting something.

## DateTime parsing

When trying to convert a value to a DateTime object, by default Container will call \DateTime::createFromFormat and try 3 different formats in order: \DateTime::ISO8601, \DateTime::W3C, 'U'. If any of those attempts does not fail, the result is returned. You can set which formats are tried by calling ->setDateTimeFormats(...$newFormats). Note that this replaces which formats are tested, it is NOT additive.

## Using __call()

Both Container and PrefixDecorator allow you to use __call to access indexes, but only for getting, not setting. For example:

```php
$app = new \Lucid\Component\Container\Container();
$app->set('myindex', 'myvalue');
echo($app->get('myindex'));
 # echos 'myvalue', as expected
echo($app->myindex());
 # Also echos 'myvalue'
```

Note that while PHP methods are normally case-insensitive, since the method name is used to look up the index to return, in this situation the case really does matter. Given the example code above:

```php
echo($app->myIndex());
 # Will NOT echo 'myvalue', since myIndex is not the same string as myindex.
```

## Iterating and counting

Iterating using a foreach loop works like you'd expect, but there are a couple quirks related to using the PrefixDecorator class. Only indexes that have the prefix will be used for the loop or counted. 

Example!

```php
$masterConfig = new \Lucid\Component\Container\Container();
$emailConfig = new \Lucid\Component\Container\PrefixDecorator('email/', $masterConfig);

$masterConfig->set('root-path', __DIR__);
$emailConfig->set('smpt-host', 'smtp.gmail.com');

echo('Count of $masterConfig: '.count($masterConfig));
 # should echo 2
echo('Count of $emailConfig: '.count($emailConfig));
 # should echo 1, as only one of the indexes has the right prefix
 
foreach ($masterConfig as $key=>$value) {
	echo($key.': '.$value);
}
 # this should print out two keys: root-path and email/smtp-host

foreach ($emailConfig as $key=>$value) {
	echo($key.': '.$value);
}
 # this should print out only one key: smtp-host. 

```
# Advanced Stuffs

## Using a container to construct objects 

Your container can construct any class and perform constructor-based dependency injection on the new object. Additionally, you can configure setter-based dependency injection, or manually specify the values for specific constructor parameters. 

Dependency injection can only use objects that are registered with the container.

Here's a simple example:

```php

class MyClass
{
	public function __construct(\Psr\Log\LoggerInterface $logger)
	{
		$logger->debug('class created');
	}
}

$container = new Lucid\Component\Container\Container();
$container->registerConstructor('logger', 'Lucid\\Component\\BasicLogger\\BasicLogger', true);
$myobject = $container->construct('MyClass');
```

In this example, we setup a class that depends on a Psr-3 logger as a constructor parameter. We register a class that implements the Psr-3 logging interface, and then construct the first class. The dependency is automatically detected and injected.

Registering constructors for injection is dona via ->registerConstructor(string $id, string $className, bool $isSingleton = false) method. Once registered, you can instantiate the class using either ->construct($id), or ->get($id). If when registering the constructor, you pass True for the $isSingleton parameter, then an object is only instantiated the first time you call ->construct($id) or ->get($id), and that instance is stored in the container. Future calls to ->construct($id) or ->get($id) will return the original instance.

### __construct parameters

The values passed as parameters to a class's __construct function may be set by using the ->addParameter(string $id, string $type, string $name, $value=null) function. The $type parameter may have two values:

* 'fixed', in which case a parameter with the same variable name as $name will be passed whatever is in $value. 
* 'container', in which case the value for a parameter with the same variable name as $name will be passed whatever is returned by calling ->get() on the container, and $value will be used for the index to retrieve.

Here's an example:

```php

class MyClass
{
	public function __construct(string $property1, string $property2)
	{
		$this->property1 = $property1;
		$this->property2 = $property2;
	}
}

$container = new \Lucid\Component\Container\Container();
$container->registerConstructor('myclass', 'MyClass');
$container->addParameter('myclass', 'fixed', 'property1', 'Value for property 1');
$container->addParameter('myclass', 'container', 'property2', 'property2value');
$container->set('property2value', 'Value for property2');

$object = $container->get('myclass');
echo($object->property1); # this should echo 'Value for property 1'
echo($object->property2); # this should echo 'Value for property 2'
```

### Constructor Dependency Injection

During construction, the container does try to do a bit of autowiring for any parameters whose value was not explicitly set via ->addParameter. By default, if the parameter's type is scalar and the container has a value with the same index as the parameter's name, the container's value at that index will be used.

If the parameter is NOT scalar, the container will attempt to match an index inside itself based on class name or interface name. Notably, it will also check any registered constructors to see what classes they will create and what interfaces those classes support. If necessary, an object will be instantiated.

This has a number of pluses and minuses:

* Plus: In theory, this can perform all of your dependency injection automatically for you as long as you propertly define the right classes for all of your constructor's parameters.
* Minus: Much of the work is hidden away, and it may be difficult for you to debug where a particular dependency came from
* Minus: Because you can in theory set multiple indexes to different objects that all support the same interfaces or are from the same class, you can't guarantee that a particular one will be used to resolve a constructor parameter dependency. 

Here's an example of this functionality working in practice using a logger:

```php

class MyLogger implements \PSR\Logger\LoggerInterface
{
	/* Please just pretend this is a complete psr-3 logger*/
	public function debug(string $message)
	{
		echo($message);
	}
}

class MyClass
{
	public function __construct(\PSR\Logger\LoggerInterface $logger)
	{
		$this->logger = $logger;
	}
}

$container = new \Lucid\Component\Container\Container();
$container->registerConstructor('mylogger', 'MyLogger', true);
$container->registerConstructor('myclass', 'MyClass');
$myclass = $container->get('myclass');
$myclass->logger->debug('hi'); # should echo hi'
```

So in this example, when the container prepares to instantiate MyClass, it sees that its __construct method has a parameter requiring the interface \PSR\Logger\LoggerInterface. The container looks through existing indices and constructors, and finds that MyLogger implements the necessary interface. MyLogger is instantiated, and passed to the new instance of MyClass via its __construct parameter. 



### Setter Dependency Injection

You can use Container to implement setter injection as well, though it's a more manual process than using constructor injection. Container provides the method ->    ->addInstantiationClosure(string $id, callable $closure), which allows you to perform actions on an object before it is returned from ->get() or ->construct. In this closure, you can call as many setters as you want. Here's an example:

```php

class MyMailer
{
	public function setSmtpHost($newHost) 
	{
		$this->host = $newHost;
	}
}


$container = new \Lucid\Component\Container\Container();
$container->registerConstructor('mailer', 'MyMailer');
$container->addInstantiationClosure('mailer', function($object, $container) {
	$object->setSmtpHost('smtp.gmail.com');
});
```

In this example, whenever ->get('mailer') is called, a new class of MyMailer will be instantiated and the closure will be called, which in turn calls the new object's ->setSmtpHost function. Voila, setter injection.

## Delegate containers

This functionality is based on the DelegateInterface portion of the container interop project, which is documented here: https://github.com/container-interop/container-interop/blob/master/docs/Delegate-lookup-meta.md

This functionality is implemented using two functions: 
* ->setAsParentContainerOf($container)
* ->setAsChildContainerOf($container)

You can create the relationship between the parent (or composite) container and the child from either the parent or the child by calling the appropriate function, and you do not need to call both functions. In this implementation, creating a delegate relationship between two containers only affects using a container to construct an object and locating parameter values for the object's constructor. In this case, any container in the hierarchy will first find the root container, and from the root container search for a suitable value for the constructor's parameter. A value may be a match by being one of the following (in order):

* The parameter's type is scalar, and the container contains an index with the same name as the parameter.
* The parameter's type is an object, and the container contains one of the following:
   * An instantiated object whose class is the same as the type of the parameter
   * An instantiated object that implements an interface that is the same type as the parameter
   * A registered constructor for a class that is the same type as the parameter (in which case, a new object will be instantiated and used as the constructor's parameter)
   * A registered constructor for a class that implements an interface that is the same type as the parameter  (in which case, a new object will be instantiated and used as the constructor's parameter)

## Executing Methods (now with Dependency Injection!)

This class also provides a way to instantiate an object and immediately call a method and handling dependency injection via reflection for both the constructor and the method called. This is done via ->execute(string $id, string $method). 

Example:

```php
class MyLogger implements \PSR\Logger\LoggerInterface
{
}

class MyClass
{
	public function myMethod(\PSR\Logger\LoggerInterface $logger)
	{
		$logger->debug('logging something here');
		return 'hi';
	}
}

$container = new \Lucid\Component\Container\Container();
$container->registerConstructor('logger', 'MyLogger');
$container->registerConstructor('myclass', 'MyClass');

echo($container->execute('myclass', 'myMethod')); # should echo 'hi'
```

In this example, the container will instantiate MyClass, and then use reflection to look at myMethod and determine its parameters. Notably parameters can be matched in 3 different ways:

* By name (in which case $logger matches in the index 'logger'), 
* By class
* By interface

Both name and interface would've worked here.



## Exception Classes

6 Exception classes are provided:

* NotFoundException: thrown when ->get is called, but the index cannot be found in the container. Notably, this exception will NOT be thrown when using one of the scalar getters or ->DateTime, as those functions have a second parameter $defaultValue, which is returned if the index is not set.
* DateTimeParseException: thrown when data cannot be parsed by \DateTime::createFromFormat
* InvalidSourceException: thrown when ->setSource($newSource) is called, but the new source is not an array, nor an object that supports both the ArrayAccess and Traversable interfaces
* LockedIndexEception: thrown when trying to set a locked index
* RequiredInterfaceException: thrown when an index requires an object that implements one more more interfaces, and you attempt to set that index to an object that does not implement those interfaces
* RequestInvalidBoolException: thrown only by the RequestContainer class when calling ->bool and the value in the index does not match either a valid true or valid false value. 