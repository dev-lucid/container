# Using the PrefixDecorator class

The PrefixDecorator class allows you to create an object based on your container that has the same api, but automagically prefixes all indexes. One might use this to have one master container that contains all of your config options, and hand off decorated copies of the container to sub components, each of which stores their own config in the master container with prefixes. Anything using the decorated objects doesn't need to be aware of this structure at all.

How about an example?

```php
$masterConfig = new \Lucid\Container\Container();
$emailConfig = new \Lucid\Container\PrefixDecorator('email/', $masterConfig);

$masterConfig->set('root-path', __DIR__);
$emailConfig->set('smtp-host', 'smtp.gmail.com');

print_r($masterConfig->getValues());
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
