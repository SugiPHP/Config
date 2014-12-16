<?php
/**
 * XML file loader.
 *
 * @package SugiPHP.Config
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Config;

class XmlLoader implements LoaderInterface
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
		// check the extension. If it's not provided we'll add .xml
		if (pathinfo($resource, PATHINFO_EXTENSION) === "") {
			$resource .= ".xml";
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
			$xmlstring = file_get_contents($file);
			$xml = simplexml_load_string($xmlstring);
			$json = json_encode($xml);
			$array = json_decode($json, true);

			return $array;
		}

		return null;
	}
}
