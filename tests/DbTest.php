<?php
/**
 * Tests for configurations with database.
 *
 * @package SugiPHP.Config
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Config;

use SugiPHP\Config\tests\Db1Loader;
use SugiPHP\Config\Config;
use PHPUnit_Framework_TestCase;

class DbTest extends PHPUnit_Framework_TestCase
{
	public function testOneTable()
	{
		$config = new Config(new Db1Loader());
		$this->assertNull($config->get("nosuchkey"));
		$this->assertNull($config->get("nosuchkey.foo"));
		$this->assertEquals(array("host" => "localhost", "debug" => 1), $config->get("development"));
		$this->assertEquals(array("host" => "example.com", "debug" => null), $config->get("production"));
		$this->assertSame("example.com", $config->get("production.host"));
		$this->assertSame(0, $config->get("production.debug", 0));
		$this->assertNull($config->get("production.foo"));
	}
}
