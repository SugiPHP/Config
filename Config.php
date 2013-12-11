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
	 * @param  mixed $default - the value to be returned if the $key is not found
	 * @return mixed
	 */
	public function get($key = null, $default = null)
	{
		if (is_null($key)) {
			return $this->registry;
		}

		$parts = explode(".", $key);
		$file = array_shift($parts);

		if (!isset($this->registry[$file])) {
			$this->registry[$file] = $this->discover($file);
		}
		$res = $this->parse($key);

		return is_null($res) ? $default : $res;
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
			// we'll try to load a configuration file (if exists)
			if (!isset($this->registry[$key])) {
				$this->registry[$key] = $this->discover($key);
			}

			if (isset($this->registry[$key]) && is_array($this->registry[$key]) && (is_array($value))) {
				$this->registry[$key] = array_replace_recursive($this->registry[$key], $value);
			} else {
				$this->registry[$key] = $value;
			}
		} else {
			$segments = explode(".", $key);
			$v = array(array_pop($segments) => $value);
			$this->set(implode(".", $segments), $v);
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
	 * Search for a key with dot notation in the array. If the key is not found NULL is returned
	 *
	 * @param  string $key
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
