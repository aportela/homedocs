<?php

declare(strict_types=1);

namespace HomeDocs;

class Stats
{
    public static function getTotalPublishedDocuments(\aportela\DatabaseWrapper\DB $db, string $userId): int
    {
        $results = $db->query(
            "
                SELECT
                    COALESCE(COUNT(DOCUMENT_HISTORY.document_id), 0) AS total
                FROM DOCUMENT_HISTORY
                WHERE
                    DOCUMENT_HISTORY.operation_type = :history_operation_add
                AND
                    DOCUMENT_HISTORY.cuid = :session_user_id
            ",
            [
                new \aportela\DatabaseWrapper\Param\IntegerParam(":history_operation_add", \HomeDocs\DocumentHistoryOperation::OPERATION_ADD_DOCUMENT),
                new \aportela\DatabaseWrapper\Param\StringParam(":session_user_id", $userId),
            ]
        );
        return (count($results) === 1 && property_exists($results[0], "total") && is_numeric($results[0]->total) ? intval($results[0]->total) : 0);
    }

    public static function getTotalUploadedAttachments(\aportela\DatabaseWrapper\DB $db, string $userId): int
    {
        $results = $db->query(
            "
                SELECT
                    COALESCE(COUNT(DOCUMENT_ATTACHMENT.attachment_id), 0) AS total
                FROM DOCUMENT_ATTACHMENT
                INNER JOIN DOCUMENT_HISTORY ON DOCUMENT_HISTORY.document_id = DOCUMENT_ATTACHMENT.document_id
                WHERE
                    DOCUMENT_HISTORY.operation_type = :history_operation_add
                AND
                    DOCUMENT_HISTORY.cuid = :session_user_id
            ",
            [
                new \aportela\DatabaseWrapper\Param\IntegerParam(":history_operation_add", \HomeDocs\DocumentHistoryOperation::OPERATION_ADD_DOCUMENT),
                new \aportela\DatabaseWrapper\Param\StringParam(":session_user_id", $userId),
            ]
        );
        return (count($results) === 1 && property_exists($results[0], "total") && is_numeric($results[0]->total) ? intval($results[0]->total) : 0);
    }

    public static function getTotalUploadedAttachmentsDiskUsage(\aportela\DatabaseWrapper\DB $db, string $userId): int
    {
        $results = $db->query(
            "
                SELECT
                    COALESCE(SUM(ATTACHMENT.size), 0) AS total
                FROM DOCUMENT_ATTACHMENT
                INNER JOIN DOCUMENT_HISTORY ON DOCUMENT_HISTORY.document_id = DOCUMENT_ATTACHMENT.document_id
                INNER JOIN ATTACHMENT ON ATTACHMENT.id = DOCUMENT_ATTACHMENT.attachment_id
                WHERE
                    DOCUMENT_HISTORY.operation_type = :history_operation_add
                AND
                    DOCUMENT_HISTORY.cuid = :session_user_id
            ",
            [
                new \aportela\DatabaseWrapper\Param\IntegerParam(":history_operation_add", \HomeDocs\DocumentHistoryOperation::OPERATION_ADD_DOCUMENT),
                new \aportela\DatabaseWrapper\Param\StringParam(":session_user_id", $userId),
            ]
        );
        return (count($results) === 1 && property_exists($results[0], "total") && is_numeric($results[0]->total) ? intval($results[0]->total) : 0);
    }

    /**
     * @return array<object>
     */
    public static function getActivityHeatMapData(\aportela\DatabaseWrapper\DB $db, string $userId, int $fromTimestamp = 0): array
    {
        $whereCondition = null;
        $params = [
            new \aportela\DatabaseWrapper\Param\StringParam(":session_user_id", $userId),
        ];
        if ($fromTimestamp > 0) {
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":from_timestamp", $fromTimestamp);
            $whereCondition = " AND DOCUMENT_HISTORY.ctime >= :from_timestamp ";
        }

        $results = $db->query(
            sprintf(
                "
                    SELECT
                        DATE(DOCUMENT_HISTORY.ctime / 1000, 'unixepoch') AS activity_date, COUNT(*) AS total
                    FROM
                        DOCUMENT_HISTORY
                    WHERE
                        DOCUMENT_HISTORY.cuid = :session_user_id
                    %s
                    GROUP BY
                        activity_date
                    ORDER BY
                        activity_date
                ",
                count($params) === 2 ? $whereCondition : null
            ),
            $params
        );
        return (array_map(
            fn(object $item): object => (object) ["date" => $item->activity_date ?? null, "count" => property_exists($item, "total") && is_numeric($item->total) ? intval($item->total) : 0],
            $results
        ));
    }
}
