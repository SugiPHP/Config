<?php
/**
 * @package    SugiPHP
 * @subpackage Config
 * @category   tests
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

use SugiPHP\Config\JsonLoader as Loader;
use SugiPHP\Config\FileLocator as Locator;

class JsonLoaderTest extends PHPUnit_Framework_TestCase
{
	public function testJsonLoaderIsLoaderInterface()
	{
		$loader = new Loader();
		$this->assertInstanceOf("\SugiPHP\Config\LoaderInterface", $loader);
	}

	public function testReturnsNull()
	{
		$loader = new Loader();
		$this->assertNull($loader->load("nosuchfile"));
		$this->assertNull($loader->load("nosuchfile.json"));
	}

	public function testAddPhp()
	{
		$loader = new Loader();
		$testArr = include(__DIR__."/config/test.php");
		$this->assertEquals($testArr, $loader->load(__DIR__."/config/test.json"));
		$this->assertEquals($testArr, $loader->load(__DIR__."/config/test"));
	}
	
	public function testLoaderWithLocator()
	{
		$testArr = include(__DIR__."/config/test.php");

		$locator = new Locator(array(__DIR__, __DIR__."/config"));
		$loader = new Loader($locator);
		$this->assertNull($loader->load("nosuchfile"));
		$this->assertEquals($testArr, $loader->load("config/test.json"));
		$this->assertEquals($testArr, $loader->load("config/test"));
		$this->assertEquals($testArr, $loader->load("test.json"));
		$this->assertEquals($testArr, $loader->load("test"));
	}	
}
