<?php

declare(strict_types=1);

namespace SugiPHP\Config;

use SugiPHP\Config\Exception\ConfigException;

/**
 * Convenience entry point that picks the right configuration reader for
 * whatever path you give its constructor:
 *
 *   - a path to an existing file           -> FileConfig
 *   - a path to an existing directory      -> DirectoryConfig
 *
 *   $config = new Config(__DIR__ . '/config/app.php');   // FileConfig
 *   $config = new Config(__DIR__ . '/config');            // DirectoryConfig
 */
class Config implements ConfigInterface
{
    private ConfigInterface $delegate;

    /**
     * @param string $path a configuration file or a directory of them
     *
     * @throws ConfigException if the path is neither an existing file nor an
     *     existing directory
     */
    public function __construct(string $path)
    {
        if (is_file($path)) {
            $this->delegate = new FileConfig($path);
        } elseif (is_dir($path)) {
            $this->delegate = new DirectoryConfig($path);
        } else {
            throw new ConfigException("Neither a file nor a directory: {$path}");
        }
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
