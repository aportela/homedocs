<?php

declare(strict_types=1);

namespace HomeDocs\Exception;

/**
 * file upload exception
 */
class UploadException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
