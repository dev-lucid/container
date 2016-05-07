# Storing and using closures

```php
$container = new Lucid\Container\Container();
$container->set('func', function($param1) {
	echo('called with parameter: '.$param1);
});

$container->func('hi');
# this should echo 'called with parameter: hi'
```

