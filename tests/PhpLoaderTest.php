<?php

declare(strict_types=1);

/**
 * Tests for PhpLoader class.
 */

namespace SugiPHP\Config\Tests;

use SugiPHP\Config\Loader\PhpLoader as Loader;
use PHPUnit\Framework\TestCase;

class PhpLoaderTest extends TestCase
{
    public function testPhpLoaderIsLoaderInterface()
    {
        $loader = new Loader();
        $this->assertInstanceOf("\SugiPHP\Config\Loader\LoaderInterface", $loader);
    }

    public function testReturnsNull()
    {
        $loader = new Loader();
        $this->assertNull($loader->load("nosuchfile"));
        $this->assertNull($loader->load("nosuchfile.php"));
    }

    public function testAddPhp()
    {
        $loader = new Loader();
        $testArr = include __DIR__."/config/test.php";
        $this->assertEquals($testArr, $loader->load(__DIR__."/config/test.php"));
        $this->assertEquals($testArr, $loader->load(__DIR__."/config/test"));
    }

    public function testLoaderWithLocator()
    {
        $testArr = include __DIR__."/config/test.php";

        $loader = new Loader(array(__DIR__, __DIR__."/config"));
        $this->assertNull($loader->load("nosuchfile"));
        $this->assertEquals($testArr, $loader->load("config/test.php"));
        $this->assertEquals($testArr, $loader->load("config/test"));
        $this->assertEquals($testArr, $loader->load("test.php"));
        $this->assertEquals($testArr, $loader->load("test"));
    }
}
