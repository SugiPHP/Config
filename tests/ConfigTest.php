<?php

declare(strict_types=1);

/**
 * Tests for Config class: a dispatching facade over FileConfig and
 * DirectoryConfig, based on the path passed to its constructor.
 */

namespace SugiPHP\Config\Tests;

use SugiPHP\Config\Config;
use SugiPHP\Config\ConfigInterface;
use SugiPHP\Config\Exception\ConfigException;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testConfigImplementsConfigInterface()
    {
        $this->assertInstanceOf(ConfigInterface::class, new Config(__DIR__."/directory"));
    }

    public function testFilePathUsesFileConfig()
    {
        $config = new Config(__DIR__."/config/test.php");

        $this->assertSame(42, $config->get("int"));
        $this->assertSame("subvalue", $config->get("arr.sub"));
    }

    public function testDirectoryPathUsesDirectoryConfig()
    {
        $config = new Config(__DIR__."/directory");

        $this->assertEquals(include __DIR__."/directory/test.php", $config->get("test"));
        $this->assertSame(42, $config->get("test.int"));
    }

    public function testGetReturnsNullIfNotFound()
    {
        $config = new Config(__DIR__."/directory");
        $this->assertNull($config->get("foo"));
        $this->assertNull($config->get("foo.bar"));
        $this->assertSame("default", $config->get("foo.bar", "default"));
    }

    public function testHasReturnsFalseWhenNotFound()
    {
        $config = new Config(__DIR__."/directory");
        $this->assertFalse($config->has("foo"));
        $this->assertFalse($config->has("foo.bar"));
    }

    public function testHasTriggersDiscoveryLikeGet()
    {
        $config = new Config(__DIR__."/directory");

        // has() must find the key without a prior get() call loading the file first
        $this->assertTrue($config->has("test.int"));
        $this->assertFalse($config->has("test.nosuchkey"));
        $this->assertFalse($config->has("nosuchfile.int"));
    }

    public function testHasAgreesWithGet()
    {
        $config = new Config(__DIR__."/directory");

        $this->assertSame($config->has("test.int"), !is_null($config->get("test.int")));
    }

    public function testInvalidPathThrows()
    {
        $this->expectException(ConfigException::class);
        new Config(__DIR__."/no-such-file-or-directory");
    }
}
