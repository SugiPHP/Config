<?php

declare(strict_types=1);

namespace SugiPHP\Config;

use SugiPHP\Config\Exception\ConfigException;

/**
 * @deprecated since 2.0.0, use SugiPHP\Config\Exception\ConfigException instead.
 *             This class will be removed in a future major version.
 */
class Exception extends ConfigException
{
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        @trigger_error(
            sprintf(
                '%s is deprecated since 2.0.0 and will be removed in a future version. Use %s instead.',
                self::class,
                ConfigException::class
            ),
            E_USER_DEPRECATED
        );

        parent::__construct($message, $code, $previous);
    }
}
