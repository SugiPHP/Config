<?php

declare(strict_types=1);

namespace SugiPHP\Config\Parser;

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
    public function fromString(string $string): array
    {
        $xml = simplexml_load_string($string);
        $json = json_encode($xml);
        $array = json_decode($json, true);

        return $array;
    }
}
