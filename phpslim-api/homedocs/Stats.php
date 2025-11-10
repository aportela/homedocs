<?php

declare(strict_types=1);

namespace HomeDocs;

class Stats
{
    public static function getTotalPublishedDocuments(\aportela\DatabaseWrapper\DB $dbh): int
    {
        $result = $dbh->query(
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
                new \aportela\DatabaseWrapper\Param\StringParam(":session_user_id", \HomeDocs\UserSession::getUserId())
            ]
        );
        return (intval($result[0]->total));
    }

    public static function getTotalUploadedAttachments(\aportela\DatabaseWrapper\DB $dbh): int
    {
        $result = $dbh->query(
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
                new \aportela\DatabaseWrapper\Param\StringParam(":session_user_id", \HomeDocs\UserSession::getUserId())
            ]
        );
        return (intval($result[0]->total));
    }

    public static function getTotalUploadedAttachmentsDiskUsage(\aportela\DatabaseWrapper\DB $dbh): int
    {
        $result = $dbh->query(
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
                new \aportela\DatabaseWrapper\Param\StringParam(":session_user_id", \HomeDocs\UserSession::getUserId())
            ]
        );
        return (intval($result[0]->total));
    }

    /**
     * @return array<mixed>
     */
    public static function getActivityHeatMapData(\aportela\DatabaseWrapper\DB $dbh, int $fromTimestamp = 0): array
    {
        $whereCondition = null;
        $params = [
            new \aportela\DatabaseWrapper\Param\StringParam(":session_user_id", \HomeDocs\UserSession::getUserId())
        ];
        if ($fromTimestamp > 0) {
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":from_timestamp", $fromTimestamp);
            $whereCondition = " AND DOCUMENT_HISTORY.ctime >= :from_timestamp ";
        }
        $results = $dbh->query(
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
            fn($item) => ["date" => $item->activity_date, "count" => intval($item->total)],
            $results
        ));
    }
}
