<?php

declare(strict_types=1);

namespace HomeDocs;

class DocumentHistoryOperation
{
    public const OPERATION_ADD_DOCUMENT = 1;
    
    public const OPERATION_UPDATE_DOCUMENT = 2;

    public function __construct(public ?int $operationTimestamp = null, public ?int $operationType = null)
    {
    }
}
