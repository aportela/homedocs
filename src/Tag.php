<?php

    declare(strict_types=1);

    namespace HomeDocs;

    class Tag {

        public static function getCloud(\HomeDocs\Database\DB $dbh) {
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
                    (new \HomeDocs\Database\DBParam())->str(":session_user_id", \HomeDocs\UserSession::getUserId())
                )
            );
            $results = array_map(
                function($item) {
                    $item->total = intval($item->total);
                    return($item);
                },
                $results
            );
            return($results);
        }

        public static function search(\HomeDocs\Database\DB $dbh) {
            $results = $dbh->query(
                "
                    SELECT
                        tag
                    FROM DOCUMENT_TAG
                    INNER JOIN DOCUMENT ON DOCUMENT.id = DOCUMENT_TAG.document_id
                    WHERE DOCUMENT.created_by_user_id = :session_user_id
                    ORDER BY tag
                ",
                array(
                    (new \HomeDocs\Database\DBParam())->str(":session_user_id", \HomeDocs\UserSession::getUserId())
                )
            );
            return($results);
        }

    }
?>