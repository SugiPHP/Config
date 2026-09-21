# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

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
