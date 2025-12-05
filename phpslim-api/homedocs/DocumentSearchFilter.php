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

    /**
     * @param array<mixed> $routeParams
     */
    public function __construct(array $routeParams = [], public string $currentUserId = '')
    {
        $this->datesFilter = new \HomeDocs\DocumentSearchDatesFilter($routeParams);
        $this->tags = $this->getTagsFilterFromParams($routeParams);
        $this->textFilter = new \HomeDocs\DocumentSearchTextFilter($routeParams);
    }

    /**
     * @param array<mixed> $params
     * @return array<string>
     */
    private function getTagsFilterFromParams(array $params): array
    {
        return (
            array_key_exists("filter", $params)
            && is_array($params["filter"])
            && array_key_exists("tags", $params["filter"])
            && is_array($params["filter"]["tags"])
            ? array_filter($params["filter"]["tags"], is_string(...))
            : []
        );
    }
}
