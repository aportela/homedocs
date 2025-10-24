<?php

declare(strict_types=1);

namespace HomeDocs;

class Tag
{
    /**
     * @return array<mixed>
     */
    public static function getCloud(\aportela\DatabaseWrapper\DB $dbh): array
    {
        $results = $dbh->query(
            "
                    SELECT
                        COUNT(*) AS total, tag
                    FROM DOCUMENT_TAG
                    INNER JOIN DOCUMENT_HISTORY ON DOCUMENT_HISTORY.document_id = DOCUMENT_TAG.document_id
                    WHERE
                        DOCUMENT_HISTORY.cuid = :session_user_id
                    AND
                        DOCUMENT_HISTORY.operation_type = :history_operation_add
                    GROUP BY
                        tag
                    ORDER BY
                        tag
                ",
            [
                new \aportela\DatabaseWrapper\Param\IntegerParam(":history_operation_add", \HomeDocs\DocumentHistoryOperation::OPERATION_ADD_DOCUMENT),
                new \aportela\DatabaseWrapper\Param\StringParam(":session_user_id", \HomeDocs\UserSession::getUserId())
            ]
        );
        $results = array_map(
            function ($item) {
                $item->total = intval($item->total);
                return ($item);
            },
            $results
        );
        return ($results);
    }

    /**
     * @return array<string>
     */
    public static function search(\aportela\DatabaseWrapper\DB $dbh): array
    {
        $results = $dbh->query(
            "
                    SELECT
                        DISTINCT tag
                    FROM DOCUMENT_TAG
                    INNER JOIN DOCUMENT_HISTORY ON DOCUMENT_HISTORY.document_id = DOCUMENT_TAG.document_id
                    WHERE
                        DOCUMENT_HISTORY.cuid = :session_user_id
                    AND
                        DOCUMENT_HISTORY.operation_type = :history_operation_add
                    ORDER BY
                        tag
                ",
            [
                new \aportela\DatabaseWrapper\Param\IntegerParam(":history_operation_add", \HomeDocs\DocumentHistoryOperation::OPERATION_ADD_DOCUMENT),
                new \aportela\DatabaseWrapper\Param\StringParam(":session_user_id", \HomeDocs\UserSession::getUserId())
            ]
        );
        $results = array_map(
            function ($item) {
                return ($item->tag);
            },
            $results
        );
        return ($results);
    }
}
