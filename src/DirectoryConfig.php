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
 * is resolved with dot notation inside that file's contents, exactly like
 * FileConfig does (a key explicitly set to null exists). The file's
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
     * Loaded files, keyed by resource name. Null marks a resource with no
     * matching file, so it isn't searched for again.
     *
     * @var array<string, FileConfig|null>
     */
    private array $files = [];

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
        [$resource, $subkey] = $this->splitKey($key);

        $file = $this->load($resource);
        if ($file === null) {
            return $default;
        }

        return $subkey === null ? $file->toArray() : $file->get($subkey, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        [$resource, $subkey] = $this->splitKey($key);

        $file = $this->load($resource);
        if ($file === null) {
            return false;
        }

        return $subkey === null || $file->has($subkey);
    }

    /**
     * Splits "db.host.name" into the resource ("db") and the key inside it
     * ("host.name", or null when there's none).
     *
     * @param string $key
     *
     * @return array{0: string, 1: string|null}
     */
    private function splitKey(string $key): array
    {
        $parts = explode('.', $key, 2);

        return [$parts[0], $parts[1] ?? null];
    }

    /**
     * Returns the file for a resource, loading it on first access.
     *
     * @param string $resource
     *
     * @return FileConfig|null Returns null if the resource was not found
     *
     * @throws ConfigException if the resource is ambiguous
     */
    private function load(string $resource): ?FileConfig
    {
        if (!array_key_exists($resource, $this->files)) {
            $filePath = $this->locateFile($resource);
            $this->files[$resource] = $filePath === null ? null : new FileConfig($filePath);
        }

        return $this->files[$resource];
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
