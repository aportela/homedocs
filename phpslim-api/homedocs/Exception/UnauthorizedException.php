<?php

declare(strict_types=1);

namespace HomeDocs\Exception;

/**
 * "unauthorized" custom exception (operation fails due not logged session)
 */
class UnauthorizedException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
