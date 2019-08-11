<?php

    declare(strict_types=1);

    namespace HomeDocs;

    class Document {

        public $id;
        public $title;
        public $description;
        public $createdOnTimestamp;
        public $createdBy;
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
                            id, title, CAST(created_on_timestamp AS INT) AS createdOnTimestamp, TMP_FILE.fileCount
                        FROM DOCUMENT
                        LEFT JOIN (
                            SELECT COUNT(*) AS fileCount, document_id
                            FROM DOCUMENT_FILE
                            GROUP BY document_id
                        ) TMP_FILE ON TMP_FILE.document_id = DOCUMENT.id
                        WHERE created_by_user_id = :session_user_id
                        ORDER BY created_on_timestamp DESC
                        LIMIT %d;
                    ",
                    $count
                ),
                array(
                    (new \HomeDocs\Database\DBParam())->str(":session_user_id", \HomeDocs\UserSession::getUserId())
                )
            );
            $results = array_map(
                function($item) {
                    $item->createdOnTimestamp = intval($item->createdOnTimestamp);
                    $item->fileCount = intval($item->fileCount);
                    return($item);
                },
            $results
        );
            return($results);
        }

        public function get (\HomeDocs\Database\DB $dbh) {
            if (! empty($this->id) && mb_strlen($this->id) == 36) {
                $data = $dbh->query(
                    "
                        SELECT
                            title, description, created_on_timestamp AS createdOnTimestamp, created_by_user_id AS createdByUserId
                        FROM DOCUMENT
                        WHERE id = :id
                    ",
                    array(
                        (new \HomeDocs\Database\DBParam())->str(":id", $this->id)
                    )
                );
                if (is_array($data) && count($data) == 1) {
                    if ($data[0]->createdByUserId == \HomeDocs\UserSession::getUserId()) {
                        $this->title = $data[0]->title;
                        $this->description = $data[0]->description;
                        $this->createdOnTimestamp = intval($data[0]->createdOnTimestamp);
                        $this->getTags($dbh);
                        $this->getFiles($dbh);
                    } else {
                        throw new \HomeDocs\Exception\AccessDeniedException("id");
                    }
                } else {
                    throw new \HomeDocs\Exception\NotFoundException("id");
                }
            } else {
                throw new \HomeDocs\Exception\InvalidParamsException("id");
            }
        }

        private function getTags (\HomeDocs\Database\DB $dbh) {
            $data = $dbh->query(
                "
                    SELECT
                        tag
                    FROM DOCUMENT_TAG
                    INNER JOIN DOCUMENT ON DOCUMENT.id = DOCUMENT_TAG.document_id
                    WHERE DOCUMENT_TAG.document_id = :document_id
                ",
                array(
                    (new \HomeDocs\Database\DBParam())->str(":document_id", $this->id)
                )
            );
            if (is_array($data) && count($data) > 0) {
                foreach($data as $item) {
                    $this->tags[] = $item->tag;
                }
            } else {
                $this->tags = [];
            }
        }

        private function getFiles (\HomeDocs\Database\DB $dbh) {
            $data = $dbh->query(
                "
                    SELECT
                        FILE.id, FILE.name, FILE.size, FILE.uploaded_on_timestamp AS uploadedOnTimestamp
                    FROM DOCUMENT_FILE
                    INNER JOIN DOCUMENT ON DOCUMENT.id = DOCUMENT_FILE.document_id
                    LEFT JOIN FILE ON FILE.id = DOCUMENT_FILE.file_id
                    WHERE DOCUMENT_FILE.document_id = :document_id
                    ORDER BY FILE.name, FILE.uploaded_on_timestamp
                ",
                array(
                    (new \HomeDocs\Database\DBParam())->str(":document_id", $this->id)
                )
            );
            if (is_array($data) && count($data) > 0) {
                foreach($data as $item) {
                    $this->files[] = new \HomeDocs\File(
                        $item->id,
                        $item->name,
                        intval($item->size),
                        $item->uploadedOnTimestamp
                    );
                }
            } else {
                $this->files = [];
            }
        }

        public static function search(\HomeDocs\Database\DB $dbh, $filter) {
            $queryConditions = array();
            $params = array();
            if (isset($filter["title"]) && ! empty($filter["title"])) {
                $queryConditions[] = sprintf(" DOCUMENT.title LIKE :title ");
                $params[] = (new \HomeDocs\Database\DBParam())->str(":title", "%" . $filter["title"] . "%");
            }
            if (isset($filter["description"]) && ! empty($filter["description"])) {
                $queryConditions[] = sprintf(" DOCUMENT.description LIKE :description ");
                $params[] = (new \HomeDocs\Database\DBParam())->str(":description", "%" . $filter["description"]. "%");
            }
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
                            DOCUMENT.id, DOCUMENT.title, DOCUMENT.description, DOCUMENT.created_on_timestamp AS createdOnTimestamp,  TMP_FILE.fileCount
                        FROM DOCUMENT
                        LEFT JOIN (
                            SELECT COUNT(*) AS fileCount, document_id
                            FROM DOCUMENT_FILE
                            GROUP BY document_id
                        ) TMP_FILE ON TMP_FILE.document_id = DOCUMENT.id
                        %s
                        ORDER BY DOCUMENT.created_on_timestamp DESC
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
            return($results);
        }
    }
?>
