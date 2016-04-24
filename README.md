# Container

A data container that implements the PSR-11 interface, plus a few extra features:

* You can lock / unlock indexes
* You can require indexes to implement interfaces
* You can store / retrieve arrays correctly
* You can return DateTime objects and set which formats your container can try to convert from
* You can decorate the container to allow an area of your code to use the same set/get API, but have the indexes be automatically prefixed inside one master container.

I wrote this class for several purposes:

* To act as a front end to $\_COOKIE, $\_SESSION, $\_REQUEST, and a config array while providing a consistent API for retrieving indexes as particular types
* To ease writing unit tests that were previously using $\_COOKIE, $\_SESSION, $\_REQUEST, etc so that they could use a mocked up container that was stored in the same location instead and use the same API.


There are 6 functions for getting your data out:

* ->get($id), which performs no type casting at all
* ->string($id), which calls strval on the data first
* ->int($id), which calls intval on the data first
* ->float($id), which calls floatval on the data first
* ->bool($id), which calls boolval on the data first
* ->DateTime($id), which attemps to convert the data into a DateTime object

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

## Storing Arrays

Container will let you store / access arrays. So, this *should* work:

```
$container = new \Lucid\Component\Container\Container();
$container->set('myarray', []);
$container->get('myarray')['testindex'] = 'testvalue';
echo($container->get('myarray')['testindex']); # should echo 'testvalue'
```

## Notes on using Container to store session data:
In order to use this to store session data, you need to call the setSource
method of Container and pass is $_SESSION by reference. You should also call session_start(); Here's a complete 3 line example:

```php
session_start();
$container = new \Lucid\Component\Container\Container();
$container->setSource(&$_SESSION);
```
Yep, that's about it.

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
echo($emailConfig->get('smtp-host'));
 # This should echo smtp.gmail.com
echo($emailConfig->get('root-path','none found'));
 # this should echo 'none found'
```

But, anything that uses the $masterConfig version *can* access indexes prefixed by the decorator:

```
echo($masterConfig->get('email:smtp-host'));
 # This should echo smtp.gmail.com
echo($masterConfig->get('root-path'));
 # this should echo something like  /var/www/myproject
```


Notably, if you think of the decorators / prefixed indexes as a hierarchy, you can actually access data higher up in the hierarchy by calling ->get and using ../ in front of an index you want. So, based on our previous example:

```
echo($emailConfig->get('root-path','none found'));
 # this should echo 'none found'
echo($emailConfig->get('../root-path','none found'));
 # this should actually echo something like  /var/www/myproject!
```

## Requiring Interfaces for Indexes

You can also require the values in a particular index to implement one or more interfaces. Let's say you've defined something named MailerInterface, and you've defined a global variable named $app that is supposed to contain an index named 'mailer'. You want to make sure that anything that goes into 'mailer' implements MailerInterface so that if someone decides to replace 'mailer', all the rest of your code will still work. Here's how you go about that:

```php
$app = new \Lucid\Component\Container\Container();
$app->requireInterfacesForIndex('mailer', 'MailerInterface');
$app->set('mailer', new MyMailer());
 # assuming your class MyMailer implements MyMailerInterface, this should work!
 # If it doesn't implement it, get ready to catch RequiredInterfaceException.

 # Now this should NOT work:
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

## Exception Classes

4 Exception classes are provided:

* DateTimeParseException: thrown when data cannot be parsed by \DateTime::createFromFormat
* InvalidSourceException: thrown when ->setSource($newSource) is called, but the new source is not an array, nor an object that supports both the ArrayAccess and Traversable interfaces
* LockedIndexEception: thrown when trying to set a locked index
* RequiredInterfaceException: thrown when an index requires an object that implements one more more interfaces, and you attempt to set that index to an object that does not implement those interfaces