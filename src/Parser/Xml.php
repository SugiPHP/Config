<?php

declare(strict_types=1);

namespace SugiPHP\Config\Parser;

use SugiPHP\Config\Exception\ParserException;

/**
 * XML parser
 */
class Xml extends AbstractFileReader
{
    /**
     * Converts XML object into PHP array
     *
     * {@inheritDoc}
     */
    public function parse(mixed $data): array
    {
        $previous = libxml_use_internal_errors(true);
        try {
            $xml = simplexml_load_string((string) $data);
            $error = libxml_get_last_error();
            libxml_clear_errors();
        } finally {
            libxml_use_internal_errors($previous);
        }

        if ($xml === false) {
            $message = $error ? trim($error->message) : 'unknown error';
            throw new ParserException("XML parse error: {$message}");
        }

        $array = json_decode((string) json_encode($xml), true);
        if (!is_array($array)) {
            throw new ParserException('XML string is not a valid array');
        }

        return $array;
    }
}
