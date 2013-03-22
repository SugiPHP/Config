<?php
/**
 * @package    SugiPHP
 * @subpackage Config
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Config;

class IniLoader implements LoaderInterface
{
	protected $locator;

	public function __construct(LocatorInterface $locator = null)
	{
		$this->locator = $locator;
	}

	public function load($resource)
	{
		// check the extension. If it's not provided we'll add .ini
		if (pathinfo($resource, PATHINFO_EXTENSION) === "") {
			$resource .= ".ini";
		}

		$file = false;

		if ($this->locator) {
			// pass it to the locator (if set) and than include the file
			$file = $this->locator->locate($resource);
		} elseif (is_file($resource) and is_readable($resource)) {
			// check if the $resource is a real file and include it
			$file = $resource;
		}

		if ($file) {
			// By setting the process_sections parameter (second param) to TRUE, you get a 
			// multidimensional array, with the section names and settings included. 
			// The default for process_sections is FALSE
			return parse_ini_file($file, true);
		}

		return null;
	}
}
