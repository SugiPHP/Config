<?php

declare(strict_types=1);

namespace SugiPHP\Config;

use SugiPHP\Config\Exception\ConfigException;

/**
 * Convenience entry point that picks the right configuration reader for
 * whatever you give its constructor:
 *
 *   - a path to an existing file           -> FileConfig
 *   - a path to an existing directory      -> DirectoryConfig
 *   - an array                             -> DotConfig
 *
 *   $config = new Config(__DIR__ . '/config/app.php');   // FileConfig
 *   $config = new Config(__DIR__ . '/config');            // DirectoryConfig
 *   $config = new Config(['db' => ['host' => 'localhost']]); // DotConfig
 */
class Config implements ConfigInterface
{
    private ConfigInterface $delegate;

    /**
     * @param string|array $source a configuration file, a directory of them,
     *     or the configuration itself as an array
     *
     * @throws ConfigException if a string is neither an existing file nor an
     *     existing directory
     */
    public function __construct(string|array $source)
    {
        if (is_array($source)) {
            $this->delegate = new DotConfig($source);
            return;
        }

        if (is_file($source)) {
            $this->delegate = new FileConfig($source);
        } elseif (is_dir($source)) {
            $this->delegate = new DirectoryConfig($source);
        } else {
            throw new ConfigException("Neither a file nor a directory: {$source}");
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
