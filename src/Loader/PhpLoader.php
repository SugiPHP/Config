<?php

declare(strict_types=1);

/**
 * PHP file loader.
 */

namespace SugiPHP\Config\Loader;

use SugiPHP\Config\Parser\Php as PhpParser;
use SugiPHP\Config\Parser\ParserInterface;

class PhpLoader extends AbstractLoader
{
    /**
     * {@inheritdoc}
     */
    protected function getExtension(): string
    {
        return 'php';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParser(): ParserInterface
    {
        return new PhpParser();
    }
}
