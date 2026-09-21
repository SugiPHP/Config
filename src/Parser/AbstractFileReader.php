<?php

declare(strict_types=1);

namespace SugiPHP\Config\Parser;

use SugiPHP\Config\Exception\FileException;

/**
 * A parser should convert a string or a file into an associative array which
 * then can be passed to a Config class.
 */
abstract class AbstractFileReader implements ParserInterface
{
    /**
     * {@inheritDoc}
     */
    abstract public function fromString(string $string): array;

    /**
     * {@inheritDoc}
     */
    public function fromFile(string $fileName): array
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

        return $this->fromString($contents);
    }
}
