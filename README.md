# console-config-resolver
Simply create a symfony console application instance via zend-servicemanager.

[![Build Status](https://travis-ci.org/dwendrich/console-config-resolver.svg?branch=master)](https://travis-ci.org/dwendrich/console-config-resolver)
[![Coverage Status](https://img.shields.io/codecov/c/github/dwendrich/console-config-resolver.svg)](https://codecov.io/gh/dwendrich/console-config-resolver)
[![Latest Stable Version](http://img.shields.io/packagist/v/dwendrich/console-config-resolver.svg?style=flat)](https://packagist.org/packages/dwendrich/console-config-resolver)

## Requirements
* PHP 7.0 or higher
* [Symfony Console component 3.2 or higher](https://github.com/symfony/console)
* [Zend Framework Service Manager component 3.3 or higher](https://github.com/zendframework/zend-servicemanager)

## Installation
ConsoleConfigResolver can be installed with composer. For information on how to get composer or how to use it, please refer to
[getcomposer.org](http://getcomposer.org).

Installation via command line:
```sh
$ php composer.phar require dwendrich/console-config-resolver
```

Installation via `composer.json` file:
```json
{
    "require": {
        "dwendrich/console-config-resolver": "*"
    }
}
```

## Usage
As part of the service-manager configuration you provide a section for the console application, e. g.
```php
return [
    'Example\Console' => [
        'name'     => 'My console application',
        'version'  => '1.0.0',
        'commands' => [
            // provide a class name or a service name configured in service manager
            MyConsoleCommand::class,
            
            // instances have to extend Symfony\Component\Console\Command\Command
            new OtherConsoleCommand(),
        ],
    ],
    
    // in zend framework applications this section is called 'service_manager'
    'dependencies' => [
        'factories' => [
            'Example\Console' => ConsoleConfigResolver\Factory\ConfigResolverFactory::class,
        ],
    ],
];
```

Under the key `commands` you can provide commands which will be added to your application. These have to either be a
class name or an object instance extending `Symfony\Component\Console\Command\Command`.

Now in your code you can use the service manager to create the console application instance, e. g. in a file called
`console.php` you can do it like this:
```php
#!/usr/bin/env php
<?php

chdir(dirname(__FILE__));

call_user_func(function () {
    // get your service manager instance
    $container = require 'config/container.php';
    
    // create the console application as configured in the example above
    $console = $container->get('Example\Console');
    
    // run the application
    $console->run();
});
```

Run the application in a terminal, e. g.
```sh
$ php console.php list
```
