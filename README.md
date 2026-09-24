# Config

## Installation

Requires PHP 8.1 or newer.

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

A key explicitly set to `null` in the configuration exists: `has()` returns
`true` for it and `get()` returns `null`, not the default value. This is the
same for every class below.

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

The file's extension is auto-detected: `DirectoryConfig` looks for
`<name>.php`, `<name>.ini`, `<name>.json` and `<name>.xml`. Exactly one of
them may exist: if, say, both `db.ini` and `db.json` are in the directory,
the lookup is ambiguous and `get()`/`has()` throw a
`SugiPHP\Config\Exception\ConfigException` instead of silently picking one.
If the directory doesn't exist, the constructor throws a `ConfigException`.

## Config

As shown at the top, `Config` can do all of the above by itself — you never
have to pick a class yourself. Its constructor looks at what you give it and
delegates accordingly:

 - a path to an existing file           -> `FileConfig`
 - a path to an existing directory      -> `DirectoryConfig`
 - an array                             -> `DotConfig`

```php
<?php
new \SugiPHP\Config\Config(__DIR__."/config/app.php");            // FileConfig
new \SugiPHP\Config\Config(__DIR__."/config");                    // DirectoryConfig
new \SugiPHP\Config\Config(["db" => ["host" => "localhost"]]);    // DotConfig
?>
```

An array works exactly like a file with the same contents, which is handy
for tests or for configuration built at runtime. A string that is neither an
existing file nor an existing directory throws a
`SugiPHP\Config\Exception\ConfigException`.

## Upgrading from loaders (2.x)

Version 3.0 removed the loaders (`IniLoader`, `JsonLoader`, `NativeLoader`,
`XmlLoader`, `LoaderInterface`) together with `FileLocator` and
`LocatorInterface`. `Config` no longer accepts a loader or an array of
loaders — only a file or directory path. Here is how to replace each typical
use.

**One loader searching one directory** — pass the directory instead. Keys stay
the same (`<file name>.<key>`):

```php
<?php
// before
$locator = new \SugiPHP\Config\FileLocator(__DIR__."/config");
$config = new \SugiPHP\Config\Config(new \SugiPHP\Config\JsonLoader($locator));
// after
$config = new \SugiPHP\Config\Config(__DIR__."/config");

$config->get("app.production.host"); // same as before
?>
```

**Several loaders (different formats) over the same directory** — also just
pass the directory; `DirectoryConfig` handles all formats at once. The
difference is that loaders picked a winner by their order when the same name
existed in several formats (e.g. `app.ini` and `app.json`), whereas now that's
an error. Keep one file per name: the files that lost were never read, so
deleting them doesn't change your configuration.

**A `FileLocator` with several directories** (e.g. defaults plus local
overrides) — there's no built-in equivalent, since each `DirectoryConfig`
reads one directory. Combine them with a few lines of your own:

```php
<?php
use SugiPHP\Config\ConfigInterface;
use SugiPHP\Config\DirectoryConfig;

/**
 * Returns a key from the first config that has it.
 */
final class FirstMatchConfig implements ConfigInterface
{
    private array $configs;

    public function __construct(ConfigInterface ...$configs)
    {
        $this->configs = $configs;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        foreach ($this->configs as $config) {
            if ($config->has($key)) {
                return $config->get($key);
            }
        }

        return $default;
    }

    public function has(string $key): bool
    {
        foreach ($this->configs as $config) {
            if ($config->has($key)) {
                return true;
            }
        }

        return false;
    }
}

$config = new FirstMatchConfig(
    new DirectoryConfig(__DIR__."/config.local"),
    new DirectoryConfig(__DIR__."/config"),
);
?>
```

Note that the loaders picked the first *file* found, so a `config.local/db.php`
hid all of `config/db.php`. `FirstMatchConfig` falls back per *key*, so
`config.local/db.php` only needs the keys it overrides.

**A loader without a `FileLocator` (resolving direct file paths)** — use
`FileConfig` (or `Config`) with the file path. Keys no longer start with the
file name:

```php
<?php
// before
$config = new \SugiPHP\Config\Config(new \SugiPHP\Config\JsonLoader());
$config->get("/path/to/app.production.host");
// after
$config = new \SugiPHP\Config\FileConfig("/path/to/app.json");
$config->get("production.host");
?>
```

**A custom `LoaderInterface` implementation** (e.g. settings from a database)
— either load the data into an array and wrap it in a `DotConfig`, or
implement `ConfigInterface` (`get()` and `has()`) directly:

```php
<?php
$rows = $pdo->query("SELECT name, value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$config = new \SugiPHP\Config\DotConfig(["settings" => $rows]);

$config->get("settings.site_name");
?>
```
