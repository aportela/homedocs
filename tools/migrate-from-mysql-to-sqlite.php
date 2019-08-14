<?php

    declare(strict_types=1);

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

    echo "HomeDocs migration script (old mysql version -> current sqlite version)" . PHP_EOL;

    $oldDatabaseSettings  = array(
        "OLD_DB_HOST" => 'localhost',
        "OLD_DB_PORT" => 3306,
        "OLD_DB_NAME" => 'homedocs',
        "OLD_DB_USERNAME" => '',
        "OLD_DB_PASSWORD" => ''
    );

    $oldStoragePath = 'c:\\Users\\z0mbie\\Documents\\GitHub\\homedocs\\storage\\';

    $app = (new \HomeDocs\App())->get();

    $missingExtensions = array_diff($app->getContainer()["settings"]["phpRequiredExtensions"], get_loaded_extensions());

    if (count($missingExtensions) > 0) {
        echo "Error: missing php extension/s: " . implode(", ", $missingExtensions) . PHP_EOL;
        exit;
    }

    $oldDatabaseHandler = new \PDO(
        sprintf(
            "mysql:dbname=%s;host=%s;port=%d",
            $oldDatabaseSettings["OLD_DB_NAME"],
            $oldDatabaseSettings["OLD_DB_HOST"],
            $oldDatabaseSettings["OLD_DB_PORT"]
        ),
        $oldDatabaseSettings["OLD_DB_USERNAME"],
        $oldDatabaseSettings["OLD_DB_PASSWORD"],
        array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        )
    );

    $mariaDBQueries = array(
        // TODO: create required mariadb function to convert old hex-binary uuids to standard varchar(36) uuids
        // USER table export
        "
            SELECT
                CONCAT(
                    \"REPLACE INTO `USER` (id, email, password_hash) VALUES ('\",
                    CONVERT_TO_UUID(ID),
                    \"', '\",
                    CONVERT(CAST(CONVERT(EMAIL USING LATIN1) AS BINARY) USING UTF8),
                    \"', '\",
                    PASSWORD,
                    \"');\"
                ) AS query
            FROM USER ;
        ",
        // FILE table export
        "
            SELECT
                CONCAT(
                    \"REPLACE INTO `FILE` (id, sha1_hash, name, size, uploaded_by_user_id, uploaded_on_timestamp) VALUES ('\",
                    CONVERT_TO_UUID(ID),
                    \"', '\",
                    SHA1_HASH,
                    \"', '\",
                    CONVERT(CAST(CONVERT(NAME USING UTF8) AS BINARY) USING UTF8),
                    \"', \",
                    SIZE,
                    \", '\",
                    CONVERT_TO_UUID(USER_ID),
                    \"', \",
                    UNIX_TIMESTAMP(UPLOADED),
                    \");\"
                ) AS query
            FROM FILE
            ORDER BY UPLOADED;
        ",
        // DOCUMENT table export
        "
            SELECT
                CONCAT(
                    \"REPLACE INTO `DOCUMENT` (id, title, description, created_by_user_id, created_on_timestamp) VALUES ('\",
                    CONVERT_TO_UUID(ID),
                    \"', '\",
                    CONVERT(CAST(CONVERT(TITLE USING UTF8) AS BINARY) USING UTF8),
                    \"', '\",
                    CONVERT(CAST(CONVERT(COALESCE(DESCRIPTION, \"\") USING UTF8) AS BINARY) USING UTF8),
                     \"', '\",
                     CONVERT_TO_UUID(USER_ID),
                    \"', \",
                    UNIX_TIMESTAMP(CREATED),
                    \");\"
                ) AS query
            FROM DOCUMENT
            ORDER BY CREATED;
        ",
        // set DOCUMENT NULL descriptions
        "
            SELECT
                \" UPDATE DOCUMENT
                    SET description = NULL
                WHERE description = ''
                \"
                AS query
        ",
        // DOCUMENT_FILE table export
        "
            SELECT
                CONCAT(
                    \"REPLACE INTO `DOCUMENT_FILE` (document_id, file_id) VALUES ('\",
                    CONVERT_TO_UUID(DOCUMENT_ID),
                    \"', '\",
                    CONVERT_TO_UUID(FILE_ID),
                    \"');\"
                ) AS query
            FROM DOCUMENT_FILE;
        ",
        // DOCUMENT_TAG table export
        "
            SELECT
                CONCAT(
                    \"REPLACE INTO `DOCUMENT_TAG` (document_id, tag) VALUES ('\",
                    CONVERT_TO_UUID(DOCUMENT_ID),
                    \"', '\",
                    CONVERT(CAST(CONVERT(TAG USING UTF8) AS BINARY) USING UTF8),
                    \"');\"
                ) AS query
            FROM DOCUMENT_TAG;
        "
    );
    $sqliteQueries = array(
        " DELETE FROM USER; ",
        " DELETE FROM FILE; ",
        " DELETE FROM DOCUMENT; ",
        " DELETE FROM DOCUMENT_FILE ",
        " DELETE FROM DOCUMENT_TAG; "
    );
    echo "Exporting queries from MariaDB...";
    foreach($mariaDBQueries as $mariaDBQuery) {
        $stmt = $oldDatabaseHandler->prepare($mariaDBQuery);
        if ($stmt->execute()) {
            while ($row = $stmt->fetchObject()) {
                $sqliteQueries[] = $row->query;
            }
        }
        $stmt->closeCursor();
    }
    // fix empty descriptions
    $sqliteQueries[] = " UPDATE DOCUMENT SET DESCRIPTION = NULL WHERE DESCRIPTION = ''; ";
    $totalSqliteQueries = count($sqliteQueries);
    echo "done!" . PHP_EOL;
    echo "Importing " . count($sqliteQueries) . " queries... " . PHP_EOL;
    $dbh = new \HomeDocs\Database\DB($app->getContainer());
    $i = 0;
    foreach($sqliteQueries as $sqliteQuery) {
        $dbh->execute($sqliteQuery);
        \HomeDocs\Utils::showProgressBar($i + 1, $totalSqliteQueries, 20);
        $i++;
    }

    $files = $dbh->query(" SELECT id, sha1_hash AS hash, name, size FROM FILE ");
    $totalFiles = count($files);
    echo "Copying " . $totalFiles . " storage files..." . PHP_EOL;
    $i = 0;
    $fileErrors = [];
    $fileHashErrors = [];
    foreach($files as $file) {
        $sourceFilePath = sprintf("%s/%s/%s/%s", rtrim($oldStoragePath, "/"), substr($file->hash, 0, 1), substr($file->hash, 1, 1), $file->hash);
        if (file_exists($sourceFilePath)) {
            $f = new \HomeDocs\File(
                $file->id
            );
            $destinationFilePath = $f->localStoragePath;
            $path = pathinfo($destinationFilePath);
            if (!file_exists($path['dirname'])) {
                mkdir($path['dirname'], 0777, true);
            }
            if (copy($sourceFilePath, $f->localStoragePath)) {
                \HomeDocs\Utils::showProgressBar($i + 1, $totalFiles, 20);
                $i++;
            } else {
                $fileErrors[] = sprintf("\tError copying file (%s): source path %s destination path %s%s", $file->name, $sourceFilePath, $destinationFilePath, PHP_EOL);
                $fileHashErrors[] = $file->hash;
            }
        } else {
            $fileErrors[] = sprintf("\tError copying file (%s): source path %s not found%s", $file->name, $sourceFilePath, PHP_EOL);
            $fileHashErrors[] = $file->hash;
        }
    }
    $totalFileErrors = count($fileErrors);
    if ($totalFileErrors > 0) {
        echo PHP_EOL , "Errors (" , $totalFileErrors , ") found while copying files:" , PHP_EOL;
        foreach($fileErrors as $fileError) {
            echo $fileError;
        }
        echo PHP_EOL , "You can try to find/recove this source files or purge from database executing (on sqlite) the following queries: " , PHP_EOL;

        echo sprintf(
            "\t
                DELETE FROM DOCUMENT_FILE
                WHERE EXISTS (
                    SELECT id
                    FROM FILE
                    WHERE FILE.id = DOCUMENT_FILE.file_id
                    AND FILE.sha1_hash IN (%s)
                );%s
            ",
            implode(
                ',',
                array_map(
                    function($str) {
                        return sprintf('"%s"', $str);
                    },
                    $fileHashErrors
                )
            ),
            PHP_EOL
        );
        echo sprintf(
            "\t
                DELETE FROM FILE
                WHERE sha1_hash IN (%s);%s
            ",
            implode(
                ',',
                array_map(
                    function($str) {
                        return sprintf('"%s"', $str);
                    },
                    $fileHashErrors
                )
            ),
            PHP_EOL
        );
    }

?>