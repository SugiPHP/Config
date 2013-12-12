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
use SugiPHP\Config\IniLoader;

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

	public function test3Loaders()
	{
		$locator = new FileLocator(array(__DIR__, __DIR__."/config"));
		$loader[] = new IniLoader($locator); // INI loader is FIRST.
		$loader[] = new JsonLoader($locator);
		$loader[] = new NativeLoader($locator);

		$config = new Config($loader);

		// in INI there is no key int
		$this->assertNull($config->get("test.int"));
		// it's iint
		$this->assertNotSame(42, $config->get("test.iint"));
		// it is "42", not 42
		$this->assertEquals(42, $config->get("test.iint"));
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
		$arr = array("value", "value2");
		$config->set("foo", $arr);
		$this->assertEquals($arr, $config->get("foo"));
		$this->assertEquals("value", $config->get("foo.0"));
		$this->assertEquals("value2", $config->get("foo.1"));

		// arrays are not equal if they appear not in the right order
		$this->assertNotEquals(array_reverse($arr), $config->get("foo"));
	}

	public function testSetHash()
	{
		$config = new Config();
		$arr = array("key" => "value", "key2" => "value2");
		$config->set("foo", $arr);
		$this->assertEquals($arr, $config->get("foo"));
		$this->assertEquals($arr["key"], $config->get("foo.key"));
		$this->assertNull($config->get("foo.key.sub"));
		$this->assertNull($config->get("foo.bar"));

		// arrays (hashes) are equal if they appear not in the right order
		$this->assertEquals(array_reverse($arr), $config->get("foo"));
	}

	public function testSetDeepArray()
	{
		$config = new Config();
		$arr = array("key" => "value", "arr" => array("sub1" => "one", "arr2" => array("sub2" => "two", "sub5" => "five")));
		$config->set("foo", $arr);
		$this->assertEquals($arr, $config->get("foo"));
		$this->assertEquals($arr["key"], $config->get("foo.key")); // "value"
		$this->assertEquals($arr["arr"], $config->get("foo.arr")); // array("sub1" => "one", "arr2" => array("sub2" => "two", "sub5" => "five"))
		$this->assertEquals("five", $config->get("foo.arr.arr2.sub5"));
	}

	public function testSetWithDotNotation()
	{
		$config = new Config();
		$config->set("foo", "foobar");
		$this->assertSame("foobar", $config->get("foo"));
		$config->set("foo.bar", "baz");
		$this->assertSame("baz", $config->get("foo.bar"));
		$this->assertSame(array("bar" => "baz"), $config->get("foo"));
		$config->set("foo.bee", "honey");
		$this->assertSame("baz", $config->get("foo.bar"));
		$this->assertSame("honey", $config->get("foo.bee"));
		// Equals, not same
		$this->assertEquals(array("bar" => "baz", "bee" => "honey"), $config->get("foo"));
		$this->assertEquals(array("bee" => "honey", "bar" => "baz"), $config->get("foo"));
	}

	public function testSetWithDotNotationDeep()
	{
		$config = new Config();
		$config->set("foo.bar.baz.bee", "honey");
		$this->assertEquals(array("bar" => array("baz" => array("bee" => "honey"))), $config->get("foo"));
		$config->set("foo.bar.baz.two", 2);
		$this->assertEquals(array("bar" => array("baz" => array("bee" => "honey", "two" => 2))), $config->get("foo"));
		$config->set("foo.bar.baz", "BAZ");
		$this->assertEquals(array("bar" => array("baz" => "BAZ")), $config->get("foo"));
	}

	public function testSetWithDotNotationDeepFromBegining() {
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

	public function testSetWithConfigFileFirstLoadingFile()
	{
		$locator = new FileLocator(__DIR__."/config");
		$loader = new NativeLoader($locator);
		$config = new Config($loader);

		$config->get("test");
		$config->set("test.some.value", "foo");
		$this->assertSame(42, $config->get("test.int"));
		$this->assertSame("foo", $config->get("test.some.value"));
	}

	public function testSetWithConfigFileFirstSettingSomeValue()
	{
		$locator = new FileLocator(__DIR__."/config");
		$loader = new NativeLoader($locator);
		$config = new Config($loader);

		$config->set("test.some.value", "foo");
		$this->assertSame(42, $config->get("test.int"));
		$this->assertSame("foo", $config->get("test.some.value"));
	}

	public function testSetWithConfigFileOverridingValue()
	{
		$locator = new FileLocator(__DIR__."/config");
		$loader = new NativeLoader($locator);
		$config = new Config($loader);

		$config->set("test.int", 7);
		$this->assertSame(7, $config->get("test.int"));
	}

	public function testSetWithNoKey()
	{
		$config = new Config();

		$this->setExpectedException("SugiPHP\Config\Exception");
		$config->set(null, 1);
	}
}
