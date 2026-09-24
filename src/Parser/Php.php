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
     * {@inheritDoc}
     */
    public function parse(mixed $data): array
    {
        throw new ConfigException('PHP parser does not support string parsing');
    }

    /**
     * @deprecated since 2.0.0, use parseFile() instead. Will be removed in a
     *             future version.
     */
    public function fromFile(string $fileName): array
    {
        @trigger_error(
            sprintf(
                '%s::fromFile() is deprecated since 2.0.0, use %s::parseFile() instead. It will be removed in a future version.',
                self::class,
                self::class
            ),
            E_USER_DEPRECATED
        );

        return $this->parseFile($fileName);
    }

    /**
     * @deprecated since 2.0.0, use parse() instead. Will be removed in a
     *             future version.
     */
    public function fromString(string $string): array
    {
        @trigger_error(
            sprintf(
                '%s::fromString() is deprecated since 2.0.0, use %s::parse() instead. It will be removed in a future version.',
                self::class,
                self::class
            ),
            E_USER_DEPRECATED
        );

        return $this->parse($string);
    }
}
