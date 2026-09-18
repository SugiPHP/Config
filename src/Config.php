<?php

declare(strict_types=1);

namespace SugiPHP\Config;

class Config implements ConfigInterface
{
    protected $registry = array();
    protected $loaders = array();

    /**
     * Creates a Config instance.
     *
     * @param array|LoaderInterface|null $loaders array of LoaderInterface
     */
    public function __construct($loaders = null)
    {
        if (!is_null($loaders)) {
            if (is_array($loaders)) {
                foreach ($loaders as $loader) {
                    $this->addLoader($loader);
                }
            } else {
                $this->addLoader($loaders);
            }
        }
    }

    public function addLoader(LoaderInterface $loader)
    {
        $this->loaders[] = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->load($key);
        $res = $this->parse($key);

        return is_null($res) ? $default : $res;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        $this->load($key);

        $values = $this->registry;
        foreach (explode(".", $key) as $part) {
            if (!is_array($values) || !array_key_exists($part, $values)) {
                return false;
            }
            $values = $values[$part];
        }

        return !is_null($values);
    }

    /**
     * Makes sure the configuration file for the given (possibly dotted) key is
     * discovered and loaded into the registry.
     *
     * @param string $key
     *
     * @return void
     */
    protected function load(string $key)
    {
        $parts = explode(".", $key);
        $file = array_shift($parts);

        if (!isset($this->registry[$file])) {
            $this->registry[$file] = $this->discover($file);
        }
    }

    /**
     * Tries to find needed resource by looping each of registered loaders.
     *
     * @param string $resource
     *
     * @return array|null Returns null if resource is not found
     */
    protected function discover($resource)
    {
        foreach ($this->loaders as $loader) {
            $res = $loader->load($resource);
            if (!is_null($res)) {
                return $res;
            }
        }
    }

    /**
     * Search for a key with dot notation in the array. If the key is not found NULL is returned
     *
     * @param string $key
     *
     * @return mixed|null Returns NULL if the key is not found.
     */
    protected function parse($key)
    {
        $values = $this->registry;
        $parts = explode(".", $key);
        foreach ($parts as $part) {
            if (!is_array($values) || !array_key_exists($part, $values)) {
                return ;
            }
            $values = $values[$part];
        }

        return $values;
    }
}
