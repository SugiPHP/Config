<?php
/**
 * @package    SugiPHP
 * @subpackage Config
 * @category   tests
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

use SugiPHP\Config\FileLocator as Locator;

class FileLocatorTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @return [type] [description]
	 */
	public function testCreate()
	{
		$loader = new Locator("");
	}

	public function testLocateSelfNoSearchPaths()
	{
		$loader = new Locator("");
		$this->assertEquals(__FILE__, $loader->locate(__FILE__));
	}

	public function testLocateSelfWithSearchPaths()
	{
		$loader = new Locator(array("/tmp", "/temp", __DIR__));
		$this->assertEquals(__FILE__, $loader->locate(__FILE__));
	}

	public function testLocateSelfFileName()
	{
		$loader = new Locator(__DIR__);
		$this->assertEquals(__FILE__, $loader->locate(basename(__FILE__)));
	}

	public function testLocateSelfNotFoundInUnknownDir()
	{
		$loader = new Locator(__DIR__.mt_rand());
		$this->assertNull($loader->locate(basename(__FILE__)));
	}

	public function testUnknownFileInThisDir()
	{
		$loader = new Locator(__DIR__);
		$this->assertNull($loader->locate(md5(mt_rand())));
	}

	public function testNoFile()
	{
		$loader = new Locator(__DIR__);
		$this->assertNull($loader->locate(""));
	}
}
