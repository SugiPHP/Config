<?php

declare(strict_types=1);

/**
 * Loader interface. All loaders must implement this interface.
 */

namespace SugiPHP\Config;

/**
 * Loader Interface
 */
interface LoaderInterface
{
    /**
     * Tries to load a resource.
     *
     * @param string $resource
     *
     * @return array|null Returns NULL if the resource was not found
     */
    public function load($resource);
}
