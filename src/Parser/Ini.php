<?php

declare(strict_types=1);

namespace SugiPHP\Config\Parser;

use SugiPHP\Config\Exception\ParserException;

/**
 * INI parser
 */
class Ini extends AbstractFileReader
{
    /**
     * {@inheritDoc}
     */
    public function parse(mixed $data): array
    {
        $arr = parse_ini_string((string) $data, true);
        // TODO:
        // $arr = parse_ini_string((string) $data, true, INI_SCANNER_TYPED);
        if (false === $arr) {
            throw new ParserException('INI parse error');
        }

        return $arr;
    }
}
