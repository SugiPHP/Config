<?php
/**
 * @package    SugiPHP
 * @subpackage Config
 * @category   tests
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

use SugiPHP\Config\Config;
use SugiPHP\Config\FileLocator;
use SugiPHP\Config\NativeLoader;
use SugiPHP\Config\JsonLoader;

class ConfigTest extends PHPUnit_Framework_TestCase
{
	public function testConfigCreate()
	{
		$this->assertInstanceOf("SugiPHP\Config\Config", new Config());
	}

	public function testGetRetrunsNullIfNotFound()
	{
		$config = new Config();
		$this->assertNull($config->get("foo"));
		$this->assertNull($config->get("foo.bar"));
	}

	public function testSetVar()
	{
		$config = new Config();
		$config->set("foo", "bar");
		$this->assertEquals("bar", $config->get("foo"));
		$config->set("foo", "baz");
		$this->assertEquals("baz", $config->get("foo"));
	}

	public function testSetArray()
	{
		$config = new Config();
		$arr = array("key" => "value", "key2" => "value2");
		$config->set("foo", $arr);
		$this->assertEquals($arr, $config->get("foo"));
		$this->assertEquals($arr["key"], $config->get("foo.key"));
		$this->assertNull($config->get("foo.key.sub"));
		$this->assertNull($config->get("foo.bar"));
	}

	public function testSetDeepArray()
	{
		$config = new Config();
		$arr = array("key" => "value", "arr" => array("sub1" => "one", "arr2" => array("sub2" => "two", "sub5" => "five")));
		$config->set("foo", $arr);
		$this->assertEquals($arr, $config->get("foo"));
		$this->assertEquals($arr["key"], $config->get("foo.key"));
		$this->assertEquals($arr["arr"], $config->get("foo.arr"));
		$this->assertEquals("five", $config->get("foo.arr.arr2.sub5"));
	}

	public function testNativeLoader()
	{
		$locator = new FileLocator(__DIR__."/config");
		$loader = new NativeLoader($locator);
		$config = new Config($loader);

		$this->assertEquals(include(__DIR__."/config/test.php"), $config->get("test"));
		$this->assertSame(42, $config->get("test.int"));
	}

	public function testJsonLoader()
	{
		$locator = new FileLocator(__DIR__."/config");
		$loader = new JsonLoader($locator);
		$config = new Config($loader);

		$this->assertSame(42, $config->get("test.int"));
	}

/*	
	public function testSetWithDotNotation()
	{
		$config = new Config();
		$config->set("foo.bar", "baz");
		$this->assertNull($config->get("baz"));
		$this->assertEquals("baz", $config->get("foo.bar"));
		$this->assertEquals(array("bar" => "baz"), $config->get("foo"));
	}

	public function testSetWithDotNotationDeep()
	{
		$config = new Config();
		$config->set("foo", "foovalue");
		$config->set("foo.bar", "barvalue");
		$config->set("foo.bar.baz", "bazvalue");
		$this->assertEquals("bazvalue", $config->get("foo.bar.baz"));
		$this->assertEquals(array("baz" => "bazvalue"), $config->get("foo.bar"));
		$this->assertEquals(array("bar" => array("baz" => "bazvalue")), $config->get("foo"));
		$config->set("foo.bar", "newbar");
		$this->assertEquals("newbar", $config->get("foo.bar"));
		$config->set("foo", "newfoo");
		$this->assertEquals("newfoo", $config->get("foo"));
		$this->assertNull($config->get("foo.bar"));
		$this->assertNull($config->get("foo.bar.baz"));
	}
*/
}
