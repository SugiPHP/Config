# Config

## Installation

```shell
composer require sugiphp/config @dev-main
```

SugiPHP\Config is designed to simplify access to configuration settings. It natively supports reading and parsing configuration options from several file types (php, json, ini, xml). `get("key")` (or `get("key.subkey")` for nested values, using dot notation) searches for the key and returns its value. If the key is not found it gracefully returns NULL, or some other default value if one is provided as a second parameter.

## Usage

The easiest way to use this library is `Config`: give it a path to a
configuration file and read values from it with dot notation.

```php
<?php
$config = new \SugiPHP\Config\Config(__DIR__."/config/app.php");

$config->get("production.host");           // returns "example.com"
$config->get("development");               // array("host" => "localhost", "debug" => 1)
$config->get("production.debug");          // will return NULL, the key does not exist
$config->get("testing.host", "127.0.0.1"); // will return default value "127.0.0.1"
$config->has("production.host");           // true
$config->has("testing.host");              // false
?>
```

The file's extension (`.php`, `.json`, `.ini` or `.xml`) determines which parser is
used to read it — see the formats below for what each looks like. If the file
doesn't exist, has no extension, or has an extension none of the parsers support,
the constructor throws a `SugiPHP\Config\Exception\ConfigException`.

You can use different file types to store settings:

 - PHP (with filename app.php)

```php
<?php
return array(
	"development" => array(
		"host" => "localhost",
		"debug" => 1
	),
	"production" => array(
		"host" => "example.com"
	)
);
?>
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

 - INI (app.ini)

```ini
[development]
host=localhost
debug=1

[production]
host=example.com
```

 - XML (app.xml)

```xml
<?xml version='1.0'?>
<environments>
	<development>
		<host>localhost</host>
		<debug>1</debug>
	</development>
	<production>
		<host>example.com</host>
	</production>
</environments>
```

`Config` above is a convenience entry point: depending on what you give its
constructor, it picks one of the classes below and delegates to it. You can
also use any of them directly.

## FileConfig

`FileConfig` reads a single configuration file — this is what `Config` uses
internally when given a file path.

```php
<?php
$config = new \SugiPHP\Config\FileConfig(__DIR__."/config/app.php");

$config->get("production.host"); // returns "example.com"
?>
```

## DotConfig

`DotConfig` wraps a plain PHP array (already in memory — no file, no
directory) and gives it dot notation access.

```php
<?php
$config = new \SugiPHP\Config\DotConfig([
    "db" => ["host" => "localhost", "port" => 5432],
]);

$config->get("db.host");         // "localhost"
$config->get("db.user", "root"); // "root", the key does not exist
$config->has("db.port");         // true
$config->toArray();              // the whole underlying array
?>
```

## DirectoryConfig

If your configuration is split across multiple files in a directory (one file
per "section") instead of one big file, use `DirectoryConfig`. The first
segment of the key (up to the first dot) is treated as a file name to look up
in the given directory; the rest of the key is resolved with
dot notation inside that file's contents.

```
config/
├── db.php
└── mail.json
```

```php
<?php
// config/db.php
return array("host" => "localhost", "port" => 5432);
?>
```

```php
<?php
$config = new \SugiPHP\Config\DirectoryConfig(__DIR__."/config");

$config->get("db.host");                // "localhost"
$config->get("db");                     // array("host" => "localhost", "port" => 5432)
$config->get("mail.host", "127.0.0.1"); // default value, mail.json has no "host" key
$config->has("db.port");                // true
?>
```

The file's extension is auto-detected: `DirectoryConfig` looks for `<name>.php`, then `<name>.ini`, then `<name>.json`, then
`<name>.xml` — the first match wins. If the directory doesn't exist,
the constructor throws a `SugiPHP\Config\Exception\ConfigException`.

## LoaderConfig

`LoaderConfig` resolves resources by trying one or more loaders, in order,
stopping at the first one that finds a match — the same mechanism `Config`
uses internally when given a loader. Using loaders directly is more manual
than `FileConfig`/`DirectoryConfig`, so it's discouraged for typical use;
reach for it only when you need to merge several loaders together, each one
searching one or more directories for its own file format.

**`LoaderConfig` (and constructing loaders directly in general) exists only
for backward compatibility with the old, loader-based `Config`. It may be
removed in a future major version — prefer `FileConfig`/`DirectoryConfig`.**

```php
<?php
$loader = new \SugiPHP\Config\Loader\JsonLoader(__DIR__."/config");
$config = new \SugiPHP\Config\LoaderConfig($loader);

$config->get("app.production.host"); // returns example.com
$config->get("app.development"); // array("host" => "localhost", "debug" => 1)
$config->get("app.production.debug"); // will return NULL
$config->get("app.testing.host", "127.0.0.1"); // will return default value "127.0.0.1"
?>
```

A loader can be given a directory, an array of directories, or nothing at all
(in which case it resolves resources as direct file paths):

```php
<?php
// search in one directory only
$loader = new \SugiPHP\Config\Loader\JsonLoader("/path/to/your/app/config/");
// search in several directories
$loader = new \SugiPHP\Config\Loader\JsonLoader(array("/path/to/your/app/config", "/other/config/path/"));
?>
```

## Config

As shown at the top, `Config` can do all of the above by itself — you never
have to pick a class yourself. Its constructor looks at what you give it and
delegates accordingly:

 - a path to an existing file           -> `FileConfig`
 - a path to an existing directory      -> `DirectoryConfig`
 - a loader, or an array of loaders     -> `LoaderConfig`

```php
<?php
new \SugiPHP\Config\Config(__DIR__."/config/app.php");                          // FileConfig
new \SugiPHP\Config\Config(__DIR__."/config");                                  // DirectoryConfig
new \SugiPHP\Config\Config(new \SugiPHP\Config\Loader\JsonLoader(__DIR__."/config")); // LoaderConfig
?>
```
