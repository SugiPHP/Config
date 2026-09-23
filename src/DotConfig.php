<?php

declare(strict_types=1);

namespace SugiPHP\Config;

/**
 * Provides a common way to use arrays as application configuration settings.
 * Access configuration settings with dot notation:
 *
 *   $config = new DotConfig([
 *      'db' => [
 *          'host' => 'localhost'
 *      ]
 *   ])
 *   $config->get('db.host'); // will return 'localhost'
 *
 */
class DotConfig implements ConfigInterface
{
    /**
     * @var array<mixed>
     */
    private array $registry = [];

    /**
     * Constructor.
     *
     * @param array<mixed> $data
     */
    public function __construct(array $data)
    {
        $this->registry = $data;
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $key): bool
    {
        $registry = $this->registry;
        $parts = explode('.', $key);
        foreach ($parts as $subkey) {
            if (!is_array($registry) || !array_key_exists($subkey, $registry)) {
                return false;
            }
            $registry = $registry[$subkey];
        }
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $registry = $this->registry;
        $parts = explode('.', $key);
        foreach ($parts as $subkey) {
            if (!is_array($registry) || !array_key_exists($subkey, $registry)) {
                return $default;
            }
            $registry = $registry[$subkey];
        }
        return $registry;
    }

    /**
     * Returns the whole underlying configuration array.
     *
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return $this->registry;
    }
}
