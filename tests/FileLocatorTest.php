<?php
/**
 * Tests for FileLocator class.
 *
 * @package SugiPHP.Config
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Config\Tests;

use SugiPHP\Config\FileLocator as Locator;
use PHPUnit_Framework_TestCase;

class FileLocatorTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $this->assertNotNull(new Locator(""));
    }

    public function testLocateSelfNoSearchPaths()
    {
        $locator = new Locator("");
        $this->assertEquals(__FILE__, $locator->locate(__FILE__));
    }

    public function testLocateSelfWithSearchPaths()
    {
        $locator = new Locator(array("/tmp", "/temp", __DIR__));
        $this->assertEquals(__FILE__, $locator->locate(__FILE__));
    }

    public function testLocateSelfFileName()
    {
        $locator = new Locator(__DIR__);
        $this->assertEquals(__FILE__, $locator->locate(basename(__FILE__)));
    }

    public function testLocateSelfNotFoundInUnknownDir()
    {
        $locator = new Locator(__DIR__.mt_rand());
        $this->assertNull($locator->locate(basename(__FILE__)));
    }

    public function testUnknownFileInThisDir()
    {
        $locator = new Locator(__DIR__);
        $this->assertNull($locator->locate(md5(mt_rand())));
    }

    public function testNoFile()
    {
        $locator = new Locator(__DIR__);
        $this->assertNull($locator->locate(""));
    }

    public function testDontFindTestFile()
    {
        $locator = new Locator(__DIR__);
        $this->assertNull($locator->locate("test.php"));
    }

    public function testFindTestFileOneSearchPath()
    {
        $locator = new Locator(__DIR__."/config");
        $this->assertEquals(__DIR__."/config/test.php", $locator->locate("test.php"));
    }

    public function testFindTestFileTwoSearchPaths()
    {
        $locator = new Locator(array(__DIR__, __DIR__."/config"));
        $this->assertEquals(__DIR__."/config/test.php", $locator->locate("test.php"));
    }

    public function testAddPath()
    {
        $locator = new Locator(__DIR__."/config");
        // test2.php not present in /config path
        $this->assertNull($locator->locate("test2.php"));
        // adding /config2 path
        $locator->addPath(__DIR__."/config2");
        // test2.php now present
        $this->assertEquals(__DIR__."/config2/test2.php", $locator->locate("test2.php"));
        $this->assertEquals(__DIR__."/config/test.php", $locator->locate("test.php"));
        // find first instance of test2.json
        $this->assertEquals(__DIR__."/config/test2.json", $locator->locate("test2.json"));
    }

    public function testPopPath()
    {
        $locator = new Locator(array(__DIR__."/config", __DIR__."/config2"));
        // test2.php is only in the second (config2) path
        $this->assertEquals(__DIR__."/config2/test2.php", $locator->locate("test2.php"));
        // removing second search path
        $locator->popPath();
        $this->assertNull($locator->locate("test2.php"));
        $this->assertEquals(__DIR__."/config/test.php", $locator->locate("test.php"));
        // removing first search path
        $locator->popPath();
        $this->assertNull($locator->locate("test.php"));
    }

    public function testUnshiftPath()
    {
        $locator = new Locator(__DIR__."/config");
        $this->assertNull($locator->locate("test2.php"));
        $this->assertEquals(__DIR__."/config/test2.json", $locator->locate("test2.json"));
        $locator->unshiftPath(__DIR__."/config2");
        $this->assertEquals(__DIR__."/config2/test2.php", $locator->locate("test2.php"));
        $this->assertEquals(__DIR__."/config2/test2.json", $locator->locate("test2.json"));
    }

    public function testShiftPath()
    {
        $locator = new Locator(array(__DIR__."/config", __DIR__."/config2"));
        $this->assertEquals(__DIR__."/config/test2.json", $locator->locate("test2.json"));
        $locator->shiftPath();
        $this->assertEquals(__DIR__."/config2/test2.json", $locator->locate("test2.json"));
    }
}
