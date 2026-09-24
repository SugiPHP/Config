# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [3.0.0] - Unreleased

### Breaking Changes

- Removed the loaders and the loader-based `Config`. Configuration is now read
  with `FileConfig` (one file), `DirectoryConfig` (one file per top-level key
  in a directory), `DotConfig` (an in-memory array), or `Config`, which picks
  one of them from what it's given. See "Upgrading from loaders (2.x)" in the
  README for how to replace each use. Removed classes:
  - `SugiPHP\Config\IniLoader`, `JsonLoader`, `NativeLoader`, `XmlLoader` and
    `LoaderInterface`, so custom loaders can no longer be plugged in;
    implement `ConfigInterface` or wrap an array in `DotConfig` instead.
  - `SugiPHP\Config\FileLocator` and `LocatorInterface`, together with
    `FileLocator`'s deprecated `addPath()`, `popPath()`, `prependPath()`,
    `unshiftPath()` and `shiftPath()`. There is no built-in replacement for
    searching several directories; the README shows a short
    `ConfigInterface` implementation that combines several `DirectoryConfig`s.
- `Config`'s constructor is now `__construct(string|array $source)`:
  - a path to an existing file is delegated to `FileConfig`;
  - a path to an existing directory is delegated to `DirectoryConfig`;
  - an array is delegated to `DotConfig`;
  - a string that is neither an existing file nor an existing directory
    throws `ConfigException`.

  It no longer accepts a loader, an array of loaders or `null`, the argument
  is required (`new Config()` no longer works), and `addLoader()` is gone.
- A resource that exists in several formats in the same directory (e.g.
  `app.ini` and `app.json`) used to be resolved by loader order, the first
  loader silently winning. `DirectoryConfig` treats it as ambiguous and
  throws `ConfigException`; keep one file per name.
- INI files are parsed with `INI_SCANNER_TYPED`: numbers become `int`/`float`,
  `true`/`on`/`yes` and `false`/`off`/`no`/`none` become `bool`, and `null`
  becomes `null`. In 2.x every value was a string: `42` gave `"42"`, `on`
  gave `"1"`, and `off`/`none`/`null` gave `""`. Quoted values (`"42"`) stay
  strings. Code comparing INI values strictly against strings must be updated.
- Removed `SugiPHP\Config\Exception` (`src/Exception.php`), deprecated since
  2.0.0. Catch/throw `SugiPHP\Config\Exception\ConfigException` (or a more
  specific subclass: `FileException`, `ParserException`) instead.
- Removed `fromString()` and `fromFile()` from the parsers (`Ini`, `Json`,
  `Php`, `Xml`) and `AbstractFileReader`, deprecated since 2.0.0. Use
  `parse()` and `parseFile()` instead. `ParserInterface` already declared only
  the new methods, so custom parsers implementing it are unaffected.
- Parsers throw different exception classes for some errors (see "Changed"
  below). All of them are still `ConfigException` subclasses, so only code
  catching `FileException` for a PHP file that doesn't return an array, or a
  `\ParseError`/`\TypeError` from a broken config file, is affected.

### Added

- `SugiPHP\Config\FileConfig`: reads a single configuration file, choosing the
  parser from its extension (`.php`, `.json`, `.ini`, `.xml`). Keys are
  resolved with dot notation inside the file (no file-name prefix). Throws
  `ConfigException` if the file doesn't exist, has no extension, or has an
  unsupported one, `FileException` if it can't be read, and
  `ParserException` if its contents are invalid.
- `SugiPHP\Config\DirectoryConfig`: reads configuration split across files in
  one directory. The first segment of the key is the file name (extension
  detected among `.php`, `.ini`, `.json` and `.xml`), the rest is resolved
  with dot notation inside that file, e.g. `get("db.host")` reads `host` from
  `db.php`. Files are loaded lazily, on first access. The constructor takes
  exactly one directory and throws `ConfigException` if it doesn't exist.
  Like the 2.x `Config`, a key whose value is `null` is treated as absent.
- `SugiPHP\Config\DotConfig`: wraps an in-memory array with dot-notation
  `get()`/`has()`, plus `toArray()` to get the whole array back. Unlike
  `DirectoryConfig`, a key explicitly set to `null` exists: `has()` returns
  `true` and `get()` returns `null` rather than the default. `FileConfig`
  extends `DotConfig`, so the same applies to it (and to `Config` given a
  file or an array).

### Changed

- `SugiPHP\Config\Parser\Php::parse()` returns an array passed to it
  unchanged, instead of always throwing. Anything else (including a string of
  PHP code) still throws.
- Parsers consistently throw `SugiPHP\Config\Exception\ParserException` for
  invalid content and `FileException` only for missing or unreadable files:
  - `Xml`: malformed XML used to end in a `TypeError` (and emit libxml
    warnings); it now throws `ParserException` with the libxml error message.
  - `Json`: syntax errors and non-array results threw the base
    `ConfigException`; now `ParserException`.
  - `Php`: a file that doesn't return an array threw `FileException`; now
    `ParserException`. A file with a PHP syntax error used to escape as an
    uncaught `\ParseError`; it's now wrapped in a `ParserException` (the
    original is available via `getPrevious()`). `parse()` with a non-array
    throws `ParserException` instead of `ConfigException`.
  - `Ini`: a syntax error used to emit a PHP warning before throwing (which a
    global error handler could turn into a different exception); the warning
    is now captured and its text included in the `ParserException` message.
- A configuration file that exists but isn't readable always throws
  `FileException`. In 2.x a loader without a `FileLocator` treated it as "not
  found", falling through to the next loader or the default value and hiding
  permission problems. `Php::parseFile()` now checks readability too, instead
  of emitting an `include` warning.


## [2.0.0]

### Breaking Changes

- `Config::get()` now requires a string `$key` argument (was `get($key, $default = null)`
  with no type declaration). Previously, an empty or `null` `$key` gracefully returned
  `$default`; now:
  - `get()` with no arguments throws `ArgumentCountError`.
  - `get(null)` throws `TypeError`.
  - `get("")` no longer returns `$default` — it now returns the internal registry
    keyed under `""` (e.g. `["" => null]`) instead.
- Removed the SQLite-backed database loader example (`tests/Db1Loader.php`,
  `tests/DbTest.php`) and the corresponding README section/link. There was never a
  `DbLoader` shipped in `src/`; this only removes the example code and its docs, but
  any downstream code that referenced `SugiPHP\Config\Tests\Db1Loader` directly will
  break.
- Removed `Config::set()`. `Config` is now read-only: configuration can only come
  from registered loaders, not runtime mutation. Code calling `$config->set(...)`
  will fail with a fatal error (undefined method). Corresponding tests
  (`testSetVar`, `testSetArray`, `testSetHash`, `testSetDeepArray`,
  `testSetWithDotNotation`, `testSetWithDotNotationDeep`,
  `testSetWithDotNotationDeepFromBegining`, `testSetWithConfigFileFirstLoadingFile`,
  `testSetWithConfigFileFirstSettingSomeValue`, `testSetWithConfigFileOverridingValue`,
  `testSetWithNoKey`) were removed from `tests/ConfigTest.php`.
- Removed YAML support: `src/YamlLoader.php`, `tests/YamlLoaderTest.php`, and the
  `tests/config/test.yml` fixture are gone, along with the `symfony/yaml` dependency
  (`require-dev` and `suggest`) in `composer.json` and the YAML example in the
  README. Code relying on `SugiPHP\Config\YamlLoader` or `.yml` config files will
  break.

### Added

- `ConfigInterface`, defining `has(string $key): bool` and
  `get(string $key, mixed $default = null): mixed`. `Config` now implements it.
- `Config::has()` method to check whether a key is registered.
- `SugiPHP\Config\Parser\ParserInterface::parse(mixed $data): array` and
  `parseFile(string $fileName): array`, replacing `fromString()`/`fromFile()`
  as the interface's contract. All parsers (`Ini`, `Json`, `Php`, `Xml`) and
  `AbstractFileReader` implement the new methods; the loaders (`IniLoader`,
  `JsonLoader`, `NativeLoader`, `XmlLoader`) now call `parseFile()` internally.

### Changed

- `Config` now uses `declare(strict_types=1)` and scalar/`mixed` type declarations
  on `get()`.
- README installation instructions updated from `composer require sugiphp/config ~1.1`
  to `composer require sugiphp/config ^2.0`.

### Deprecated

- `SugiPHP\Config\Exception` is deprecated in favor of
  `SugiPHP\Config\Exception\ConfigException`, which is now its base class.
  Constructing it emits an `E_USER_DEPRECATED` notice. It will be removed in a
  future major version — catch/throw `ConfigException` (or a more specific
  subclass) instead.
- `FileLocator::addPath()`, `popPath()`, `prependPath()`, `unshiftPath()` and
  `shiftPath()` are deprecated. Calling any of them now emits an
  `E_USER_DEPRECATED` notice; they will be removed in a future major version.
  Pass all search paths to the `FileLocator` constructor instead of mutating them
  after construction.
- `ParserInterface::fromString()` and `fromFile()` are deprecated in favor of
  `parse()` and `parseFile()` (same behavior, `fromString()`'s `string $string`
  parameter is now `mixed $data` on `parse()`). Calling either old method on
  `Ini`, `Json`, `Php`, `Xml` or `AbstractFileReader` now emits an
  `E_USER_DEPRECATED` notice and delegates to the new method; they will be
  removed in a future major version.
