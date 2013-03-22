<?php
/**
 * @package    SugiPHP
 * @subpackage Config
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
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
	 * @param  string $resource
	 * @return array|NULL - NULL if the resource was not found
	 */
	public function load($resource);
}
