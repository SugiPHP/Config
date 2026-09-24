<?php

declare(strict_types=1);

namespace SugiPHP\Config\Parser;

use SugiPHP\Config\Exception\FileException;
use SugiPHP\Config\Exception\ConfigException;

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

        $arr = include $fileName;
        if (!is_array($arr)) {
            throw new FileException("File {$fileName} does not return an array");
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

        throw new ConfigException('PHP parser can only parse arrays, ' . get_debug_type($data) . ' given');
    }
}
