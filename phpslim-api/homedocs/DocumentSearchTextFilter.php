<?php

declare(strict_types=1);

namespace HomeDocs;

class DocumentSearchTextFilter
{
    public readonly ?string $title;

    public readonly ?string $description;

    public readonly ?string $notesBody;

    public readonly ?string $attachmentsFilename;

    public function __construct(array $routeParams = [])
    {
        $this->title = $this->getTextFilterFromParams($routeParams, "title");
        $this->description = $this->getTextFilterFromParams($routeParams, "description");
        $this->notesBody = $this->getTextFilterFromParams($routeParams, "notesBody");
        $this->attachmentsFilename = $this->getTextFilterFromParams($routeParams, "attachmentsFilename");
    }

    private function getTextFilterFromParams(array $routeParams, string $textFilterType): string | null
    {
        return (
            array_key_exists("filter", $routeParams) &&
            is_array($routeParams["filter"]) &&
            array_key_exists("text", $routeParams["filter"]) &&
            is_array(($routeParams["filter"]["text"])) &&
            array_key_exists($textFilterType, $routeParams["filter"]["text"]) &&
            is_string($routeParams["filter"]["text"][$textFilterType])
            ?
            $routeParams["filter"]["text"][$textFilterType]
            :
            null
        );
    }
}
