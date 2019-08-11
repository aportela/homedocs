<?php

    declare(strict_types=1);

    namespace HomeDocs;

    class Document {

        public $id;
        public $title;
        public $description;
        public $createdOn;
        public $createdBy;
        public $fileCount;
        public $files;
        public $tags;

        public function __construct (string $id = "", string $title = "", string $description = "") {
            $this->id = $id;
            $this->title = $title;
            $this->description = $description;
        }

        public static function searchRecent(\HomeDocs\Database\DB $dbh, int $count = 16) {
            $results = $dbh->query(
                sprintf("
                    SELECT
                        id, title, uploaded_on AS uploadedOn
                    FROM DOCUMENT
                    ORDER BY uploaded_on DESC
                    LIMIT %d;
                ", $count),
                array()
            );
            return($results);
        }

        public static function search(\HomeDocs\Database\DB $dbh) {
            $results = $dbh->query(
                "
                    SELECT
                        id, title, description, TMP_FILE.fileCount
                    FROM DOCUMENT
                    LEFT JOIN (
                        SELECT COUNT(*) AS fileCount, document_id
                        FROM DOCUMENT_FILE
                        GROUP BY document_id
                    ) TMP_FILE ON TMP_FILE.document_id = DOCUMENT.id
                    ORDER BY uploaded_on DESC
                    LIMIT 32;
                ",
                array(
                )
            );
            return($results);
        }

        public static function searchTags(\HomeDocs\Database\DB $dbh) {
            $results = $dbh->query(
                "
                    SELECT
                        COUNT(*) AS total, tag FROM DOCUMENT_TAG
                    GROUP BY tag
                    ORDER BY tag
                ",
                array(
                )
            );
            return($results);
        }
    }
?>
