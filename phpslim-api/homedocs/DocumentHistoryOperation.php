<?php

declare(strict_types=1);

namespace HomeDocs;

class DocumentHistoryOperation
{
    const OPERATION_ADD_DOCUMENT = 1;
    const OPERATION_UPDATE_DOCUMENT = 2;

    public ?int $operationTimestamp;
    public ?int $operationType;

    public function __construct(?int $operationTimestamp = null, ?int $operationType = null, ?string $name = null, ?int $size = null, ?string $hash = null, ?int $createdOnTimestamp = null)
    {
        $this->operationTimestamp = $operationTimestamp;
        $this->operationType = $operationType;
    }
}
