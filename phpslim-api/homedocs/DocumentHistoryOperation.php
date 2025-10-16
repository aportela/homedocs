<?php

declare(strict_types=1);

namespace HomeDocs;

class DocumentHistoryOperation
{
    public const OPERATION_ADD_DOCUMENT = 1;
    public const OPERATION_UPDATE_DOCUMENT = 2;

    public ?int $operationTimestamp;
    public ?int $operationType;

    public function __construct(?int $operationTimestamp = null, ?int $operationType = null)
    {
        $this->operationTimestamp = $operationTimestamp;
        $this->operationType = $operationType;
    }
}
