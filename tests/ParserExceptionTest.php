<?php

declare(strict_types=1);

/**
 * Tests that parsers signal invalid content with ParserException.
 */

namespace SugiPHP\Config\Tests;

use SugiPHP\Config\Exception\ParserException;
use SugiPHP\Config\Parser\Ini;
use SugiPHP\Config\Parser\Json;
use SugiPHP\Config\Parser\Php;
use SugiPHP\Config\Parser\Xml;
use PHPUnit\Framework\TestCase;

class ParserExceptionTest extends TestCase
{
    public function testMalformedXmlThrowsParserException()
    {
        $this->expectException(ParserException::class);
        (new Xml())->parse('<config><host>localhost</host>');
    }

    public function testMalformedXmlDoesNotEmitWarnings()
    {
        set_error_handler(function ($errno, $errstr) {
            $this->fail("Unexpected PHP warning/notice: {$errstr}");
        });

        try {
            (new Xml())->parse('<config>');
            $this->fail('Expected ParserException');
        } catch (ParserException $e) {
            $this->assertStringContainsString('XML parse error', $e->getMessage());
        } finally {
            restore_error_handler();
        }
    }

    public function testValidXmlStillParses()
    {
        $this->assertSame(["host" => "localhost"], (new Xml())->parse('<config><host>localhost</host></config>'));
    }

    public function testMalformedJsonThrowsParserException()
    {
        $this->expectException(ParserException::class);
        (new Json())->parse('{"host": ');
    }

    public function testNonArrayJsonThrowsParserException()
    {
        $this->expectException(ParserException::class);
        (new Json())->parse('42');
    }

    public function testPhpFileNotReturningArrayThrowsParserException()
    {
        $tmp = tempnam(sys_get_temp_dir(), 'sugi');
        $file = $tmp . '.php';
        file_put_contents($file, '<?php return "not an array";');

        try {
            $this->expectException(ParserException::class);
            (new Php())->parseFile($file);
        } finally {
            unlink($file);
            unlink($tmp);
        }
    }

    public function testMalformedIniThrowsParserException()
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('INI parse error: syntax error');
        (new Ini())->parse('a=b=c');
    }

    public function testMalformedIniThrowsParserExceptionEvenWithThrowingErrorHandler()
    {
        // a global handler converting warnings to exceptions (as frameworks
        // in debug mode do) must not see the parse_ini_string() warning
        set_error_handler(function (int $errno, string $errstr) {
            throw new \ErrorException($errstr, 0, $errno);
        });

        try {
            (new Ini())->parse('[section');
            $this->fail('Expected ParserException');
        } catch (ParserException $e) {
            $this->assertStringContainsString('INI parse error', $e->getMessage());
        } finally {
            restore_error_handler();
        }
    }

    public function testIniParserRestoresPreviousErrorHandler()
    {
        $handler = function () {
            return true;
        };
        set_error_handler($handler);

        try {
            try {
                (new Ini())->parse('a=b=c');
            } catch (ParserException $e) {
            }
            $this->assertSame($handler, set_error_handler(null));
        } finally {
            restore_error_handler();
            restore_error_handler();
        }
    }

    public function testPhpFileWithSyntaxErrorThrowsParserException()
    {
        $tmp = tempnam(sys_get_temp_dir(), 'sugi');
        $file = $tmp . '.php';
        file_put_contents($file, '<?php return ["key" => ;');

        try {
            (new Php())->parseFile($file);
            $this->fail('Expected ParserException');
        } catch (ParserException $e) {
            $this->assertStringContainsString("PHP parse error in {$file} on line 1", $e->getMessage());
            $this->assertInstanceOf(\ParseError::class, $e->getPrevious());
        } finally {
            unlink($file);
            unlink($tmp);
        }
    }
}
