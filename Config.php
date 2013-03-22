<?php
/**
 * @package    SugiPHP
 * @subpackage Config
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Config;

class Config
{
	protected $registry = array();
	protected $loaders = array();

	/**
	 * Creates a Config instance
	 * @param array|LoaderInterface|null $loaders array of LoaderInterface
	 */
	public function __construct($loaders = null)
	{
		if (!is_null($loaders)) {
			if (is_array($loaders)) {
				foreach ($loaders as $loader) {
					$this->addLoader($loader);
				}
			} else {
				$this->addLoader($loaders);
			}
		}
	}

	public function addLoader(LoaderInterface $loader)
	{
		$this->loaders[] = $loader;
	}

	/**
	 * Returns loaded config option. If the key is not found it checks if registered loaders can
	 * find the key.
	 * 
	 * @param  string $key
	 * @return mixed
	 */
	public function get($key)
	{
		$parts = explode(".", $key);
		$file = array_shift($parts);

		if (!isset($this->registry[$file])) {
			$this->registry[$file] = $this->discover($file);
		}

		return $this->parse($key);
	}

	/**
	 * Registers a config variable
	 * 
	 * @param string $key
	 * @param mixed $value
	 */
	public function set($key, $value)
	{
		if (strpos($key, ".") === false) {
			$this->registry[$key] = $value;
		} else {
			throw new Exception("Storing keys with dot notation currently is not supported");
			// TODO: this should be rethought! Do we need this? How not to overwrite already stored arrays...
			$segments = explode(".", $key);
			do {
				$value = array(array_pop($segments) => $value);
			} while ($segments);

			$this->registry = array_merge_recursive($this->registry, $value);
		}
	}

	/**
	 * Tries to find needed resource by looping each of registered loaders.
	 * 
	 * @param  string $resource
	 * @return array
	 */
	protected function discover($resource)
	{
		foreach ($this->loaders as $loader) {
			$res = $loader->load($resource);
			if (!is_null($res)) {
				return $res;
			}
		}
	}

	/**
	 * Search for a key with dot notation in the array. If the key is not NULL is returned
	 * 
	 * @param string $key
	 * @return mixed
	 */
	protected function parse($key)
	{
		$values = $this->registry;
		$parts = explode(".", $key);
		foreach ($parts as $part) {
			if ($part === "") {
				return $values;
			}
			if (!is_array($values) or !array_key_exists($part, $values)) {
				return ;
			}
			$values = $values[$part];
		}

		return $values;
	}
}
