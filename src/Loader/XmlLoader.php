<?php

declare(strict_types=1);

/**
 * XML file loader.
 */

namespace SugiPHP\Config\Loader;

use SugiPHP\Config\Parser\Xml as XmlParser;
use SugiPHP\Config\Parser\ParserInterface;

class XmlLoader extends AbstractLoader
{
    /**
     * {@inheritdoc}
     */
    protected function getExtension(): string
    {
        return 'xml';
    }

    /**
     * {@inheritdoc}
     */
    protected function getParser(): ParserInterface
    {
        return new XmlParser();
    }
}
