<?php

declare(strict_types=1);

/**
 * Tests for FileConfig class, one per supported format (these replace the
 * per-format loader tests removed in 3.0.0).
 */

namespace SugiPHP\Config\Tests;

use SugiPHP\Config\ConfigInterface;
use SugiPHP\Config\DotConfig;
use SugiPHP\Config\Exception\ConfigException;
use SugiPHP\Config\FileConfig;
use PHPUnit\Framework\TestCase;

class FileConfigTest extends TestCase
{
    public function testFileConfigIsDotConfig()
    {
        $config = new FileConfig(__DIR__."/config/test.php");
        $this->assertInstanceOf(ConfigInterface::class, $config);
        $this->assertInstanceOf(DotConfig::class, $config);
    }

    public function testPhpFile()
    {
        $config = new FileConfig(__DIR__."/config/test.php");

        $this->assertSame(include __DIR__."/config/test.php", $config->toArray());
        $this->assertSame(42, $config->get("int"));
        $this->assertSame("subvalue", $config->get("arr.sub"));
    }

    public function testJsonFile()
    {
        $config = new FileConfig(__DIR__."/config/test.json");

        $this->assertEquals(include __DIR__."/config/test.php", $config->toArray());
        $this->assertSame(42, $config->get("int"));
    }

    public function testIniFile()
    {
        $config = new FileConfig(__DIR__."/config/test.ini");

        // INI_SCANNER_TYPED gives native ints/bools; "inull =" is an empty string
        $this->assertEquals(
            ["istr" => "value", "iarr" => ["sub" => "subvalue", "nokey"], "inull" => null, "iint" => 42, "izero" => 0, "ifalse" => false, "itrue" => true],
            $config->toArray()
        );
        $this->assertSame(42, $config->get("iint"));
        $this->assertSame(true, $config->get("itrue"));
        $this->assertSame("subvalue", $config->get("iarr.sub"));
    }

    public function testXmlFile()
    {
        $config = new FileConfig(__DIR__."/config/test.xml");

        // XML has no types: every value is a string
        $this->assertEquals(
            ["str" => "value", "arr" => [["sub" => "subvalue"], "nokey"], "null" => "null", "int" => "42", "zero" => "0", "false" => "false", "true" => "true"],
            $config->toArray()
        );
        $this->assertSame("42", $config->get("int"));
    }

    public function testMissingFileThrows()
    {
        $this->expectException(ConfigException::class);
        new FileConfig(__DIR__."/config/nosuchfile.php");
    }

    public function testDirectoryThrows()
    {
        $this->expectException(ConfigException::class);
        new FileConfig(__DIR__."/config");
    }

    public function testUnsupportedExtensionThrows()
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage("Parser unavailable for file with extension md");
        new FileConfig(__DIR__."/../README.md");
    }

    public function testFileWithoutExtensionThrows()
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage("w/o extension");
        new FileConfig(__DIR__."/../LICENSE");
    }
}
