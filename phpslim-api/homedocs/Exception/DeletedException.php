<?php

declare(strict_types=1);

namespace HomeDocs\Exception;

/**
 * "deleted element" custom exception (operation fails due element has been deleted and can not be accessed)
 */
class DeletedException extends \Exception
{
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
