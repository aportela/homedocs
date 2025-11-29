<?php

declare(strict_types=1);

namespace HomeDocs;

class DocumentSearchTextFilter
{
    public readonly ?string $title;

    public readonly ?string $description;

    public readonly ?string $notesBody;

    public readonly ?string $attachmentsFilename;

    /**
     * @param array<mixed> $routeParams
     */
    public function __construct(array $routeParams = [])
    {
        $this->title = $this->getTextFilterFromParams($routeParams, "title");
        $this->description = $this->getTextFilterFromParams($routeParams, "description");
        $this->notesBody = $this->getTextFilterFromParams($routeParams, "notesBody");
        $this->attachmentsFilename = $this->getTextFilterFromParams($routeParams, "attachmentsFilename");
    }

    /**
     * @param array<mixed> $params
     */
    private function getTextFilterFromParams(array $params, string $textFilterType): ?string
    {
        return (
            array_key_exists("filter", $params)
            && is_array($params["filter"])
            && array_key_exists("text", $params["filter"])
            && is_array(($params["filter"]["text"]))
            && array_key_exists($textFilterType, $params["filter"]["text"])
            && is_string($params["filter"]["text"][$textFilterType])
            ? $params["filter"]["text"][$textFilterType]
            : null
        );
    }
}
