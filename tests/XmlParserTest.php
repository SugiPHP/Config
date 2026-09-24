<?php

declare(strict_types=1);

/**
 * Tests for the XML parser.
 */

namespace SugiPHP\Config\Tests;

use SugiPHP\Config\Parser\Xml;
use PHPUnit\Framework\TestCase;

class XmlParserTest extends TestCase
{
    public function testParsesFixture()
    {
        $this->assertSame(
            ["str" => "value", "arr" => [["sub" => "subvalue"], "nokey"], "null" => "null", "int" => "42", "zero" => "0", "false" => "false", "true" => "true"],
            (new Xml())->parseFile(__DIR__."/config/test.xml")
        );
    }

    public function testCdataIsKept()
    {
        $this->assertSame(
            ["password" => "p&ss<word>"],
            (new Xml())->parse('<config><password><![CDATA[p&ss<word>]]></password></config>')
        );
    }

    public function testEmptyElementIsEmptyString()
    {
        $this->assertSame(
            ["name" => "", "other" => ""],
            (new Xml())->parse('<config><name></name><other/></config>')
        );
    }

    public function testEmptyRootIsEmptyArray()
    {
        $this->assertSame([], (new Xml())->parse('<config/>'));
    }

    public function testRepeatedElementsBecomeList()
    {
        $this->assertSame(
            ["item" => ["a", "b", "c"], "single" => "x"],
            (new Xml())->parse('<config><item>a</item><item>b</item><item>c</item><single>x</single></config>')
        );
    }

    public function testAttributes()
    {
        $this->assertSame(
            ["db" => ["@attributes" => ["driver" => "mysql"], "host" => "h"], "flag" => ["@attributes" => ["on" => "1"]]],
            (new Xml())->parse('<config><db driver="mysql"><host>h</host></db><flag on="1"/></config>')
        );
    }
}
