<?php
/**
 * @package    SugiPHP
 * @subpackage Config
 * @category   tests
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

use SugiPHP\Config\NativeLoader as Loader;

class NativeLoaderTest extends PHPUnit_Framework_TestCase
{
	public function testNativeLoaderIsLoaderInterface()
	{
		$loader = new Loader();
		$this->assertInstanceOf("\SugiPHP\Config\LoaderInterface", $loader);
	}

	public function testAddPhp()
	{
		$loader = new Loader();

	}

	
}
