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

        public function __construct (string $id = "", string $title = "", string $description = "", $tags = array(), $files = array()) {
            $this->id = $id;
            $this->title = $title;
            $this->description = $description;
            $this->tags = $tags;
            $this->files = $files;
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

        private function validate() {
            if (! empty($this->id) && mb_strlen($this->id) == 36) {
                if (! empty($this->title) && mb_strlen($this->title) <= 128) {
                    if ((! empty($this->description) && mb_strlen($this->title) <= 4096) || empty($this->description)) {
                        if (is_array($this->tags) && count($this->tags) > 0) {
                            foreach($this->tags as $tag) {
                                if (empty($tag)) {
                                    throw new \HomeDocs\Exception\InvalidParamsException("tags");
                                } else if (mb_strlen($tag) > 32) {
                                    throw new \HomeDocs\Exception\InvalidParamsException("tags");
                                }
                            }
                        }
                    } else {
                        throw new \HomeDocs\Exception\InvalidParamsException("description");
                    }
                } else {
                    throw new \HomeDocs\Exception\InvalidParamsException("title");
                }
            } else {
                throw new \HomeDocs\Exception\InvalidParamsException("id");
            }
        }

        public function add(\HomeDocs\Database\DB $dbh) {
            $this->validate();
            $params = array(
                (new \HomeDocs\Database\DBParam())->str(":id", mb_strtolower($this->id)),
                (new \HomeDocs\Database\DBParam())->str(":title", $this->title),
                (new \HomeDocs\Database\DBParam())->str(":created_by_user_id", \HomeDocs\UserSession::getUserId())
            );
            if (! empty($this->description)) {
                $params[] = (new \HomeDocs\Database\DBParam())->str(":description", $this->description);
            } else {
                $params[] = (new \HomeDocs\Database\DBParam())->null(":description");
            }
            if ($dbh->execute(
                "
                    INSERT INTO DOCUMENT
                        (id, title, description, created_by_user_id, created_on_timestamp)
                    VALUES
                        (:id, :title, :description, :created_by_user_id, strftime('%s', 'now'))
                ",
                $params)
            ) {
                $tagsQuery = "
                    INSERT INTO DOCUMENT_TAG
                        (document_id, tag)
                    VALUES
                        (:document_id, :tag)
                ";
                foreach($this->tags as $tag) {
                    $params = array(
                        (new \HomeDocs\Database\DBParam())->str(":document_id", mb_strtolower($this->id)),
                        (new \HomeDocs\Database\DBParam())->str(":tag", mb_strtolower($tag))
                    );
                    $dbh->execute($tagsQuery, $params);
                }
            }
        }

        public function update(\HomeDocs\Database\DB $dbh) {
            $this->validate();
            $params = array(
                (new \HomeDocs\Database\DBParam())->str(":id", mb_strtolower($this->id)),
                (new \HomeDocs\Database\DBParam())->str(":title", $this->title),
            );
            if (! empty($this->description)) {
                $params[] = (new \HomeDocs\Database\DBParam())->str(":description", $this->description);
            } else {
                $params[] = (new \HomeDocs\Database\DBParam())->null(":description");
            }
            if ($dbh->execute(
                "
                   UPDATE DOCUMENT SET
                        title = :title,
                        description = :description
                   WHERE
                        id = :id
                ",
                $params)
            ) {
                $dbh->execute(
                    "
                        DELETE FROM DOCUMENT_TAG
                        WHERE document_id = :document_id
                    ",
                    array(
                        (new \HomeDocs\Database\DBParam())->str(":document_id", mb_strtolower($this->id)),
                    )
                );
                foreach($this->tags as $tag) {
                    $params = array(
                        (new \HomeDocs\Database\DBParam())->str(":document_id", mb_strtolower($this->id)),
                        (new \HomeDocs\Database\DBParam())->str(":tag", mb_strtolower($tag))
                    );
                    $dbh->execute(
                        "
                            INSERT INTO DOCUMENT_TAG
                                (document_id, tag)
                            VALUES
                                (:document_id, :tag)
                        "
                        ,
                        $params
                    );
                }
            }
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
                        (new \HomeDocs\Database\DBParam())->str(":id", mb_strtolower($this->id))
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
                    (new \HomeDocs\Database\DBParam())->str(":document_id", mb_strtolower($this->id))
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
                    (new \HomeDocs\Database\DBParam())->str(":document_id", mb_strtolower($this->id))
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

        public static function search(\HomeDocs\Database\DB $dbh, int $currentPage = 1, int $resultsPage = 16, $filter = array(), string $sortBy = "createdOnTimestamp", string $sortOrder = "DESC") {
            $data = new \stdClass();
            $data->pagination = new \stdClass();
            $data->results = array();
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

            $queryCount = sprintf('
                SELECT
                    COUNT (DOCUMENT.id) AS total
                FROM DOCUMENT
                %s
            ', $whereCondition);
            $result = $dbh->query($queryCount, $params);
            $data->pagination->currentPage = $currentPage;
            $data->pagination->resultsPage = $resultsPage;
            $data->pagination->totalResults = intval($result[0]->total);
            if ($data->pagination->resultsPage > 0) {
                $data->pagination->totalPages = ceil($data->pagination->totalResults / $resultsPage);
            } else {
                $data->pagination->totalPages = $data->pagination->totalResults > 0 ? 1: 0;
            }
            if ($data->pagination->totalResults > 0) {
                $sqlSortBy = "";
                switch($sortBy) {
                    case "title":
                        $sqlSortBy = "DOCUMENT.title";
                    break;
                    case "description":
                        $sqlSortBy = "DOCUMENT.description";
                    break;
                    case "fileCount":
                        $sqlSortBy = "TMP_FILE.fileCount";
                    break;
                    default:
                        $sqlSortBy = "DOCUMENT.created_on_timestamp";
                    break;
                }
                $data->results = $dbh->query(
                    sprintf(
                        "
                            SELECT
                                DOCUMENT.id, DOCUMENT.title, DOCUMENT.description, DOCUMENT.created_on_timestamp AS createdOnTimestamp, TMP_FILE.fileCount
                            FROM DOCUMENT
                            LEFT JOIN (
                                SELECT COUNT(*) AS fileCount, document_id
                                FROM DOCUMENT_FILE
                                GROUP BY document_id
                            ) TMP_FILE ON TMP_FILE.document_id = DOCUMENT.id
                            %s
                            ORDER BY %s COLLATE NOCASE %s
                            %s;
                        ",
                        $whereCondition,
                        $sqlSortBy,
                    $sortOrder == "DESC" ? "DESC": "ASC",
                    $data->pagination->resultsPage > 0 ? sprintf("LIMIT %d OFFSET %d", $data->pagination->resultsPage, $data->pagination->resultsPage * ($data->pagination->currentPage - 1)) : null
                    ), $params
                );
                $data->results = array_map(
                    function($item) {
                        $item->createdOnTimestamp = intval($item->createdOnTimestamp);
                        $item->fileCount = intval($item->fileCount);
                        return($item);
                    },
                    $data->results
                );
            }
            return($data);
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
            $results = array_map(
                function($item) {
                    $item->total = intval($item->total);
                    return($item);
                },
                $results
            );
            return($results);
        }
    }
?>
