<?php

declare(strict_types=1);

namespace SugiPHP\Config\Parser;

use SugiPHP\Config\Exception\FileException;
use SugiPHP\Config\Exception\ParserException;

/**
 * PHP array
 */
class Php implements ParserInterface
{
    /**
     * {@inheritDoc}
     */
    public function parseFile(string $fileName): array
    {
        if (!is_file($fileName)) {
            throw new FileException("File {$fileName} not found");
        }
        if (!is_readable($fileName)) {
            throw new FileException("File {$fileName} is unreadable");
        }

        // include the absolute path of the file checked above; a relative path
        // would be looked up in include_path first and could run another file.
        // realpath() fails for stream wrappers (phar://...), which include
        // doesn't resolve through include_path anyway
        $realPath = realpath($fileName) ?: $fileName;

        try {
            $arr = include $realPath;
        } catch (\ParseError $e) {
            throw new ParserException("PHP parse error in {$fileName} on line {$e->getLine()}: {$e->getMessage()}", 0, $e);
        }
        if (!is_array($arr)) {
            throw new ParserException("File {$fileName} does not return an array");
        }

        return $arr;
    }

    /**
     * A PHP array is already "parsed", so it's returned as is. Strings (PHP
     * source code) are not supported.
     *
     * {@inheritDoc}
     */
    public function parse(mixed $data): array
    {
        if (is_array($data)) {
            return $data;
        }

        throw new ParserException('PHP parser can only parse arrays, ' . get_debug_type($data) . ' given');
    }
}
