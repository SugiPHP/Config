<?php
/**
 * Locator interface. All locators must implement this interface.
 *
 * @package SugiPHP.Config
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
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
