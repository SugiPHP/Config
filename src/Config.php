<?php

declare(strict_types=1);

namespace SugiPHP\Config;

use SugiPHP\Config\Exception\ConfigException;
use SugiPHP\Config\Loader\LoaderInterface;

/**
 * Convenience entry point that picks the right configuration reader for
 * whatever you give its constructor:
 *
 *   - a path to an existing file           -> FileConfig
 *   - a path to an existing directory,
 *     or an array of directories           -> DirectoryConfig
 *   - a LoaderInterface,
 *     or an array of LoaderInterface       -> LoaderConfig
 *
 *   $config = new Config(__DIR__ . '/config/app.php');   // FileConfig
 *   $config = new Config(__DIR__ . '/config');            // DirectoryConfig
 *   $config = new Config([__DIR__ . '/config', __DIR__ . '/config.local']);
 *   $config = new Config(new PhpLoader($locator));        // LoaderConfig
 *   $config = new Config([$phpLoader, $jsonLoader]);
 */
class Config implements ConfigInterface
{
    private ConfigInterface $delegate;

    /**
     * @param string|array<string>|array<LoaderInterface>|LoaderInterface $source
     */
    public function __construct(string|array|LoaderInterface $source = [])
    {
        if ($source instanceof LoaderInterface) {
            $this->delegate = new LoaderConfig($source);
            return;
        }

        if (is_string($source)) {
            if (is_file($source)) {
                $this->delegate = new FileConfig($source);
                return;
            }

            if (is_dir($source)) {
                $this->delegate = new DirectoryConfig($source);
                return;
            }

            throw new ConfigException("Neither a file nor a directory: {$source}");
        }

        // array
        if (empty($source)) {
            $this->delegate = new LoaderConfig();
            return;
        }

        $first = reset($source);

        $this->delegate = $first instanceof LoaderInterface
            ? new LoaderConfig($source)
            : new DirectoryConfig($source);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->delegate->get($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        return $this->delegate->has($key);
    }
}
