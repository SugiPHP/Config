<?php

declare(strict_types=1);

/**
 * Tests for DirectoryConfig class.
 */

namespace SugiPHP\Config\Tests;

use SugiPHP\Config\ConfigInterface;
use SugiPHP\Config\DirectoryConfig;
use SugiPHP\Config\Exception\ConfigException;
use PHPUnit\Framework\TestCase;

class DirectoryConfigTest extends TestCase
{
    public function testDirectoryConfigImplementsConfigInterface()
    {
        $this->assertInstanceOf(ConfigInterface::class, new DirectoryConfig(__DIR__."/config"));
    }

    public function testConstructThrowsWhenDirectoryDoesNotExist()
    {
        $this->expectException(ConfigException::class);
        new DirectoryConfig(__DIR__."/no-such-directory");
    }

    public function testConstructAcceptsSingleDirectoryStringWithoutWarnings()
    {
        set_error_handler(function ($errno, $errstr) {
            $this->fail("Unexpected PHP warning/notice: {$errstr}");
        });

        try {
            new DirectoryConfig(__DIR__."/config");
        } finally {
            restore_error_handler();
        }

        $this->assertTrue(true);
    }

    public function testConstructAcceptsArrayOfDirectories()
    {
        $config = new DirectoryConfig([__DIR__."/config", __DIR__."/config2"]);
        $this->assertInstanceOf(ConfigInterface::class, $config);
    }

    public function testConstructWithNoArgumentsIsEmpty()
    {
        $config = new DirectoryConfig();
        $this->assertInstanceOf(ConfigInterface::class, $config);
        $this->assertNull($config->get("foo"));
        $this->assertFalse($config->has("foo"));
    }

    public function testAddDirectoryAfterConstruction()
    {
        $config = new DirectoryConfig();
        $config->addDirectory(__DIR__."/config");

        $this->assertSame(42, $config->get("test.int"));
    }

    public function testGetReturnsNullIfResourceFileNotFound()
    {
        $config = new DirectoryConfig(__DIR__."/config");
        $this->assertNull($config->get("nosuchfile"));
        $this->assertNull($config->get("nosuchfile.key"));
    }

    public function testGetReturnsDefaultIfResourceFileNotFound()
    {
        $config = new DirectoryConfig(__DIR__."/config");
        $this->assertSame("default", $config->get("nosuchfile", "default"));
        $this->assertSame("default", $config->get("nosuchfile.key", "default"));
    }

    public function testHasReturnsFalseIfResourceFileNotFound()
    {
        $config = new DirectoryConfig(__DIR__."/config");
        $this->assertFalse($config->has("nosuchfile"));
        $this->assertFalse($config->has("nosuchfile.key"));
    }

    public function testGetReturnsWholeFileContents()
    {
        $config = new DirectoryConfig(__DIR__."/config");
        $this->assertEquals(include __DIR__."/config/test.php", $config->get("test"));
    }

    public function testGetResolvesTopLevelDotNotation()
    {
        $config = new DirectoryConfig(__DIR__."/config");
        $this->assertSame(42, $config->get("test.int"));
        $this->assertSame("value", $config->get("test.str"));
    }

    public function testGetResolvesNestedDotNotation()
    {
        $config = new DirectoryConfig(__DIR__."/config");
        $this->assertSame("subvalue", $config->get("test.arr.sub"));
    }

    public function testGetReturnsDefaultForMissingKeyInsideFoundFile()
    {
        $config = new DirectoryConfig(__DIR__."/config");
        $this->assertNull($config->get("test.nosuchkey"));
        $this->assertSame("default", $config->get("test.nosuchkey", "default"));
    }

    public function testHasReturnsTrueForExistingTopLevelResource()
    {
        $config = new DirectoryConfig(__DIR__."/config");
        $this->assertTrue($config->has("test"));
    }

    public function testHasReturnsTrueForExistingNestedKey()
    {
        $config = new DirectoryConfig(__DIR__."/config");
        $this->assertTrue($config->has("test.int"));
    }

    public function testHasReturnsFalseForMissingKeyInsideFoundFile()
    {
        $config = new DirectoryConfig(__DIR__."/config");
        $this->assertFalse($config->has("test.nosuchkey"));
    }

    public function testMultipleDirectoriesAreSearched()
    {
        $config = new DirectoryConfig([__DIR__."/config", __DIR__."/config2"]);
        // "test" is only in the first directory
        $this->assertSame(42, $config->get("test.int"));
        // "site" is only in the second directory
        $this->assertSame("site in config2", $config->get("site.name"));
    }

    /**
     * Documents which format wins when several exist for the same resource
     * name in one directory: DirectoryConfig's own hardcoded order of
     * php, ini, json, xml.
     */
    public function testPhpTakesPriorityOverOtherFormats()
    {
        $config = new DirectoryConfig(__DIR__."/config/priority-all");
        $this->assertSame("php", $config->get("conf.marker"));
    }

    public function testIniTakesPriorityOverJsonAndXmlWhenPhpMissing()
    {
        $config = new DirectoryConfig(__DIR__."/config/priority-no-php");
        $this->assertSame("ini", $config->get("conf.marker"));
    }

    public function testJsonTakesPriorityOverXmlWhenPhpAndIniMissing()
    {
        $config = new DirectoryConfig(__DIR__."/config/priority-json-xml-only");
        $this->assertSame("json", $config->get("conf.marker"));
    }

    public function testXmlIsUsedAsLastResort()
    {
        $config = new DirectoryConfig(__DIR__."/config/priority-xml-only");
        $this->assertSame("xml", $config->get("conf.marker"));
    }

    public function testNullValueIsTreatedAsAbsentLikeConfig()
    {
        // "test.null" exists and is explicitly null in tests/config/test.php;
        // DirectoryConfig follows Config's convention (not DotConfig's) where
        // a resolved null is treated the same as "not found".
        $config = new DirectoryConfig(__DIR__."/config");
        $this->assertSame("default", $config->get("test.null", "default"));
        $this->assertFalse($config->has("test.null"));
    }
}
