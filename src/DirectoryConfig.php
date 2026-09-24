<?php

declare(strict_types=1);

namespace SugiPHP\Config;

use SugiPHP\Config\Exception\ConfigException;

/**
 * Reads configuration spread across multiple files in a directory, with dot
 * notation access (like DotConfig) across the whole thing.
 *
 * The first segment of a key (up to the first dot) is treated as a file name
 * (without extension) to look up in the given directory; the rest of the key
 * is resolved with dot notation inside that file's contents. The file's
 * extension is auto-detected: <name>.php, <name>.ini, <name>.json or
 * <name>.xml. Exactly one of them may exist - if more than one does, the
 * resource is ambiguous and a ConfigException is thrown, rather than one
 * file silently shadowing the others.
 *
 *   // config/db.php returns ['host' => 'localhost']
 *   $config = new DirectoryConfig(__DIR__ . '/config');
 *   $config->get('db.host'); // 'localhost'
 */
class DirectoryConfig implements ConfigInterface
{
    private string $directory;

    /**
     * @var array<string, array|null>
     */
    private array $registry = [];

    /**
     * @param string $directory the directory to search
     *
     * @throws ConfigException if the directory does not exist
     */
    public function __construct(string $directory)
    {
        if (!is_dir($directory)) {
            throw new ConfigException("Directory does not exist: $directory");
        }
        $this->directory = rtrim($directory, "\\/");
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
     * @param string $resource
     *
     * @return array|null Returns null if the resource was not found
     *
     * @throws ConfigException if the resource is ambiguous
     */
    private function discover(string $resource): ?array
    {
        $filePath = $this->locateFile($resource);
        if ($filePath === null) {
            return null;
        }

        return (new FileConfig($filePath))->toArray();
    }

    /**
     * @param string $fileName file name without extension
     *
     * @return string|null Returns null if no matching file exists
     *
     * @throws ConfigException if more than one matching file exists
     */
    private function locateFile(string $fileName): ?string
    {
        $found = [];
        foreach (['php', 'ini', 'json', 'xml'] as $ext) {
            $filePath = $this->directory . '/' . $fileName . '.' . $ext;
            if (is_file($filePath)) {
                $found[] = $filePath;
            }
        }

        if (count($found) > 1) {
            $names = implode(', ', array_map('basename', $found));
            throw new ConfigException("Ambiguous configuration resource \"{$fileName}\": found {$names} in {$this->directory}");
        }

        return $found[0] ?? null;
    }
}
