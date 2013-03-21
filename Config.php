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
	 * @param array|LoaderInterface $loaders array of LoaderInterface
	 */
	public function __construct($loaders)
	{
		$this->loaders = (array) $loaders;
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
		if (!isset($this->registry[$key])) {
			$this->registry[$key] = $this->discover($resourse);
		}

		return $this->registry[$key];
	}

	/**
	 * Registers a config variable
	 * 
	 * @param string $key
	 * @param mixed $value
	 */
	public function set($key, $value)
	{
		$this->registry[$key] = $value;
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
			try {
				return $loader->load($resource);
			} catch (Exception $e) {
				// the loader fails
			}
		}
	}
}
