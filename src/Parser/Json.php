<?php

declare(strict_types=1);

namespace SugiPHP\Config\Parser;

use SugiPHP\Config\Exception\ConfigException;

/**
 * JSON parser
 */
class Json extends AbstractFileReader
{
    /**
     * Converts JSON object into PHP array
     *
     * {@inheritDoc}
     */
    public function parse(mixed $data): array
    {
        $arr = json_decode((string) $data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ConfigException(json_last_error_msg());
        }

        if (!is_array($arr)) {
            throw new ConfigException('JSON string is not a valid array');
        }

        return $arr;
    }
}
