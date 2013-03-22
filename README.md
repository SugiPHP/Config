Config
======

SugiPHP\Config can read several different type of files (php, json, ini, neon, yaml, etc.) and interpret them as 
configuration options. With simple get("your.key") method you can retrieve corresponding config value.

Loaders
-------

A loader binds a key with a corresponding value which can be found somewhere (in a file, in a database, etc.) 
and can be in any form (a php array, json string, xml, etc.). 
A simple example that explains a loader:
Lets assume your application resides in a "/path/to/app", and your configuration files are in "/path/to/app/config"
path. Your application needs a database connection. The host, database, user and password are described in a PHP file 
living in configuration directory. You can use a loader which will include a file (a $key = "database" with ".php" extension) 
in that folder and return the contents, like the PHP code will:
``` 
include "/path/to/app/config/database.php";
```
does. A slightly more complicated example is when a database is described in an json format. So the loader will do something 
like:
```
return json_decode(file_get_contents("/path/to/app/config/database.json"), true);
```
Another example is if some of your app configurations are stored not in files but lets say in a NoSQL storage. So you can 
write your custom loader which will connect to the NoSQL DB, fetch items and return them as array. And that's really easy,
and the better thing is that your existing code will not need any modification.

FileLocator
-----------

FileLocator is used to search for a (configuration) file in one or more directories.

```
// search in one directory only
$locator = new FileLocator("/path/to/app/config");
// search in several directories
$locator = new FileLocator(array("/path/to/app/config", "/other/config/path/"));
// add additional path
$locator->addPath("/somewhere/else/config/");
```


