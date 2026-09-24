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

    public function testParseFileIgnoresIncludePath()
    {
        // a same-named file earlier in include_path must not be run instead
        // of the relative path that was checked
        $base = sys_get_temp_dir() . '/sugi-include-' . uniqid();
        mkdir("{$base}/decoy/conf", 0777, true);
        mkdir("{$base}/cwd/conf", 0777, true);
        file_put_contents("{$base}/decoy/conf/app.php", '<?php return ["from" => "include_path"];');
        file_put_contents("{$base}/cwd/conf/app.php", '<?php return ["from" => "cwd"];');

        $cwd = getcwd();
        $includePath = get_include_path();
        try {
            chdir("{$base}/cwd");
            set_include_path("{$base}/decoy");
            $this->assertSame(["from" => "cwd"], (new Php())->parseFile("conf/app.php"));
        } finally {
            chdir($cwd);
            set_include_path($includePath);
            unlink("{$base}/decoy/conf/app.php");
            unlink("{$base}/cwd/conf/app.php");
            rmdir("{$base}/decoy/conf");
            rmdir("{$base}/cwd/conf");
            rmdir("{$base}/decoy");
            rmdir("{$base}/cwd");
            rmdir($base);
        }
    }
}
