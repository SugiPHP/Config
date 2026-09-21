<?php

declare(strict_types=1);

namespace SugiPHP\Config\Parser;

/**
 * A parser should convert a string or a file into an associative array which
 * then can be passed to a Config class.
 */
interface ParserInterface
{
    /**
     * Convert a string into an array
     *
     * @param string $string
     *
     * @return array
     */
    public function fromString(string $string): array;

    /**
     * Convert a file to an array.
     * Usually this method will read a file and pass it to fromString method
     * and return the resulting array.
     *
     * @param string $fileName
     *
     * @return array
     */
    public function fromFile(string $fileName): array;
}
