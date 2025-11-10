<?php

declare(strict_types=1);

namespace HomeDocs;

class Note
{
    public function __construct(public ?string $id = null, public ?int $createdOnTimestamp = null, public ?string $body = null)
    {
    }
}
