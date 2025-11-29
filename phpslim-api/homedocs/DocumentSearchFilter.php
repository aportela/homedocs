<?php

declare(strict_types=1);

namespace HomeDocs;

class DocumentSearchFilter
{
    public readonly \HomeDocs\DocumentSearchDatesFilter $datesFilter;

    /**
     * @var array<string>
     */
    public readonly array $tags;

    public readonly \HomeDocs\DocumentSearchTextFilter $textFilter;

    public function __construct(array $routeParams = [])
    {
        $this->datesFilter = new \HomeDocs\DocumentSearchDatesFilter($routeParams);
        $this->tags = $this->getTagsFilterFromParams($routeParams);
        $this->textFilter = new \HomeDocs\DocumentSearchTextFilter($routeParams);
    }

    private function getTagsFilterFromParams(array $params): array
    {
        return (
            array_key_exists("tags", $params)  &&
            is_array($params["tags"])
            ?
            $params["tags"]
            :
            []
        );
    }
}
