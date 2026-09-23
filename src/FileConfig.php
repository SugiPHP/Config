<?php

namespace SugiPHP\Config;

use SugiPHP\Config\Exception\ConfigException;
use SugiPHP\Config\Parser\Ini as IniParser;
use SugiPHP\Config\Parser\Json as JsonParser;
use SugiPHP\Config\Parser\Php as PhpParser;
use SugiPHP\Config\Parser\Xml as XmlParser;

class FileConfig extends DotConfig
{
    public function __construct(string $filename)
    {
        // check if it is a file, but not a directory
        if (!is_file($filename)) {
            throw new ConfigException("File not found: {$filename}");
        }

        $pathinfo = pathinfo($filename);

        if (!isset($pathinfo['extension'])) {
            throw new ConfigException("File {$filename} w/o extension cannot be auto loaded");
        }

        $ext = $pathinfo['extension'];

        if ('ini' === $ext) {
            $parser = new IniParser();
        } elseif ('json' === $ext) {
            $parser = new JsonParser();
        } elseif ('php' === $ext) {
            $parser = new PhpParser();
        } elseif ('xml' === $ext) {
            $parser = new XmlParser();
        } else {
            throw new ConfigException("Parser unavailable for file with extension {$ext}");
        }
        $arr = $parser->fromFile($filename);

        parent::__construct($arr);
    }
}
