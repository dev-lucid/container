# Using Container as a service locator

There's a couple ways you can use the container as a service locator:

* Storing a container in a global variable ($app, $config, $factory, etc)
* Storing a container into a static property in a class (App::$container, MyApp::$services, MyApp::$config, etc)
* Using a global function

Using a global function is my personal preference, and I don't see many people using this idea, so I'm going to give a quick example:

```php
global $appContainer;
$appContainer = new Lucid\Container\InjectorFactoryContainer();

# declare a global function that acts as a frontend to the global variable
function app(string $id = null)
{
    global $appContainer;
    if (is_null($id) === true) {
        return $appContainer;
    }
    return $appContainer->get($id);
}

app()->set('cookie',  new Lucid\Container\CookieContainer());
app()->set('session', new Lucid\Container\SessionContainer());
app()->set('server',  new Lucid\Container\ServerContainer());
app()->set('config',  new Lucid\Container\Container());
app()->set('logger',  new YourLoggingClass());
```

Given this code, from any other piece of code in your application, you can access anything in your container by calling app($id). For example,

```php
app('logger')->debug('session user_id = '. app('session')->int('user_id, 0));
```

Note that container stored in $appContainer is an InjectorFactoryContainer class, meaning it's capable of performing [dependency injection](InjectionFactory.md). For example, given the above code, you could do something like this:

```php
# this code is located in an appropriatedly named file for your autoloader to discover
namespace App\Controller;
use \Psr\Log\LoggerInterface;
use \Lucid\Container\ContainerInterface;

class Users 
{
	public function __construct(ContainerInterface $config)
	{
		$this->config = $config;
	}
	
	public function processRegistration(LoggerInterface $logger, ContainerInterface $request, ContainerInterface $session)
	{
		$logger->debug('Processing a registration!');
		$logger->debug('The user submitted this email address: '.$request->string('email'));
	}
}

# this line is NOT in the same file. Perhaps in index.php
app()->execute("App\Controller\Users", 'processRegistration');

```

In this last example, a class is declared: App\Controller\Users. This class's constructor has a parameter named $config, which must implement \Container\ContainerInterface and is named $config;

It has a second method named processRegistration, and this has 3 parameters:

* $logger, which must implement \Psr\Log\LoggerInterface
* $request, which must implement \Lucid\Container\ContainerInterface
* $session, which must implement \Lucid\Container\ContainerInterface

The last line does all the magic. From left to right:

* Calling the global function app() with no parameter will simply return instantiated the InjectorFactoryContainer. 
* The execute method takes two parameters:
* * An index in the container which contains an object or an alredy instanted class or the name of an object that the constructor knows how to instantiate,
* * A method to call

In this case, we did NOT configure any constructors, so the parameter will be treated a class to be instantiated. The execute function does so. During this process, the function will:

* * Use reflection to discover that the __construct method if the class has a parameter named $config that must implement \Lucid\Container\ContainerInterface. 
* * The container looks for an index named 'config', verifies that it suppors the necessary interface 
* * Calls the constructor and passes the correct index in the container for that parameter

The execute function then calls the processRegistrationMethod. This involves the same basic steps as constructing the object:

* * Use reflection to discover that the metho has 3 parameters
* * Locate appropriate indexes inside the container for those parameters,
* * Call the method with the right values for each parameter.

## How to unit test in this configuration

Given the above example, the best way to unit test would be to setup different indexes in your container based on whether or not you're unit testing, in a development environment, or in production. Consider thise code:

```php
global $appContainer;
$appContainer = new Lucid\Container\InjectorFactoryContainer();

# declare a global function that acts as a frontend to the global variable
function app(string $id = null)
{
    global $appContainer;
    if (is_null($id) === true) {
        return $appContainer;
    }
    return $appContainer->get($id);
}

if ($_ENV['stage'] === 'phpunit') {
	app()->set('server',  new Lucid\Container\Container());
	app()->set('cookie',  new Lucid\Container\Container());
	app()->set('session', new Lucid\Container\Container());
	app()->set('config',  new Lucid\Container\Container());
	$logger = MyLoggingClass();
	$logger->setOutputFile('/dev/null');
	app()->set('logger',  $logger);
} else {
	app()->set('server',  new Lucid\Container\ServerContainer());
	app()->set('cookie',  new Lucid\Container\CookieContainer());
	app()->set('session', new Lucid\Container\SessionContainer());
	app()->set('config',  new Lucid\Container\Container());
	
	$logger = MyLoggingClass();
	if ($_ENV['stage'] == 'development') {
		$logger->setOutputFile('/var/log/development.log');
	}
	if ($_ENV['stage'] == 'qa') {
		$logger->setOutputFile('/var/log/qa.log');
	}
	if ($_ENV['stage'] == 'production') {
		$logger->setOutputFile('/var/log/production.log');
	}
	app()->set('logger',  $logger);
}

```
Given this code, an environment variable is used to set 1 of 4 possible stages: phpunit, development, qa, or production. When in the phpunit stage, all logging is directed to /dev/null, and the indexes 'server', 'cookie', 'session' are all generic containers that would not actually write cookies/session variables/etc. This would allow your unit test scripts to set mock data before each test.

In the other three environments, 'cookie' and 'session' really do write cookies/session variables, and the only real difference is that your log file is named differently. 