<?php
/**
 * @package    SugiPHP
 * @subpackage Config
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Config;

use Symfony\Component\Yaml\Yaml;

class YamlLoader implements LoaderInterface
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
			$resource .= ".yml";
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
			return Yaml::parse($file);
		}

		return null;
	}
}
