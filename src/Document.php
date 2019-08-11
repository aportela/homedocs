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
                sprintf(
                    "
                        SELECT
                            id, title, uploaded_on AS uploadedOn
                        FROM DOCUMENT
                        ORDER BY uploaded_on DESC
                        LIMIT %d;
                    ",
                    $count
                )
            );
            return($results);
        }

        public static function search(\HomeDocs\Database\DB $dbh, $filter) {
            $queryConditions = array();
            $params = array();
            if (isset($filter["tags"]) && is_array($filter["tags"]) && count($filter["tags"]) > 0) {
                $tagParamNames = array();
                foreach($filter["tags"] as $i => $tag) {
                    $paramName = sprintf(":TAG_%03d", $i + 1);
                    $tagParamNames[] = $paramName;
                    $params[] = (new \HomeDocs\Database\DBParam())->str($paramName, $tag);
                }
                $queryConditions[] = sprintf(
                    "
                        EXISTS (
                            SELECT document_id
                            FROM DOCUMENT_TAG
                            WHERE DOCUMENT_TAG.document_id = DOCUMENT.id
                            AND DOCUMENT_TAG.tag IN (%s)
                        )
                    ",
                    implode(", ", $tagParamNames)
                );
            }
            $whereCondition = count($queryConditions) > 0 ? " WHERE " .  implode(" AND ", $queryConditions) : "";

            $results = $dbh->query(
                sprintf(
                    "
                        SELECT
                            id, title, description, TMP_FILE.fileCount
                        FROM DOCUMENT
                        LEFT JOIN (
                            SELECT COUNT(*) AS fileCount, document_id
                            FROM DOCUMENT_FILE
                            GROUP BY document_id
                        ) TMP_FILE ON TMP_FILE.document_id = DOCUMENT.id
                        %s
                        ORDER BY uploaded_on DESC
                        LIMIT 32;
                    ",
                    $whereCondition
                ), $params
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
                "
            );
            return($results);
        }
    }
?>
