<?php
/**
 * File locater
 *
 * @package SugiPHP.Config
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
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
        $this->addPath($paths);
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
     */
    public function addPath($path)
    {
        $paths = (array) $path;
        foreach ($paths as $path) {
            $this->paths[] = rtrim($path, "\\/") . DIRECTORY_SEPARATOR;
        }
    }

    /**
     * Remove last search path.
     *
     * @return void
     */
    public function popPath()
    {
        array_pop($this->paths);
    }

    /**
     * @deprecated Use unshiftPath() method
     */
    public function prependPath($path)
    {
        return $this->unshiftPath($path);
    }

    /**
     * Prepends one path to the beginning of the search paths.
     *
     * @param string $path
     *
     * @return void
     */
    public function unshiftPath($path)
    {
        array_unshift($this->paths, rtrim($path, "\\/") . DIRECTORY_SEPARATOR);
    }

    /**
     * Remove first path from the search paths.
     *
     * @return void
     */
    public function shiftPath()
    {
        array_shift($this->paths);
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
