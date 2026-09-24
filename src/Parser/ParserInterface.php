<?php

declare(strict_types=1);

namespace SugiPHP\Config\Parser;

/**
 * A parser should convert data or a file into an associative array which
 * then can be passed to a Config class.
 */
interface ParserInterface
{
    /**
     * Convert data into an array.
     *
     * @param mixed $data
     *
     * @return array
     */
    public function parse(mixed $data): array;

    /**
     * Convert a file to an array.
     * Usually this method will read a file and pass its contents to parse()
     * and return the resulting array.
     *
     * @param string $fileName
     *
     * @return array
     */
    public function parseFile(string $fileName): array;
}
