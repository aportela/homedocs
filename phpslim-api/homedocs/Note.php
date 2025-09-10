<?php

declare(strict_types=1);

namespace HomeDocs;

class Note
{
    public ?string $id;
    public ?int $createdOnTimestamp;
    public ?string $body;

    public function __construct(string $id = "", int $createdOnTimestamp = null, string $body = null)
    {
        $this->id = $id;
        $this->createdOnTimestamp = $createdOnTimestamp;
        $this->body = $body;
    }
}
