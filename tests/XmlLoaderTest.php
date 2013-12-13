<?php
/**
 * @package    SugiPHP
 * @subpackage Config
 * @category   tests
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

use SugiPHP\Config\XmlLoader as Loader;
use SugiPHP\Config\FileLocator as Locator;

class XmlLoaderTest extends PHPUnit_Framework_TestCase
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
		$this->assertNull($loader->load("nosuchfile.xml"));
	}

	public function testAddExtension()
	{
		$loader = new Loader();
		$this->assertEquals($loader->load(__DIR__."/config/test"), $loader->load(__DIR__."/config/test.xml"));
	}

	public function testLoaderWithLocator()
	{
		$testArr = array("str" => "value", "arr" => array(array("sub" => "subvalue"), "nokey"), "null" => "null", "int" => "42", "zero" => "0", "false" => "false", "true" => "true");

		$locator = new Locator(array(__DIR__, __DIR__."/config"));
		$loader = new Loader($locator);
		$this->assertNull($loader->load("nosuchfile"));
		$this->assertEquals($testArr, $loader->load("config/test.xml"));
		$this->assertEquals($testArr, $loader->load("config/test"));
		$this->assertEquals($testArr, $loader->load("test.xml"));
		$this->assertEquals($testArr, $loader->load("test"));
	}
}
