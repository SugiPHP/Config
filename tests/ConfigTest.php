<?php

declare(strict_types=1);

/**
 * Tests for Config class: a dispatching facade over FileConfig,
 * DirectoryConfig and LoaderConfig, based on what's passed to its
 * constructor.
 */

namespace SugiPHP\Config\Tests;

use SugiPHP\Config\Config;
use SugiPHP\Config\Exception\ConfigException;
use SugiPHP\Config\Loader\PhpLoader;
use SugiPHP\Config\Loader\JsonLoader;
use SugiPHP\Config\Loader\IniLoader;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
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

    public function testPhpLoader()
    {
        $loader = new PhpLoader(__DIR__."/config");
        $config = new Config($loader);

        $this->assertEquals(include __DIR__."/config/test.php", $config->get("test"));
        $this->assertSame(42, $config->get("test.int"));
    }

    public function testJsonLoader()
    {
        $loader = new JsonLoader(__DIR__."/config");
        $config = new Config($loader);

        $this->assertSame(42, $config->get("test.int"));
    }

    public function test3Loaders()
    {
        $paths = array(__DIR__, __DIR__."/config");
        $loader = array();
        $loader[] = new IniLoader($paths); // INI loader is FIRST.
        $loader[] = new JsonLoader($paths);
        $loader[] = new PhpLoader($paths);

        $config = new Config($loader);

        // in INI there is no key int
        $this->assertNull($config->get("test.int"));
        // it's iint, and with INI_SCANNER_TYPED it's a native int, same as JSON/PHP
        $this->assertSame(42, $config->get("test.iint"));
    }

    public function testHasReturnsFalseWhenNotFound()
    {
        $config = new Config();
        $this->assertFalse($config->has("foo"));
        $this->assertFalse($config->has("foo.bar"));
    }

    public function testHasTriggersDiscoveryLikeGet()
    {
        $loader = new PhpLoader(__DIR__."/config");
        $config = new Config($loader);

        // has() must find the key without a prior get() call loading the file first
        $this->assertTrue($config->has("test.int"));
        $this->assertFalse($config->has("test.nosuchkey"));
        $this->assertFalse($config->has("nosuchfile.int"));
    }

    public function testHasAgreesWithGet()
    {
        $loader = new PhpLoader(__DIR__."/config");
        $config = new Config($loader);

        $this->assertSame($config->has("test.int"), !is_null($config->get("test.int")));
    }

    public function testFilePathUsesFileConfig()
    {
        $config = new Config(__DIR__."/config/test.php");

        $this->assertSame(42, $config->get("int"));
        $this->assertSame("subvalue", $config->get("arr.sub"));
    }

    public function testDirectoryPathUsesDirectoryConfig()
    {
        $config = new Config(__DIR__."/config");

        $this->assertSame(42, $config->get("test.int"));
    }

    public function testArrayOfDirectoriesUsesDirectoryConfig()
    {
        $config = new Config([__DIR__."/config", __DIR__."/config2"]);

        // "test" is only in the first directory
        $this->assertSame(42, $config->get("test.int"));
        // "site" is only in the second directory
        $this->assertSame("site in config2", $config->get("site.name"));
    }

    public function testInvalidPathThrows()
    {
        $this->expectException(ConfigException::class);
        new Config(__DIR__."/no-such-file-or-directory");
    }

}
