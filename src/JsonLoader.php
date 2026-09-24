<?php

declare(strict_types=1);

/**
 * JSON file loader.
 */

namespace SugiPHP\Config;

class JsonLoader implements LoaderInterface
{
    protected $locator;

    public function __construct(?LocatorInterface $locator = null)
    {
        $this->locator = $locator;
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $resource): ?array
    {
        // check the extension. If it's not provided we'll add .json
        if (pathinfo($resource, PATHINFO_EXTENSION) === '') {
            $resource .= '.json';
        }

        $file = false;

        if ($this->locator) {
            // pass it to the locator (if set) and than include the file
            $file = $this->locator->locate($resource);
        } elseif (is_file($resource) && is_readable($resource)) {
            // check if the $resource is a real file and include it
            $file = $resource;
        }

        if ($file) {
            $parser = new \SugiPHP\Config\Parser\Json();
            return $parser->parseFile($file);
        }

        return null;
    }
}
