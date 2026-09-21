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
    public function fromFile(string $fileName): array
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
     * {@inheritDoc}
     */
    public function fromString(string $string): array
    {
        throw new ConfigException('PHP parser does not support string parsing');
    }
}
