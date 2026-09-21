<?php

declare(strict_types=1);

/**
 * File locater
 */

namespace SugiPHP\Config;

/**
 * File Locator searches for a file in registered search paths.
 */
class FileLocator implements LocatorInterface
{
    /**
     * Search for a file in one or more directories.
     * @var array
     */
    protected $paths;

    /**
     * File Locator creator.
     *
     * @param array|string $paths
     *
     * @return void
     */
    public function __construct($paths)
    {
        $this->pushPaths($paths);
    }

    /**
     * {@inheritdoc}
     */
    public function locate($resource)
    {
        // empty string
        if (empty($resource)) {
            return ;
        }
        if ($this->isFullPath($resource)) {
            if (is_file($resource)) {
                return $resource;
            }
        } else {
            foreach ($this->paths as $path) {
                $file = "{$path}{$resource}";
                if (is_file($file)) {
                    return $file;
                }
            }
        }
    }

    /**
     * Adds a search paths.
     *
     * @param string|array $path or several paths
     *
     * @return void
     *
     * @deprecated since 2.0.0, mutating the search paths after construction will
     *             be removed in a future version. Pass all search paths to the
     *             constructor instead.
     */
    public function addPath($path)
    {
        @trigger_error(
            sprintf('%s::addPath() is deprecated since 2.0.0 and will be removed in a future version.', self::class),
            E_USER_DEPRECATED
        );

        $this->pushPaths($path);
    }

    /**
     * Remove last search path.
     *
     * @return void
     *
     * @deprecated since 2.0.0, mutating the search paths after construction will
     *             be removed in a future version.
     */
    public function popPath()
    {
        @trigger_error(
            sprintf('%s::popPath() is deprecated since 2.0.0 and will be removed in a future version.', self::class),
            E_USER_DEPRECATED
        );

        array_pop($this->paths);
    }

    /**
     * @deprecated since 2.0.0, use unshiftPath() instead. Will be removed in a
     *             future version.
     */
    public function prependPath($path)
    {
        @trigger_error(
            sprintf(
                '%s::prependPath() is deprecated since 2.0.0, use %s::unshiftPath() instead. It will be removed in a future version.',
                self::class,
                self::class
            ),
            E_USER_DEPRECATED
        );

        $this->pushPathToFront($path);
    }

    /**
     * Prepends one path to the beginning of the search paths.
     *
     * @param string $path
     *
     * @return void
     *
     * @deprecated since 2.0.0, mutating the search paths after construction will
     *             be removed in a future version. Pass all search paths to the
     *             constructor instead.
     */
    public function unshiftPath($path)
    {
        @trigger_error(
            sprintf('%s::unshiftPath() is deprecated since 2.0.0 and will be removed in a future version.', self::class),
            E_USER_DEPRECATED
        );

        $this->pushPathToFront($path);
    }

    /**
     * Remove first path from the search paths.
     *
     * @return void
     *
     * @deprecated since 2.0.0, mutating the search paths after construction will
     *             be removed in a future version.
     */
    public function shiftPath()
    {
        @trigger_error(
            sprintf('%s::shiftPath() is deprecated since 2.0.0 and will be removed in a future version.', self::class),
            E_USER_DEPRECATED
        );

        array_shift($this->paths);
    }

    /**
     * Adds one or more search paths to the end of the search paths, without
     * triggering the addPath() deprecation notice.
     *
     * @param string|array $path or several paths
     *
     * @return void
     */
    protected function pushPaths($path)
    {
        $paths = (array) $path;
        foreach ($paths as $path) {
            $this->paths[] = rtrim($path, "\\/") . DIRECTORY_SEPARATOR;
        }
    }

    /**
     * Adds one search path to the beginning of the search paths, without
     * triggering the unshiftPath()/prependPath() deprecation notice.
     *
     * @param string $path
     *
     * @return void
     */
    protected function pushPathToFront($path)
    {
        array_unshift($this->paths, rtrim($path, "\\/") . DIRECTORY_SEPARATOR);
    }

    /**
     * Returns all registered search paths.
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Check if the file/path is given with absolute path.
     *
     * @param string $path
     *
     * @return bool
     */
    protected function isFullPath($path)
    {
        // *nix style
        if ($path[0] == "/") {
            return true;
        }

        // windows style
        if (preg_match("#[A-Z]:\\.+#U", $path)) {
            return true;
        }

        return false;
    }
}
