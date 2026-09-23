<?php

declare(strict_types=1);

/**
 * JSON file loader.
 */

namespace SugiPHP\Config\Loader;

use SugiPHP\Config\Parser\Json as JsonParser;
use SugiPHP\Config\Parser\ParserInterface;

class JsonLoader extends AbstractLoader
{
    /**
     * {@inheritdoc}
     */
    protected function getExtension(): string
    {
        return 'json';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParser(): ParserInterface
    {
        return new JsonParser();
    }
}
