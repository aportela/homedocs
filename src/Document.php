<?php

declare(strict_types=1);

namespace HomeDocs;

class Document
{

    public $id;
    public $title;
    public $description;
    public $createdOnTimestamp;
    public $createdBy;
    public $files;
    public $tags;

    public function __construct(string $id = "", string $title = "", string $description = "", $tags = array(), $files = array())
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->tags = $tags;
        $this->files = $files;
    }

    public static function searchRecent(\aportela\DatabaseWrapper\DB $dbh, int $count = 16)
    {
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
                new \aportela\DatabaseWrapper\Param\StringParam(":session_user_id", \HomeDocs\UserSession::getUserId())
            )
        );
        $results = array_map(
            function ($item) {
                $item->createdOnTimestamp = intval($item->createdOnTimestamp);
                $item->fileCount = intval($item->fileCount);
                return ($item);
            },
            $results
        );
        return ($results);
    }

    private function validate()
    {
        if (!empty($this->id) && mb_strlen($this->id) == 36) {
            if (!empty($this->title) && mb_strlen($this->title) <= 128) {
                if ((!empty($this->description) && mb_strlen($this->title) <= 4096) || empty($this->description)) {
                    if (is_array($this->tags) && count($this->tags) > 0) {
                        foreach ($this->tags as $tag) {
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

    public function add(\aportela\DatabaseWrapper\DB $dbh)
    {
        $this->validate();
        $params = array(
            (new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower($this->id))),
            (new \aportela\DatabaseWrapper\Param\StringParam(":title", $this->title)),
            (new \aportela\DatabaseWrapper\Param\StringParam(":created_by_user_id", \HomeDocs\UserSession::getUserId()))
        );
        if (!empty($this->description)) {
            $params[] = (new \aportela\DatabaseWrapper\Param\StringParam(":description", $this->description));
        } else {
            $params[] = (new \aportela\DatabaseWrapper\Param\NullParam(":description"));
        }
        if ($dbh->exec(
            "
                    INSERT INTO DOCUMENT
                        (id, title, description, created_by_user_id, created_on_timestamp)
                    VALUES
                        (:id, :title, :description, :created_by_user_id, strftime('%s', 'now'))
                ",
            $params
        )) {
            $tagsQuery = "
                    INSERT INTO DOCUMENT_TAG
                        (document_id, tag)
                    VALUES
                        (:document_id, :tag)
                ";
            foreach ($this->tags as $tag) {
                if (!empty($tag)) {
                    $params = array(
                        new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower($this->id)),
                        new \aportela\DatabaseWrapper\Param\StringParam(":tag", mb_strtolower($tag))
                    );
                    $dbh->exec($tagsQuery, $params);
                } else {
                    throw new \HomeDocs\Exception\InvalidParamsException("tag");
                }
            }
            $filesQuery = "
                    INSERT INTO DOCUMENT_FILE
                        (document_id, file_id)
                    VALUES
                        (:document_id, :file_id)
                ";
            foreach ($this->files as $file) {
                if (!empty($file->id)) {
                    $params = array(
                        new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower($this->id)),
                        new \aportela\DatabaseWrapper\Param\StringParam(":file_id", mb_strtolower($file->id))
                    );
                    $dbh->exec($filesQuery, $params);
                } else {
                    throw new \HomeDocs\Exception\InvalidParamsException("fileId");
                }
            }
        }
    }

    public function update(\aportela\DatabaseWrapper\DB $dbh)
    {
        $this->validate();
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower($this->id)),
            new \aportela\DatabaseWrapper\Param\StringParam(":title", $this->title)
        );
        if (!empty($this->description)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":description", $this->description);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":description");
        }
        if ($dbh->exec(
            "
                   UPDATE DOCUMENT SET
                        title = :title,
                        description = :description
                   WHERE
                        id = :id
                ",
            $params
        )) {
            $dbh->exec(
                "
                        DELETE FROM DOCUMENT_TAG
                        WHERE document_id = :document_id
                    ",
                array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower($this->id)),
                )
            );
            $tagsQuery = "
                    INSERT INTO DOCUMENT_TAG
                        (document_id, tag)
                    VALUES
                        (:document_id, :tag)
                ";
            foreach ($this->tags as $tag) {
                if (!empty($tag)) {
                    $params = array(
                        new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower($this->id)),
                        new \aportela\DatabaseWrapper\Param\StringParam(":tag", mb_strtolower($tag))
                    );
                    $dbh->exec($tagsQuery, $params);
                } else {
                    throw new \HomeDocs\Exception\InvalidParamsException("tag");
                }
            }
            $originalFiles = $this->getFiles($dbh);
            foreach ($originalFiles as $originalFile) {
                $notFound = true;
                foreach ($this->files as $file) {
                    if ($file->id == $originalFile->id) {
                        $notFound = false;
                    }
                }
                if ($notFound) {
                    $dbh->exec(
                        "
                                DELETE FROM DOCUMENT_FILE
                                WHERE document_id = :document_id
                                AND file_id = :file_id
                            ",
                        array(
                            new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower($this->id)),
                            new \aportela\DatabaseWrapper\Param\StringParam(":file_id", mb_strtolower($originalFile->id))
                        )
                    );
                    $file = new \HomeDocs\File($originalFile->id);
                    $file->remove($dbh);
                }
            }
            foreach ($this->files as $file) {
                if (!empty($file->id)) {
                    $notFound = true;
                    foreach ($originalFiles as $originalFile) {
                        if ($file->id == $originalFile->id) {
                            $notFound = false;
                        }
                    }
                    if ($notFound) {
                        $params = array(
                            new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower($this->id)),
                            new \aportela\DatabaseWrapper\Param\StringParam(":file_id", mb_strtolower($file->id))
                        );
                        $dbh->exec(
                            "
                                    INSERT INTO DOCUMENT_FILE
                                        (document_id, file_id)
                                    VALUES
                                        (:document_id, :file_id)
                                ",
                            $params
                        );
                    }
                } else {
                    throw new \HomeDocs\Exception\InvalidParamsException("fileId");
                }
            }
        }
    }

    public function delete(\aportela\DatabaseWrapper\DB $dbh)
    {
        if (!empty($this->id) && mb_strlen($this->id) == 36) {
            $originalFiles = $this->getFiles($dbh);
            foreach ($originalFiles as $file) {
                $file = new \HomeDocs\File($file->id);
                $file->remove($dbh);
            }

            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower($this->id)),
            );

            $dbh->exec(
                "
                        DELETE FROM DOCUMENT_TAG
                        WHERE document_id = :document_id
                    ",
                $params
            );
            $dbh->exec(
                "
                        DELETE FROM DOCUMENT_FILE
                        WHERE document_id = :document_id
                    ",
                $params
            );
            $dbh->exec(
                "
                        DELETE FROM DOCUMENT
                        WHERE id = :document_id
                    ",
                $params
            );
        } else {
            throw new \HomeDocs\Exception\InvalidParamsException("id");
        }
    }

    public function get(\aportela\DatabaseWrapper\DB $dbh)
    {
        if (!empty($this->id) && mb_strlen($this->id) == 36) {
            $data = $dbh->query(
                "
                        SELECT
                            title, description, created_on_timestamp AS createdOnTimestamp, created_by_user_id AS createdByUserId
                        FROM DOCUMENT
                        WHERE id = :id
                ",
                array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower($this->id))
                )
            );
            if (is_array($data) && count($data) == 1) {
                if ($data[0]->createdByUserId == \HomeDocs\UserSession::getUserId()) {
                    $this->title = $data[0]->title;
                    $this->description = $data[0]->description;
                    $this->createdOnTimestamp = intval($data[0]->createdOnTimestamp);
                    $this->tags = $this->getTags($dbh);
                    $this->files = $this->getFiles($dbh);
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

    private function getTags(\aportela\DatabaseWrapper\DB $dbh)
    {
        $tags = [];
        $data = $dbh->query(
            "
                    SELECT
                        tag
                    FROM DOCUMENT_TAG
                    INNER JOIN DOCUMENT ON DOCUMENT.id = DOCUMENT_TAG.document_id
                    WHERE DOCUMENT_TAG.document_id = :document_id
                    ORDER BY DOCUMENT_TAG.tag
                ",
            array(
                new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower($this->id))
            )
        );
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $item) {
                $tags[] = $item->tag;
            }
        }
        return ($tags);
    }

    private function getFiles(\aportela\DatabaseWrapper\DB $dbh)
    {
        $files = [];
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
                new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower($this->id))
            )
        );
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $item) {
                $files[] = new \HomeDocs\File(
                    $item->id,
                    $item->name,
                    intval($item->size),
                    null,
                    $item->uploadedOnTimestamp
                );
            }
        }
        return ($files);
    }

    public static function search(\aportela\DatabaseWrapper\DB $dbh, int $currentPage = 1, int $resultsPage = 16, $filter = array(), string $sortBy = "createdOnTimestamp", string $sortOrder = "DESC")
    {
        $data = new \stdClass();
        $data->pagination = new \stdClass();
        $data->documents = array();
        $queryConditions = array(
            " DOCUMENT.created_by_user_id = :session_user_id "
        );
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":session_user_id", \HomeDocs\UserSession::getUserId())
        );
        if (isset($filter["title"]) && !empty($filter["title"])) {
            // explode into words, remove duplicated & empty elements
            $words = array_filter(array_unique(explode(" ", trim(mb_strtolower($filter["title"])))));
            $totalWords = count($words);
            if ($totalWords > 0) {
                foreach ($words as $word) {
                    $paramName = sprintf(":TITLE_%03d", $totalWords);
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . $word . "%");
                    $queryConditions[] = sprintf(" DOCUMENT.title LIKE %s ", $paramName);
                    $totalWords--;
                }
            }
        }
        if (isset($filter["description"]) && !empty($filter["description"])) {
            // explode into words, remove duplicated & empty elements
            $words = array_filter(array_unique(explode(" ", trim(mb_strtolower($filter["description"])))));
            $totalWords = count($words);
            if ($totalWords > 0) {
                foreach ($words as $word) {
                    $paramName = sprintf(":DESCRIPTION_%03d", $totalWords);
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . $word . "%");
                    $queryConditions[] = sprintf(" DOCUMENT.description LIKE %s ", $paramName);
                    $totalWords--;
                }
            }
        }
        if (isset($filter["fromTimestampCondition"]) && $filter["fromTimestampCondition"] > 0) {
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":fromTimestamp", $filter["fromTimestampCondition"]);
            $queryConditions[] = " DOCUMENT.created_on_timestamp >= :fromTimestamp ";
        }
        if (isset($filter["toTimestampCondition"]) && $filter["toTimestampCondition"] > 0) {
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":toTimestamp", $filter["toTimestampCondition"]);
            $queryConditions[] = " DOCUMENT.created_on_timestamp <= :toTimestamp ";
        }
        if (isset($filter["tags"]) && is_array($filter["tags"]) && count($filter["tags"]) > 0) {
            $tagParamNames = array();
            foreach ($filter["tags"] as $i => $tag) {
                $paramName = sprintf(":TAG_%03d", $i + 1);
                $tagParamNames[] = $paramName;
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, $tag);
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
            $data->pagination->totalPages = $data->pagination->totalResults > 0 ? 1 : 0;
        }
        if ($data->pagination->totalResults > 0) {
            $sqlSortBy = "";
            switch ($sortBy) {
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
            $data->documents = $dbh->query(
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
                    $sortOrder == "DESC" ? "DESC" : "ASC",
                    $data->pagination->resultsPage > 0 ? sprintf("LIMIT %d OFFSET %d", $data->pagination->resultsPage, $data->pagination->resultsPage * ($data->pagination->currentPage - 1)) : null
                ),
                $params
            );
            $data->documents = array_map(
                function ($item) {
                    $item->createdOnTimestamp = intval($item->createdOnTimestamp);
                    $item->fileCount = intval($item->fileCount);
                    return ($item);
                },
                $data->documents
            );
        }
        return ($data);
    }
}
