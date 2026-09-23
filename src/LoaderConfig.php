<?php

declare(strict_types=1);

namespace SugiPHP\Config;

use SugiPHP\Config\Loader\LoaderInterface;

/**
 * Reads configuration by resolving the first segment of a key (up to the
 * first dot) as a resource name via one or more LoaderInterface instances,
 * tried in order; the rest of the key is resolved with dot notation inside
 * that resource's contents.
 *
 *   $config = new LoaderConfig(new PhpLoader(__DIR__.'/config'));
 *   $config->get('db.host');
 *
 *   // several loaders: the first one to resolve the resource wins
 *   $config = new LoaderConfig([$phpLoader, $jsonLoader, $iniLoader]);
 *
 *   // loaders can also be added after construction
 *   $config = new LoaderConfig();
 *   $config->addLoader($phpLoader);
 *
 * @see DirectoryConfig for the equivalent that resolves resources by
 *      searching one or more directories instead of using loaders.
 */
class LoaderConfig implements ConfigInterface
{
    /**
     * @var array<LoaderInterface>
     */
    private array $loaders = [];

    /**
     * @var array<string, array|null>
     */
    private array $registry = [];

    /**
     * @param LoaderInterface|array<LoaderInterface> $loaders
     *     A loader, an array of loaders, or nothing at all (loaders can then
     *     be added later via addLoader()).
     */
    public function __construct(LoaderInterface|array $loaders = [])
    {
        if ($loaders instanceof LoaderInterface) {
            $this->addLoader($loaders);
            return;
        }

        foreach ($loaders as $loader) {
            $this->addLoader($loader);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->load($key);

        $values = $this->registry;
        foreach (explode('.', $key) as $part) {
            if (!is_array($values) || !array_key_exists($part, $values)) {
                return $default;
            }
            $values = $values[$part];
        }

        return $values ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        $this->load($key);

        $values = $this->registry;
        foreach (explode('.', $key) as $part) {
            if (!is_array($values) || !array_key_exists($part, $values)) {
                return false;
            }
            $values = $values[$part];
        }

        return !is_null($values);
    }

    public function addLoader(LoaderInterface $loader): void
    {
        $this->loaders[] = $loader;
    }

    /**
     * Makes sure the resource (the first segment of the key) has been
     * discovered and, if found, loaded into the registry.
     *
     * @param string $key
     *
     * @return void
     */
    private function load(string $key): void
    {
        $parts = explode('.', $key);
        $resource = array_shift($parts);

        if (!isset($this->registry[$resource])) {
            $this->registry[$resource] = $this->discover($resource);
        }
    }

    /**
     * Tries each loader, in order, stopping at the first one that resolves
     * the resource.
     *
     * @param string $resource
     *
     * @return array|null Returns null if no loader found the resource
     */
    private function discover(string $resource): ?array
    {
        foreach ($this->loaders as $loader) {
            $result = $loader->load($resource);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }
}
