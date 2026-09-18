<?php

declare(strict_types=1);

/**
 * Locator interface. All locators must implement this interface.
 */

namespace SugiPHP\Config;

/**
 * Locator Interface
 */
interface LocatorInterface
{
    /**
     * Search for a particular resource.
     *
     * @param string $resource
     *
     * @return string|null
     */
    public function locate($resource);
}
