<?php

declare(strict_types=1);

/**
 * Tests for LoaderConfig class, mirroring the Config class test suite
 * (see ConfigTest.php) since LoaderConfig reimplements the same
 * loader-list resolution as Config, independently of it.
 */

namespace SugiPHP\Config\Tests;

use SugiPHP\Config\ConfigInterface;
use SugiPHP\Config\LoaderConfig;
use SugiPHP\Config\Loader\IniLoader;
use SugiPHP\Config\Loader\JsonLoader;
use SugiPHP\Config\Loader\PhpLoader;
use PHPUnit\Framework\TestCase;

class LoaderConfigTest extends TestCase
{
    public function testLoaderConfigImplementsConfigInterface()
    {
        $this->assertInstanceOf(ConfigInterface::class, new LoaderConfig());
    }

    public function testConstructWithNoArgumentsIsEmpty()
    {
        $config = new LoaderConfig();
        $this->assertNull($config->get("foo"));
        $this->assertFalse($config->has("foo"));
    }

    public function testConstructAcceptsSingleLoader()
    {
        $config = new LoaderConfig(new PhpLoader(__DIR__."/config"));

        $this->assertSame(42, $config->get("test.int"));
    }

    public function testConstructAcceptsArrayOfLoaders()
    {
        // mirrors ConfigTest::test3Loaders: INI loader is FIRST
        $config = new LoaderConfig([
            new IniLoader(__DIR__."/config"),
            new JsonLoader(__DIR__."/config"),
            new PhpLoader(__DIR__."/config"),
        ]);

        // in INI there is no key "int"
        $this->assertNull($config->get("test.int"));
        // it's "iint", and it is "42" (string), not 42
        $this->assertSame("42", $config->get("test.iint"));
    }

    public function testAddLoaderAfterConstruction()
    {
        $config = new LoaderConfig();
        $config->addLoader(new PhpLoader(__DIR__."/config"));

        $this->assertSame(42, $config->get("test.int"));
    }

    public function testGetReturnsNullIfResourceNotFound()
    {
        $config = new LoaderConfig(new PhpLoader(__DIR__."/config"));

        $this->assertNull($config->get("nosuchfile"));
        $this->assertNull($config->get("nosuchfile.key"));
    }

    public function testGetReturnsDefaultIfResourceNotFound()
    {
        $config = new LoaderConfig(new PhpLoader(__DIR__."/config"));

        $this->assertSame("default", $config->get("nosuchfile", "default"));
    }

    public function testHasReturnsFalseIfResourceNotFound()
    {
        $config = new LoaderConfig(new PhpLoader(__DIR__."/config"));

        $this->assertFalse($config->has("nosuchfile"));
    }

    public function testGetResolvesNestedDotNotation()
    {
        $config = new LoaderConfig(new PhpLoader(__DIR__."/config"));

        $this->assertSame("subvalue", $config->get("test.arr.sub"));
    }

    public function testHasReturnsTrueForExistingNestedKey()
    {
        $config = new LoaderConfig(new PhpLoader(__DIR__."/config"));

        $this->assertTrue($config->has("test.int"));
        $this->assertFalse($config->has("test.nosuchkey"));
    }

    public function testNullValueIsTreatedAsAbsent()
    {
        // "test.null" exists and is explicitly null in tests/config/test.php
        $config = new LoaderConfig(new PhpLoader(__DIR__."/config"));

        $this->assertSame("default", $config->get("test.null", "default"));
        $this->assertFalse($config->has("test.null"));
    }
}
