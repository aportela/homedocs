<?php

declare(strict_types=1);

namespace HomeDocs;

class Tag
{
    /**
     * @return array<string>
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
                        DOCUMENT_HISTORY.operation_user_id = :session_user_id
                    AND
                        DOCUMENT_HISTORY.operation_type = 1
                    GROUP BY tag
                    ORDER BY tag
                ",
            array(
                new \aportela\DatabaseWrapper\Param\StringParam(":session_user_id", \HomeDocs\UserSession::getUserId())
            )
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
                        DOCUMENT_HISTORY.operation_user_id = :session_user_id
                    AND
                        DOCUMENT_HISTORY.operation_type = 1
                    ORDER BY tag
                ",
            array(
                new \aportela\DatabaseWrapper\Param\StringParam(":session_user_id", \HomeDocs\UserSession::getUserId())
            )
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
