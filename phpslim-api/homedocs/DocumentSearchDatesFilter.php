<?php

declare(strict_types=1);

namespace HomeDocs;

class DocumentSearchDatesFilter
{
    public readonly int $createdAtFromTimestamp;

    public readonly int $createdAtToTimestamp;

    public readonly int $lastUpdateAtFromTimestamp;

    public readonly int $lastUpdateAtToTimestamp;

    public readonly int $updatedAtFromTimestamp;

    public readonly int $updatedAtToTimestamp;

    /**
     * @param array<mixed> $routeParams
     */
    public function __construct(array $routeParams = [])
    {
        $this->createdAtFromTimestamp = $this->getDateFilterFromParams($routeParams, "createdAt", "from") ?? 0;
        $this->createdAtToTimestamp = $this->getDateFilterFromParams($routeParams, "createdAt", "to") ?? 0;
        $this->lastUpdateAtFromTimestamp = $this->getDateFilterFromParams($routeParams, "lastUpdateAt", "from") ?? 0;
        $this->lastUpdateAtToTimestamp = $this->getDateFilterFromParams($routeParams, "lastUpdateAt", "to") ?? 0;
        $this->updatedAtFromTimestamp = $this->getDateFilterFromParams($routeParams, "updatedAt", "from") ?? 0;
        $this->updatedAtToTimestamp = $this->getDateFilterFromParams($routeParams, "updatedAt", "to") ?? 0;
    }

    /**
     * @param array<mixed> $params
     */
    private function getDateFilterFromParams(array $params = [], string $dateType = '', string $timestampType = ''): ?int
    {
        if (
            array_key_exists("filter", $params)
            && is_array($params["filter"])
            && array_key_exists("dates", $params["filter"])
            && is_array($params["filter"]["dates"])
            && array_key_exists($dateType, $params["filter"]["dates"])
            && is_array($params["filter"]["dates"][$dateType])
            && array_key_exists("timestamps", $params["filter"]["dates"][$dateType])
            && is_array($params["filter"]["dates"][$dateType]["timestamps"])
            && array_key_exists($timestampType, $params["filter"]["dates"][$dateType]["timestamps"])
            && is_numeric($params["filter"]["dates"][$dateType]["timestamps"][$timestampType])
        ) {
            return (intval($params["filter"]["dates"][$dateType]["timestamps"][$timestampType]));
        } else {
            return (null);
        }
    }
}
