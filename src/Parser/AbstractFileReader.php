<?php

declare(strict_types=1);

namespace SugiPHP\Config\Parser;

use SugiPHP\Config\Exception\FileException;

/**
 * A parser should convert data or a file into an associative array which
 * then can be passed to a Config class.
 */
abstract class AbstractFileReader implements ParserInterface
{
    /**
     * {@inheritDoc}
     */
    abstract public function parse(mixed $data): array;

    /**
     * {@inheritDoc}
     */
    public function parseFile(string $fileName): array
    {
        // check if the $fileName is a real file and load it
        if (!is_file($fileName)) {
            throw new FileException("Could not find configuration file {$fileName}");
        }
        if (!is_readable($fileName)) {
            throw new FileException("Configuration file {$fileName} is unreadable");
        }
        $contents = file_get_contents($fileName);
        if ($contents === false) {
            throw new FileException("Could not read configuration file {$fileName}");
        }

        return $this->parse($contents);
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
                static::class,
                static::class
            ),
            E_USER_DEPRECATED
        );

        return $this->parse($string);
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
                static::class,
                static::class
            ),
            E_USER_DEPRECATED
        );

        return $this->parseFile($fileName);
    }
}
