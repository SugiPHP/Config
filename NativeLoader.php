<?php
/**
 * @package    SugiPHP
 * @subpackage Config
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Config;

class NativeLoader implements LoaderInterface
{
	protected $locator;

	public function __construct(LocatorInterface $locator = null)
	{
		$this->locator = $locator;
	}

	public function load($resource)
	{
		// check the extension. If it's not provided we'll add .php
		if (pathinfo($resource, PATHINFO_EXTENSION) === "") {
			$resource .= ".php";
		}

		$file = false;

		// TODO: check if the $resource is a real file with absolute path
		
		if ($this->locator) {
			// pass it to the locator (if set) and than include the file
			$files = $this->locator->locate($resource);
			if (count($files) > 0) {
				$file = $files[0];
			}
		}
		elseif (is_file($resource) and is_readable($resource)) {
			// include it
			$file = $resource;
		}

		if ($file) {
			return include $file;
		}

		return null;
	}
}
