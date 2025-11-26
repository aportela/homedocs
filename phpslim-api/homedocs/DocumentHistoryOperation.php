<?php

declare(strict_types=1);

namespace HomeDocs;

class DocumentHistoryOperation

// TODO: enum ???
{
    public const OPERATION_NONE = 0;
    public const OPERATION_ADD_DOCUMENT = 1;
    public const OPERATION_UPDATE_DOCUMENT = 2;

    public function __construct(public int $createdAtTimestamp, public int $operationType) {}
}
