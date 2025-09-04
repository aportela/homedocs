<?php

declare(strict_types=1);

namespace HomeDocs;

class Stats
{
    public static function documentsCount(\aportela\DatabaseWrapper\DB $dbh): int
    {
        $result = $dbh->query(
            "
                SELECT
                    COALESCE(COUNT(DOCUMENT.id), 0) AS total
                FROM DOCUMENT
                WHERE
                    DOCUMENT.created_by_user_id = :session_user_id
            ",
            array(
                new \aportela\DatabaseWrapper\Param\StringParam(":session_user_id", \HomeDocs\UserSession::getUserId())
            )
        );
        return (intval($result[0]->total));
    }

    public static function attachmentsCount(\aportela\DatabaseWrapper\DB $dbh): int
    {
        $result = $dbh->query(
            "
                SELECT
                    COALESCE(COUNT(DOCUMENT_FILE.file_id), 0) AS total
                FROM DOCUMENT_FILE
                INNER JOIN DOCUMENT ON DOCUMENT.id = DOCUMENT_FILE.document_id
                WHERE
                    DOCUMENT.created_by_user_id = :session_user_id
            ",
            array(
                new \aportela\DatabaseWrapper\Param\StringParam(":session_user_id", \HomeDocs\UserSession::getUserId())
            )
        );
        return (intval($result[0]->total));
    }

    public static function attachmentsDiskSize(\aportela\DatabaseWrapper\DB $dbh): int
    {
        $result = $dbh->query(
            "
                SELECT
                    COALESCE(SUM(FILE.size), 0) AS total
                FROM DOCUMENT_FILE
                INNER JOIN DOCUMENT ON DOCUMENT.id = DOCUMENT_FILE.document_id
                INNER JOIN FILE ON FILE.id = DOCUMENT_FILE.file_id
                WHERE
                    DOCUMENT.created_by_user_id = :session_user_id
            ",
            array(
                new \aportela\DatabaseWrapper\Param\StringParam(":session_user_id", \HomeDocs\UserSession::getUserId())
            )
        );
        return (intval($result[0]->total));
    }
}
