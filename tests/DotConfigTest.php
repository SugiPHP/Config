<?php

declare(strict_types=1);

/**
 * Tests for DotConfig class.
 */

namespace SugiPHP\Config\Tests;

use SugiPHP\Config\ConfigInterface;
use SugiPHP\Config\DotConfig;
use PHPUnit\Framework\TestCase;

class DotConfigTest extends TestCase
{
    public function testImplementsConfigInterface()
    {
        $this->assertInstanceOf(ConfigInterface::class, new DotConfig([]));
    }

    public function testGetReturnsNullIfNotFoundAndNoDefaultGiven()
    {
        $config = new DotConfig([]);
        $this->assertNull($config->get("foo"));
        $this->assertNull($config->get("foo.bar"));
    }

    public function testGetReturnsDefaultIfNotFound()
    {
        $config = new DotConfig([]);
        $this->assertSame("default", $config->get("foo", "default"));
        $this->assertSame("default", $config->get("foo.bar", "default"));
    }

    public function testGetReturnsTopLevelValue()
    {
        $config = new DotConfig(["host" => "localhost"]);
        $this->assertSame("localhost", $config->get("host"));
    }

    public function testGetReturnsNestedValueWithDotNotation()
    {
        $config = new DotConfig(["db" => ["host" => "localhost"]]);
        $this->assertSame("localhost", $config->get("db.host"));
    }

    public function testGetReturnsDeeplyNestedValue()
    {
        $config = new DotConfig(["a" => ["b" => ["c" => "d"]]]);
        $this->assertSame("d", $config->get("a.b.c"));
    }

    public function testGetReturnsWholeSubArray()
    {
        $arr = ["host" => "localhost", "port" => 5432];
        $config = new DotConfig(["db" => $arr]);
        $this->assertSame($arr, $config->get("db"));
    }

    public function testGetReturnsDefaultWhenPartialPathMissing()
    {
        $config = new DotConfig(["db" => ["host" => "localhost"]]);
        $this->assertNull($config->get("db.port"));
        $this->assertSame("default", $config->get("db.port", "default"));
    }

    public function testGetReturnsDefaultWhenIntermediateValueIsNotArray()
    {
        $config = new DotConfig(["db" => "localhost"]);
        $this->assertNull($config->get("db.host"));
        $this->assertSame("default", $config->get("db.host", "default"));
    }

    public function testGetReturnsActualNullValueNotDefault()
    {
        $config = new DotConfig(["foo" => null]);
        // the key exists and its value is null, so null (not "default") is returned
        $this->assertNull($config->get("foo", "default"));
    }

    public function testGetOnEmptyKey()
    {
        $config = new DotConfig(["" => "root"]);
        $this->assertSame("root", $config->get(""));
    }

    public function testHasReturnsFalseIfNotFound()
    {
        $config = new DotConfig([]);
        $this->assertFalse($config->has("foo"));
        $this->assertFalse($config->has("foo.bar"));
    }

    public function testHasReturnsTrueForTopLevelKey()
    {
        $config = new DotConfig(["host" => "localhost"]);
        $this->assertTrue($config->has("host"));
    }

    public function testHasReturnsTrueForNestedKey()
    {
        $config = new DotConfig(["db" => ["host" => "localhost"]]);
        $this->assertTrue($config->has("db.host"));
        $this->assertTrue($config->has("db"));
    }

    public function testHasReturnsFalseWhenPartialPathMissing()
    {
        $config = new DotConfig(["db" => ["host" => "localhost"]]);
        $this->assertFalse($config->has("db.port"));
    }

    public function testHasReturnsFalseWhenIntermediateValueIsNotArray()
    {
        $config = new DotConfig(["db" => "localhost"]);
        $this->assertFalse($config->has("db.host"));
    }

    public function testHasReturnsTrueForExplicitNullValue()
    {
        $config = new DotConfig(["foo" => null]);
        // unlike get(), has() only checks key existence, not the value itself
        $this->assertTrue($config->has("foo"));
    }

    public function testConstructWithEmptyArray()
    {
        $config = new DotConfig([]);
        $this->assertFalse($config->has("anything"));
        $this->assertNull($config->get("anything"));
    }
}
