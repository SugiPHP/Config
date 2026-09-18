<?php

declare(strict_types=1);

/**
 * Tests for Config class.
 */

namespace SugiPHP\Config\Tests;

use SugiPHP\Config\Config;
use SugiPHP\Config\FileLocator;
use SugiPHP\Config\NativeLoader;
use SugiPHP\Config\JsonLoader;
use SugiPHP\Config\IniLoader;
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

    public function testNativeLoader()
    {
        $locator = new FileLocator(__DIR__."/config");
        $loader = new NativeLoader($locator);
        $config = new Config($loader);

        $this->assertEquals(include __DIR__."/config/test.php", $config->get("test"));
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
        $loader = array();
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

    public function testHasReturnsFalseWhenNotFound()
    {
        $config = new Config();
        $this->assertFalse($config->has("foo"));
        $this->assertFalse($config->has("foo.bar"));
    }

    public function testHasTriggersDiscoveryLikeGet()
    {
        $locator = new FileLocator(__DIR__."/config");
        $loader = new NativeLoader($locator);
        $config = new Config($loader);

        // has() must find the key without a prior get() call loading the file first
        $this->assertTrue($config->has("test.int"));
        $this->assertFalse($config->has("test.nosuchkey"));
        $this->assertFalse($config->has("nosuchfile.int"));
    }

    public function testHasAgreesWithGet()
    {
        $locator = new FileLocator(__DIR__."/config");
        $loader = new NativeLoader($locator);
        $config = new Config($loader);

        $this->assertSame($config->has("test.int"), !is_null($config->get("test.int")));
    }
}
