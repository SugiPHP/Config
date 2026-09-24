<?php

declare(strict_types=1);

namespace SugiPHP\Config\Parser;

use SimpleXMLElement;
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
            // LIBXML_NOCDATA merges CDATA sections into the text of their element
            $xml = simplexml_load_string((string) $data, SimpleXMLElement::class, LIBXML_NOCDATA);
            $error = libxml_get_last_error();
            libxml_clear_errors();
        } finally {
            libxml_use_internal_errors($previous);
        }

        if ($xml === false) {
            $message = $error ? trim($error->message) : 'unknown error';
            throw new ParserException("XML parse error: {$message}");
        }

        $array = $this->toArray($xml);

        // the root element holds the configuration, so it's always an array,
        // even when it has no child elements
        return is_array($array) ? $array : [];
    }

    /**
     * Converts an element to an array of its children, or to its text if it
     * has none. Repeated child elements become a list. Attributes are kept
     * under "@attributes" for elements without text.
     *
     * @param SimpleXMLElement $element
     *
     * @return array|string
     */
    private function toArray(SimpleXMLElement $element): array|string
    {
        $attributes = [];
        foreach ($element->attributes() as $name => $value) {
            $attributes[$name] = (string) $value;
        }

        $children = [];
        $counts = [];
        foreach ($element->children() as $name => $child) {
            $value = $this->toArray($child);
            $counts[$name] = ($counts[$name] ?? 0) + 1;
            if ($counts[$name] === 1) {
                $children[$name] = $value;
            } elseif ($counts[$name] === 2) {
                $children[$name] = [$children[$name], $value];
            } else {
                $children[$name][] = $value;
            }
        }

        if ($children) {
            return $attributes ? ['@attributes' => $attributes] + $children : $children;
        }

        $text = (string) $element;
        if ($text === '' && $attributes) {
            return ['@attributes' => $attributes];
        }

        return $text;
    }
}
