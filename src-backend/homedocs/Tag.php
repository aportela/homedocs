<?php

declare(strict_types=1);

namespace HomeDocs;

class Tag
{

    public static function getCloud(\aportela\DatabaseWrapper\DB $dbh)
    {
        $results = $dbh->query(
            "
                    SELECT
                        COUNT(*) AS total, tag
                    FROM DOCUMENT_TAG
                    INNER JOIN DOCUMENT ON DOCUMENT.id = DOCUMENT_TAG.document_id
                    WHERE DOCUMENT.created_by_user_id = :session_user_id
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

    public static function search(\aportela\DatabaseWrapper\DB $dbh)
    {
        $results = $dbh->query(
            "
                    SELECT
                        DISTINCT tag
                    FROM DOCUMENT_TAG
                    INNER JOIN DOCUMENT ON DOCUMENT.id = DOCUMENT_TAG.document_id
                    WHERE DOCUMENT.created_by_user_id = :session_user_id
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
