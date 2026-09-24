# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [3.0.0] - Unreleased

### Breaking Changes

- Removed `SugiPHP\Config\Exception` (`src/Exception.php`), deprecated since
  2.0.0. Use `SugiPHP\Config\Exception\ConfigException` (or a more specific
  subclass: `FileException`, `ParserException`) instead. Code catching
  `SugiPHP\Config\Exception` specifically (rather than `ConfigException` or
  `\Exception`) must be updated.
- Removed `fromString()` and `fromFile()` from the parsers (`Ini`, `Json`,
  `Php`, `Xml`) and `AbstractFileReader`, deprecated since 2.0.0. Use
  `parse()` and `parseFile()` instead. `ParserInterface` already declared only
  the new methods, so custom parsers implementing it are unaffected.
- Moved all loaders into the `SugiPHP\Config\Loader` namespace and renamed
  `NativeLoader` to `PhpLoader`:
  - `SugiPHP\Config\IniLoader` → `SugiPHP\Config\Loader\IniLoader`
  - `SugiPHP\Config\JsonLoader` → `SugiPHP\Config\Loader\JsonLoader`
  - `SugiPHP\Config\XmlLoader` → `SugiPHP\Config\Loader\XmlLoader`
  - `SugiPHP\Config\NativeLoader` → `SugiPHP\Config\Loader\PhpLoader`
  - `SugiPHP\Config\LoaderInterface` → `SugiPHP\Config\Loader\LoaderInterface`
- Removed `SugiPHP\Config\FileLocator` and `SugiPHP\Config\LocatorInterface`
  entirely (their deprecated mutation methods — `addPath()`, `popPath()`,
  `prependPath()`, `unshiftPath()`, `shiftPath()` — had already been removed;
  now the whole class is gone). Its directory-search logic was merged
  directly into `AbstractLoader`, the new base class shared by `IniLoader`,
  `JsonLoader`, `PhpLoader` and `XmlLoader`. Those loaders' constructors now
  take `string|array<string>|null $paths` (one or more directories) directly
  — code doing `new IniLoader(new FileLocator($dirs))` must change to
  `new IniLoader($dirs)`.
  - `Config`'s constructor no longer accepts `null`; omit the argument
    entirely to get an empty, loader-based `Config` (`addLoader()` isn't
    available on it either — see above).
  - Constructing it with a string that's neither an existing file nor an
    existing directory now throws `SugiPHP\Config\Exception\ConfigException`.

### Added

- `SugiPHP\Config\DotConfig`: wraps a plain, already-in-memory PHP array with
  dot-notation `get()`/`has()`, plus `toArray()` to get the whole array back.
- `SugiPHP\Config\FileConfig` (extends `DotConfig`): reads a single
  configuration file, auto-detecting the parser from its extension (`.php`,
  `.json`, `.ini`, `.xml`). Throws `ConfigException` if the file doesn't
  exist, has no extension, or has an unsupported one.
- `SugiPHP\Config\DirectoryConfig`: resolves `resource.key` style lookups
  against files in a single directory — the first segment of the key is
  the file name (extension auto-detected: `.php`, then `.ini`, then `.json`,
  then `.xml`; first match wins), the rest is resolved with dot notation
  inside that file. The constructor takes exactly one directory (a string,
  not an array) and throws `ConfigException` if it doesn't exist.
- `SugiPHP\Config\LoaderConfig`: a `Config`-independent reimplementation of
  the old loader-list resolution (tries each loader in order, first match
  wins). Public `addLoader()`.

### Changed

- `SugiPHP\Config\Parser\Php::parse()` now returns an array passed to it
  unchanged, instead of always throwing. Anything else (including a string of
  PHP code) still throws `ConfigException`.

### Deprecated

- `LoaderConfig` (and constructing loaders directly in general, including
  passing a loader to `Config`) is documented as existing only for backward
  compatibility with the old, loader-based `Config`, and may be removed in a
  future major version — prefer `FileConfig`/`DirectoryConfig`. Unlike the
  deprecations below, this one doesn't emit a runtime `E_USER_DEPRECATED`
  notice yet, it's a documentation-only notice for now.


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
