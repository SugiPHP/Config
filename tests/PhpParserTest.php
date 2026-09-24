<?php

declare(strict_types=1);

/**
 * Tests for the PHP parser.
 */

namespace SugiPHP\Config\Tests;

use SugiPHP\Config\Exception\ParserException;
use SugiPHP\Config\Parser\Php;
use PHPUnit\Framework\TestCase;

class PhpParserTest extends TestCase
{
    public function testParseReturnsArrayAsIs()
    {
        $arr = ["db" => ["host" => "localhost"], "null" => null];
        $this->assertSame($arr, (new Php())->parse($arr));
    }

    public function testParseReturnsEmptyArray()
    {
        $this->assertSame([], (new Php())->parse([]));
    }

    public function testParseThrowsOnString()
    {
        $this->expectException(ParserException::class);
        (new Php())->parse('<?php return ["foo" => "bar"];');
    }

    public function testParseThrowsOnNonArray()
    {
        $this->expectException(ParserException::class);
        (new Php())->parse(42);
    }

    public function testParseFileReturnsFileArray()
    {
        $this->assertSame(include __DIR__."/config/test.php", (new Php())->parseFile(__DIR__."/config/test.php"));
    }
}
