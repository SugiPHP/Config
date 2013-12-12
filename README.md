SugiPHP\Config
==============

[![Build Status](https://travis-ci.org/SugiPHP/Config.png)](https://travis-ci.org/SugiPHP/Config)

SugiPHP\Config is designed to simplify access to configuration settings. Config class natively supports reading and
parsing configuration options from several file types (php, json, yaml, ini) stored in one or several locations in
your project. Config::get("file.key") method automatically finds configuration file, loads it, parses it and then
searches for the key and returns it's value. If the file or the key is not found gracefully returns NULL or some other
default value if it is provided like a second parameter.

Usage
-----

You can use different file types to store settings:

 - PHP (with filename app.php)

```php
<?php

return array(
	"development" => array(
		"host" => "localhost",
		"debug" => 1
	},
	"production" => array(
		"host" => "example.com"
	)
);
```

 - JSON (app.json)
```json
{
	"development": {
		"host": "localhost",
		"debug": 1
	},
	"production": {
		"host": "example.com"
	}
}
```

 - YAML (app.yml)

```yaml

development:
	host: localhost
	debug: 1

production:
	host: example.com
```

 - INI (app.ini)

```ini
[development]
host=localhost
debug=1

[production]
host=example.com
```

To access the host option no matter wich type of configurations file you use:

```php
<?php
use SugiPHP\Config\Config;

$locator = new FileLocator(__DIR__."/config");
$loader = new JsonLoader($locator);
$config = new Config($loader);

$config->get("app.production.host"); // returns example.com
$config->get("app.development"); // array("host" => "localhost", "debug" => 1)
$config->get("app.production.debug"); // will return NULL
$config->get("app.testing.host", "127.0.0.1"); // will return default value "127.0.0.1"
```

FileLocator
-----------

FileLocator is used to search for a (configuration) file in one or more directories.

```php
<?php
// search in one directory only
$locator = new FileLocator("/path/to/your/app/config/");
// search in several directories
$locator = new FileLocator(array("/path/to/your/app/config", "/other/config/path/"));
// add additional path
$locator->addPath("/somewhere/else/config/");
```


Loaders
-------

A loader binds a key with a corresponding value which can be found somewhere (in a file, in a database, etc.)
and can be in any form (a php array, json string, xml, etc.).
A simple example that explains a loader:
Lets assume your application resides in a "/path/to/app", and your configuration files are in "/path/to/app/config"
path. Your application needs a database connection. The host, database, user and password are described in a PHP file
living in configuration directory. You can use a loader which will include a file (a $key = "database" with ".php" extension)
in that folder and return the contents, like the PHP code will:

```php
include "/path/to/app/config/database.php";
```

does. A slightly more complicated example is when a database is described in an json format. So the loader will do something
like:

```php
return json_decode(file_get_contents("/path/to/app/config/database.json"), true);
```

Another example is if some of your app configurations are stored not in files but lets say in a NoSQL storage. So you can
write your custom loader which will connect to the NoSQL DB, fetch items and return them as array. And that's really easy,
and the better thing is that your existing code will not need any modification.
