<?php
/**
 * @package    SugiPHP
 * @subpackage Config
 * @category   tests
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Config;

use SugiPHP\Config\NativeLoader as Loader;
use SugiPHP\Config\FileLocator as Locator;
use PHPUnit_Framework_TestCase;

class NativeLoaderTest extends PHPUnit_Framework_TestCase
{
	public function testNativeLoaderIsLoaderInterface()
	{
		$loader = new Loader();
		$this->assertInstanceOf("\SugiPHP\Config\LoaderInterface", $loader);
	}

	public function testReturnsNull()
	{
		$loader = new Loader();
		$this->assertNull($loader->load("nosuchfile"));
		$this->assertNull($loader->load("nosuchfile.php"));
	}

	public function testAddPhp()
	{
		$loader = new Loader();
		$testArr = include(__DIR__."/config/test.php");
		$this->assertEquals($testArr, $loader->load(__DIR__."/config/test.php"));
		$this->assertEquals($testArr, $loader->load(__DIR__."/config/test"));
	}

	public function testLoaderWithLocator()
	{
		$testArr = include(__DIR__."/config/test.php");

		$locator = new Locator(array(__DIR__, __DIR__."/config"));
		$loader = new Loader($locator);
		$this->assertNull($loader->load("nosuchfile"));
		$this->assertEquals($testArr, $loader->load("config/test.php"));
		$this->assertEquals($testArr, $loader->load("config/test"));
		$this->assertEquals($testArr, $loader->load("test.php"));
		$this->assertEquals($testArr, $loader->load("test"));
	}
}
