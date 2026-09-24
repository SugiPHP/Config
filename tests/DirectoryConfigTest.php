<?php

declare(strict_types=1);

/**
 * Tests for DirectoryConfig class.
 */

namespace SugiPHP\Config\Tests;

use SugiPHP\Config\ConfigInterface;
use SugiPHP\Config\DirectoryConfig;
use SugiPHP\Config\Exception\ConfigException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DirectoryConfigTest extends TestCase
{
    public function testDirectoryConfigImplementsConfigInterface()
    {
        $this->assertInstanceOf(ConfigInterface::class, new DirectoryConfig(__DIR__."/directory"));
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
            new DirectoryConfig(__DIR__."/directory");
        } finally {
            restore_error_handler();
        }

        $this->assertTrue(true);
    }

    public function testGetReturnsNullIfResourceFileNotFound()
    {
        $config = new DirectoryConfig(__DIR__."/directory");
        $this->assertNull($config->get("nosuchfile"));
        $this->assertNull($config->get("nosuchfile.key"));
    }

    public function testGetReturnsDefaultIfResourceFileNotFound()
    {
        $config = new DirectoryConfig(__DIR__."/directory");
        $this->assertSame("default", $config->get("nosuchfile", "default"));
        $this->assertSame("default", $config->get("nosuchfile.key", "default"));
    }

    public function testHasReturnsFalseIfResourceFileNotFound()
    {
        $config = new DirectoryConfig(__DIR__."/directory");
        $this->assertFalse($config->has("nosuchfile"));
        $this->assertFalse($config->has("nosuchfile.key"));
    }

    public function testGetReturnsWholeFileContents()
    {
        $config = new DirectoryConfig(__DIR__."/directory");
        $this->assertEquals(include __DIR__."/directory/test.php", $config->get("test"));
    }

    public function testGetResolvesTopLevelDotNotation()
    {
        $config = new DirectoryConfig(__DIR__."/directory");
        $this->assertSame(42, $config->get("test.int"));
        $this->assertSame("value", $config->get("test.str"));
    }

    public function testGetResolvesNestedDotNotation()
    {
        $config = new DirectoryConfig(__DIR__."/directory");
        $this->assertSame("subvalue", $config->get("test.arr.sub"));
    }

    public function testGetReturnsDefaultForMissingKeyInsideFoundFile()
    {
        $config = new DirectoryConfig(__DIR__."/directory");
        $this->assertNull($config->get("test.nosuchkey"));
        $this->assertSame("default", $config->get("test.nosuchkey", "default"));
    }

    public function testHasReturnsTrueForExistingTopLevelResource()
    {
        $config = new DirectoryConfig(__DIR__."/directory");
        $this->assertTrue($config->has("test"));
    }

    public function testHasReturnsTrueForExistingNestedKey()
    {
        $config = new DirectoryConfig(__DIR__."/directory");
        $this->assertTrue($config->has("test.int"));
    }

    public function testHasReturnsFalseForMissingKeyInsideFoundFile()
    {
        $config = new DirectoryConfig(__DIR__."/directory");
        $this->assertFalse($config->has("test.nosuchkey"));
    }

    public function testTrailingSlashInDirectoryIsAccepted()
    {
        $config = new DirectoryConfig(__DIR__."/directory/");
        $this->assertSame(42, $config->get("test.int"));
    }

    /**
     * When several files exist for the same resource name (e.g. conf.ini and
     * conf.json), none of them silently wins - the resource is ambiguous.
     */
    public static function ambiguousDirectories(): array
    {
        return [
            'php, ini, json, xml' => ["ambiguous-all", "conf.php, conf.ini, conf.json, conf.xml"],
            'ini, json, xml'      => ["ambiguous-ini-json-xml", "conf.ini, conf.json, conf.xml"],
            'ini, xml'            => ["ambiguous-ini-xml", "conf.ini, conf.xml"],
            'json, xml'           => ["ambiguous-json-xml", "conf.json, conf.xml"],
        ];
    }

    #[DataProvider('ambiguousDirectories')]
    public function testGetThrowsWhenResourceIsAmbiguous(string $dir, string $files)
    {
        $config = new DirectoryConfig(__DIR__."/config/{$dir}");

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage("Ambiguous configuration resource \"conf\": found {$files}");
        $config->get("conf.marker");
    }

    #[DataProvider('ambiguousDirectories')]
    public function testHasThrowsWhenResourceIsAmbiguous(string $dir, string $files)
    {
        $config = new DirectoryConfig(__DIR__."/config/{$dir}");

        $this->expectException(ConfigException::class);
        $config->has("conf");
    }

    public function testSingleFileIsUsedWhateverItsFormat()
    {
        $config = new DirectoryConfig(__DIR__."/config/single-xml");
        $this->assertSame("xml", $config->get("conf.marker"));
    }

    public function testAmbiguityOfOneResourceDoesNotAffectOthers()
    {
        // tests/config holds test.php, test.ini, test.json and test.xml, so
        // "test" is ambiguous there, but other lookups still work
        $config = new DirectoryConfig(__DIR__."/config");
        $this->assertSame("default", $config->get("nosuchfile.key", "default"));
        $this->expectException(ConfigException::class);
        $config->has("test");
    }

    public function testNullValueExistsLikeInFileConfig()
    {
        // "test.null" exists and is explicitly null in tests/directory/test.php;
        // like FileConfig and DotConfig, an explicit null is a value
        $config = new DirectoryConfig(__DIR__."/directory");
        $this->assertNull($config->get("test.null", "default"));
        $this->assertTrue($config->has("test.null"));
    }

    public function testMissingKeyInsideNullValueIsAbsent()
    {
        $config = new DirectoryConfig(__DIR__."/directory");
        $this->assertSame("default", $config->get("test.null.sub", "default"));
        $this->assertFalse($config->has("test.null.sub"));
    }

    public function testMissingResourceIsNotReportedAsExistingOnSecondLookup()
    {
        $config = new DirectoryConfig(__DIR__."/directory");
        $this->assertFalse($config->has("nosuchfile"));
        $this->assertFalse($config->has("nosuchfile"));
        $this->assertSame("default", $config->get("nosuchfile", "default"));
    }
}
