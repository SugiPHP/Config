<?php

declare(strict_types=1);

/**
 * INI file loader.
 */

namespace SugiPHP\Config\Loader;

use SugiPHP\Config\Parser\Ini as IniParser;
use SugiPHP\Config\Parser\ParserInterface;

class IniLoader extends AbstractLoader
{
    /**
     * {@inheritdoc}
     */
    protected function getExtension(): string
    {
        return 'ini';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParser(): ParserInterface
    {
        return new IniParser();
    }
}
