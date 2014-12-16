<?php
/**
 * Tests for IniLoader class.
 *
 * @package SugiPHP.Config
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Config;

use SugiPHP\Config\IniLoader as Loader;
use SugiPHP\Config\FileLocator as Locator;
use PHPUnit_Framework_TestCase;

class IniLoaderTest extends PHPUnit_Framework_TestCase
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
		$this->assertNull($loader->load("nosuchfile.ini"));
	}

	public function testAddExtension()
	{
		$testArr = array("istr" => "value", "iarr" => array("sub" => "subvalue", "nokey"), "inull" => null, "iint" => 42, "izero" => 0, "ifalse" => false, "itrue" => true);

		$loader = new Loader();
		$this->assertEquals($testArr, $loader->load(__DIR__."/config/test.ini"));
		$this->assertEquals($testArr, $loader->load(__DIR__."/config/test"));
	}

	public function testLoaderWithLocator()
	{
		$testArr = array("istr" => "value", "iarr" => array("sub" => "subvalue", "nokey"), "inull" => null, "iint" => 42, "izero" => 0, "ifalse" => false, "itrue" => true);

		$locator = new Locator(array(__DIR__, __DIR__."/config"));
		$loader = new Loader($locator);
		$this->assertNull($loader->load("nosuchfile"));
		$this->assertEquals($testArr, $loader->load("config/test.ini"));
		$this->assertEquals($testArr, $loader->load("config/test"));
		$this->assertEquals($testArr, $loader->load("test.ini"));
		$this->assertEquals($testArr, $loader->load("test"));
	}
}
