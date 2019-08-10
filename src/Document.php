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

        public static function search(\HomeDocs\Database\DB $dbh) {
            $results = $dbh->query(
                "
                    SELECT
                        id, title, uploaded_on AS uploadedOn
                    FROM DOCUMENT
                    ORDER BY uploaded_on DESC
                    LIMIT 16;
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
