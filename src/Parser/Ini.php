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
        // capture the warning parse_ini_string() raises on a syntax error, so it
        // neither leaks to the output nor reaches a global error handler that
        // may convert it to an exception other than ParserException
        $error = null;
        set_error_handler(function (int $errno, string $errstr) use (&$error): bool {
            $error = $errstr;
            return true;
        });
        try {
            $arr = parse_ini_string((string) $data, true, INI_SCANNER_TYPED);
        } finally {
            restore_error_handler();
        }

        if (false === $arr) {
            throw new ParserException('INI parse error' . ($error === null ? '' : ": {$error}"));
        }

        return $arr;
    }
}
