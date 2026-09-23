<?php

declare(strict_types=1);

/**
 * Tests for XmlLoader class.
 */

namespace SugiPHP\Config\Tests;

use SugiPHP\Config\Loader\XmlLoader as Loader;
use PHPUnit\Framework\TestCase;

class XmlLoaderTest extends TestCase
{
    public function testJsonLoaderIsLoaderInterface()
    {
        $loader = new Loader();
        $this->assertInstanceOf("\SugiPHP\Config\Loader\LoaderInterface", $loader);
    }

    public function testReturnsNull()
    {
        $loader = new Loader();
        $this->assertNull($loader->load("nosuchfile"));
        $this->assertNull($loader->load("nosuchfile.xml"));
    }

    public function testAddExtension()
    {
        $loader = new Loader();
        $this->assertEquals($loader->load(__DIR__."/config/test"), $loader->load(__DIR__."/config/test.xml"));
    }

    public function testLoaderWithLocator()
    {
        $testArr = array("str" => "value", "arr" => array(array("sub" => "subvalue"), "nokey"), "null" => "null", "int" => "42", "zero" => "0", "false" => "false", "true" => "true");

        $loader = new Loader(array(__DIR__, __DIR__."/config"));
        $this->assertNull($loader->load("nosuchfile"));
        $this->assertEquals($testArr, $loader->load("config/test.xml"));
        $this->assertEquals($testArr, $loader->load("config/test"));
        $this->assertEquals($testArr, $loader->load("test.xml"));
        $this->assertEquals($testArr, $loader->load("test"));
    }
}
