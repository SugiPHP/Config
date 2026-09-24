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
 *   - a path to an existing directory      -> DirectoryConfig
 *   - a LoaderInterface,
 *     or an array of LoaderInterface       -> LoaderConfig
 *
 *   $config = new Config(__DIR__ . '/config/app.php');   // FileConfig
 *   $config = new Config(__DIR__ . '/config');            // DirectoryConfig
 *   $config = new Config(new PhpLoader($locator));        // LoaderConfig
 *   $config = new Config([$phpLoader, $jsonLoader]);
 */
class Config implements ConfigInterface
{
    private ConfigInterface $delegate;

    /**
     * @param string|array<LoaderInterface>|LoaderInterface $source
     *
     * @throws ConfigException if a string is neither an existing file nor an
     *     existing directory, or an array contains anything but loaders
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

        // array of loaders
        foreach ($source as $loader) {
            if (!$loader instanceof LoaderInterface) {
                throw new ConfigException('An array passed to Config must contain only ' . LoaderInterface::class . ' instances');
            }
        }

        $this->delegate = new LoaderConfig($source);
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
