<?php

declare(strict_types=1);

/**
 * INI file loader.
 */

namespace SugiPHP\Config;

class IniLoader implements LoaderInterface
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
        // check the extension. If it's not provided we'll add .ini
        if (pathinfo($resource, PATHINFO_EXTENSION) === '') {
            $resource .= '.ini';
        }

        $file = false;

        if ($this->locator) {
            // pass it to the locator (if set) and than include the file
            $file = $this->locator->locate($resource);
        } elseif (is_file($resource) && is_readable($resource)) {
            // check if the $resource is a real file and include it
            $file = $resource;
        }

        if (!$file) {
            return null;
        }

        $parser = new Parser\Ini();
        return $parser->fromFile($file);
    }
}
