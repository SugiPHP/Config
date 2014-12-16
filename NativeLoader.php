<?php
/**
 * PHP file loader.
 *
 * @package SugiPHP.Config
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Config;

class NativeLoader implements LoaderInterface
{
	protected $locator;

	public function __construct(LocatorInterface $locator = null)
	{
		$this->locator = $locator;
	}

	/**
	 * {@inheritdoc}
	 */
	public function load($resource)
	{
		// check the extension. If it's not provided we'll add .php
		if (pathinfo($resource, PATHINFO_EXTENSION) === "") {
			$resource .= ".php";
		}

		$file = false;

		if ($this->locator) {
			// pass it to the locator (if set) and than include the file
			$file = $this->locator->locate($resource);
		} elseif (is_file($resource) && is_readable($resource)) {
			// check if the $resource is a real file and include it
			$file = $resource;
		}

		if ($file) {
			return include $file;
		}

		return null;
	}
}
