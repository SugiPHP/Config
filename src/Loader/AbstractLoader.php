<?php

declare(strict_types=1);

/**
 * Base class shared by all file-based loaders.
 */

namespace SugiPHP\Config\Loader;

use SugiPHP\Config\Parser\ParserInterface;

abstract class AbstractLoader implements LoaderInterface
{
    /**
     * Search paths. Null means "no search paths configured" - resources are
     * then resolved as direct file paths instead.
     *
     * @var array<string>|null
     */
    protected $paths;

    /**
     * @param string|array<string>|null $paths
     *     One or more directories to search for a resource in. Omit (or pass
     *     null) to resolve resources as direct file paths instead.
     */
    public function __construct(string|array|null $paths = null)
    {
        if ($paths === null) {
            $this->paths = null;
            return;
        }

        $this->paths = [];
        foreach ((array) $paths as $path) {
            $this->paths[] = rtrim($path, "\\/") . DIRECTORY_SEPARATOR;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $resource): ?array
    {
        // check the extension. If it's not provided we'll add the default one
        if (pathinfo($resource, PATHINFO_EXTENSION) === '') {
            $resource .= '.' . $this->getExtension();
        }

        $file = $this->paths === null
            ? $this->locateDirect($resource)
            : $this->locateInPaths($resource);

        if ($file === null) {
            return null;
        }

        return $this->getParser()->parseFile($file);
    }

    /**
     * Default file extension (without the leading dot) appended to a resource
     * name that doesn't already have one.
     *
     * @return string
     */
    abstract protected function getExtension(): string;

    /**
     * Parser used to convert the located file into an array.
     *
     * @return ParserInterface
     */
    abstract protected function getParser(): ParserInterface;

    /**
     * Resolves a resource as a direct file path (no search paths configured).
     * An existing but unreadable file is still returned, so the parser can
     * report it with a FileException instead of it being silently skipped.
     *
     * @param string $resource
     *
     * @return string|null
     */
    private function locateDirect(string $resource): ?string
    {
        return is_file($resource) ? $resource : null;
    }

    /**
     * Resolves a resource by searching the configured paths, unless it's
     * already a full/absolute path.
     *
     * @param string $resource
     *
     * @return string|null
     */
    private function locateInPaths(string $resource): ?string
    {
        if ($resource === '') {
            return null;
        }

        if ($this->isFullPath($resource)) {
            return is_file($resource) ? $resource : null;
        }

        foreach ($this->paths as $path) {
            $file = "{$path}{$resource}";
            if (is_file($file)) {
                return $file;
            }
        }

        return null;
    }

    /**
     * Check if the file/path is given with absolute path.
     *
     * @param string $path
     *
     * @return bool
     */
    private function isFullPath(string $path): bool
    {
        // *nix style
        if ($path[0] === '/') {
            return true;
        }

        // windows style
        if (preg_match("#[A-Z]:\\.+#U", $path)) {
            return true;
        }

        return false;
    }
}
