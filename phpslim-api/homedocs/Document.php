<?php

declare(strict_types=1);

namespace HomeDocs;

class Document
{
    public ?string $id;
    public ?string $title;
    public ?string $description;
    public ?int $createdOnTimestamp;
    public ?int $lastUpdateTimestamp;
    public ?string $createdBy;
    public ?array $files = array();
    public ?array $notes = array();
    public ?string $rootStoragePath;
    public ?array $tags = array();
    public ?array $history = array();

    public function __construct(string $id = "", string $title = "", string $description = "", $tags = array(), $files = array(), $notes = array())
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->tags = $tags;
        $this->files = $files;
        $this->notes = $notes;
    }

    public function setRootStoragePath(string $rootStoragePath): void
    {
        $this->rootStoragePath = $rootStoragePath;
    }

    public static function searchRecent(\aportela\DatabaseWrapper\DB $dbh, int $count = 16): array
    {
        $results = $dbh->query(
            sprintf(
                "
                    WITH DOCUMENTS_FILES AS (
                        SELECT DOCUMENT_FILE.document_id, COUNT(*) AS fileCount
                        FROM DOCUMENT_FILE
                        INNER JOIN DOCUMENT_HISTORY ON DOCUMENT_HISTORY.document_id = DOCUMENT_FILE.document_id AND DOCUMENT_HISTORY.operation_type = :history_operation_add AND DOCUMENT_HISTORY.operation_user_id = :session_user_id
                        GROUP BY DOCUMENT_FILE.document_id
                    ),
                    DOCUMENTS_NOTES AS (
                        SELECT DOCUMENT_NOTE.document_id, COUNT(*) AS noteCount
                        FROM DOCUMENT_NOTE
                        INNER JOIN DOCUMENT_HISTORY ON DOCUMENT_HISTORY.document_id = DOCUMENT_NOTE.document_id AND DOCUMENT_HISTORY.operation_type = :history_operation_add AND DOCUMENT_HISTORY.operation_user_id = :session_user_id
                        GROUP BY DOCUMENT_NOTE.document_id
                    ),
                    DOCUMENTS_TAGS AS (
                        SELECT document_id, GROUP_CONCAT(tag, ',') AS tags
                        FROM (
                            SELECT DOCUMENT_TAG.document_id, DOCUMENT_TAG.tag
                            FROM DOCUMENT_TAG
                            INNER JOIN DOCUMENT_HISTORY ON DOCUMENT_HISTORY.document_id = DOCUMENT_TAG.document_id AND DOCUMENT_HISTORY.operation_type = :history_operation_add AND DOCUMENT_HISTORY.operation_user_id = :session_user_id
                            GROUP BY DOCUMENT_TAG.document_id, DOCUMENT_TAG.tag
                            ORDER BY DOCUMENT_TAG.tag
                        )
                        GROUP BY document_id
                    ),
                    DOCUMENTS_LAST_HISTORY_OPERATION AS (
                        SELECT document_id, MAX(operation_date) AS max_operation_date
                        FROM DOCUMENT_HISTORY
                        GROUP BY document_id
                    )
                    SELECT
                        DOCUMENT.id,
                        DOCUMENT.title,
                        DOCUMENT.description,
                        CAST(DOCUMENTS_LAST_HISTORY_OPERATION.max_operation_date AS INT) AS lastUpdateTimestamp,
                        COALESCE(DOCUMENTS_FILES.fileCount, 0) AS fileCount,
                        COALESCE(DOCUMENTS_NOTES.noteCount, 0) AS noteCount,
                        DOCUMENTS_TAGS.tags
                    FROM DOCUMENT
                    INNER JOIN DOCUMENT_HISTORY ON DOCUMENT_HISTORY.document_id = DOCUMENT.id AND DOCUMENT_HISTORY.operation_type = :history_operation_add AND DOCUMENT_HISTORY.operation_user_id = :session_user_id
                    LEFT JOIN DOCUMENTS_FILES ON DOCUMENTS_FILES.document_id = DOCUMENT.id
                    LEFT JOIN DOCUMENTS_TAGS ON DOCUMENTS_TAGS.document_id = DOCUMENT.id
                    LEFT JOIN DOCUMENTS_NOTES ON DOCUMENTS_NOTES.document_id = DOCUMENT.id
                    LEFT JOIN DOCUMENTS_LAST_HISTORY_OPERATION ON DOCUMENTS_LAST_HISTORY_OPERATION.document_id = DOCUMENT.id
                    ORDER BY DOCUMENTS_LAST_HISTORY_OPERATION.max_operation_date DESC
                    LIMIT %d;
                ",
                $count
            ),
            array(
                new \aportela\DatabaseWrapper\Param\IntegerParam(":history_operation_add", \HomeDocs\DocumentHistoryOperation::OPERATION_ADD_DOCUMENT),
                new \aportela\DatabaseWrapper\Param\StringParam(":session_user_id", \HomeDocs\UserSession::getUserId())
            )
        );
        $results = array_map(
            function ($item) {
                $item->lastUpdateTimestamp = intval($item->lastUpdateTimestamp);
                $item->fileCount = intval($item->fileCount);
                $item->noteCount = intval($item->noteCount);
                $item->tags = $item->tags ? explode(",", $item->tags) : [];
                return ($item);
            },
            $results
        );
        return ($results);
    }

    // TODO: define MAX_LENGTH FOR TITLE/DESCRIPTION/TAG/NOTE_BODY consts
    private function validate(): void
    {
        if (!empty($this->id) && mb_strlen($this->id) == 36) {
            if (!empty($this->title) && mb_strlen($this->title) <= 128) {
                if ((!empty($this->description) && mb_strlen($this->description) <= 4096) || empty($this->description)) {
                    if (is_array($this->tags) && count($this->tags) > 0) {
                        foreach ($this->tags as $tag) {
                            if (empty($tag)) {
                                throw new \HomeDocs\Exception\InvalidParamsException("tags");
                            } elseif (mb_strlen($tag) > 32) {
                                throw new \HomeDocs\Exception\InvalidParamsException("tags");
                            }
                        }
                    }
                    if (is_array($this->notes) && count($this->notes) > 0) {
                        foreach ($this->notes as $note) {
                            if (! empty($note->id) && mb_strlen($note->id) == 36) {
                                if (! (!empty($note->body) && mb_strlen($note->body) <= 16384)) {
                                    throw new \HomeDocs\Exception\InvalidParamsException("note_body");
                                }
                            } else {
                                $note->id = \HomeDocs\Utils::uuidv4();
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

    public function add(\aportela\DatabaseWrapper\DB $dbh): void
    {
        $this->validate();
        $params = array(
            (new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower($this->id))),
            (new \aportela\DatabaseWrapper\Param\StringParam(":title", $this->title)),
        );
        if (!empty($this->description)) {
            $params[] = (new \aportela\DatabaseWrapper\Param\StringParam(":description", $this->description));
        } else {
            $params[] = (new \aportela\DatabaseWrapper\Param\NullParam(":description"));
        }
        if ($dbh->exec(
            "
                    INSERT INTO DOCUMENT
                        (id, title, description)
                    VALUES
                        (:id, :title, :description)
                ",
            $params
        )) {
            $historyQuery = "
                INSERT INTO DOCUMENT_HISTORY
                    (document_id, operation_date, operation_type, operation_user_id)
                VALUES
                    (:document_id, strftime('%s', 'now'), 1, :created_by_user_id)
            ";
            $params = [
                new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower($this->id)),
                (new \aportela\DatabaseWrapper\Param\StringParam(":created_by_user_id", \HomeDocs\UserSession::getUserId()))
            ];
            if ($dbh->exec($historyQuery, $params)) {
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
                $notesQuery = "
                    INSERT INTO DOCUMENT_NOTE
                        (note_id, document_id, created_on_timestamp, created_by_user_id, body)
                    VALUES
                        (:note_id, :document_id, strftime('%s', 'now'), :created_by_user_id, :note_body)
                ";
                foreach ($this->notes as $note) {
                    $params = array(
                        new \aportela\DatabaseWrapper\Param\StringParam(":note_id", mb_strtolower($note->id)),
                        new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower($this->id)),
                        new \aportela\DatabaseWrapper\Param\StringParam(":created_by_user_id", \HomeDocs\UserSession::getUserId()),
                        new \aportela\DatabaseWrapper\Param\StringParam(":note_body", $note->body),
                    );
                    $dbh->exec($notesQuery, $params);
                }
            }
        }
    }

    public function update(\aportela\DatabaseWrapper\DB $dbh): void
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
            // TODO: fail with multiple updates on same timestamp
            $historyQuery = "
                INSERT INTO DOCUMENT_HISTORY
                    (document_id, operation_date, operation_type, operation_user_id)
                VALUES
                    (:document_id, strftime('%s', 'now'), 2, :created_by_user_id)
            ";
            $params = [
                new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower($this->id)),
                (new \aportela\DatabaseWrapper\Param\StringParam(":created_by_user_id", \HomeDocs\UserSession::getUserId()))
            ];
            if ($dbh->exec($historyQuery, $params)) {
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
                        $file = new \HomeDocs\File($this->rootStoragePath, $originalFile->id);
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
                $originalNotes = $this->getNotes($dbh);
                foreach ($originalNotes as $originalNote) {
                    $notFound = true;
                    foreach ($this->notes as $note) {
                        if ($note->id == $originalNote->id) {
                            $notFound = false;
                        }
                    }
                    if ($notFound) {
                        $dbh->exec(
                            "
                                    DELETE FROM DOCUMENT_NOTE
                                    WHERE document_id = :document_id
                                    AND note_id = :note_id
                                ",
                            array(
                                new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower($this->id)),
                                new \aportela\DatabaseWrapper\Param\StringParam(":note_id", mb_strtolower($originalNote->id))
                            )
                        );
                    }
                }
                foreach ($this->notes as $note) {
                    if (empty($note->id)) {
                        $note->id = \HomeDocs\Utils::uuidv4();
                    }
                    $notFound = true;
                    foreach ($originalNotes as $originalNote) {
                        if ($note->id == $originalNote->id) {
                            $notFound = false;
                            if ($note->body !== $originalNote->body) {
                                $noteQuery = "
                                    UPDATE DOCUMENT_NOTE SET
                                        body = :note_body
                                    WHERE
                                        note_id = :note_id
                                    AND
                                        document_id =:document_id
                                    AND
                                        created_by_user_id = :created_by_user_id
                                ";
                                $params = array(
                                    new \aportela\DatabaseWrapper\Param\StringParam(":note_id", mb_strtolower($note->id)),
                                    new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower($this->id)),
                                    new \aportela\DatabaseWrapper\Param\StringParam(":created_by_user_id", \HomeDocs\UserSession::getUserId()),
                                    new \aportela\DatabaseWrapper\Param\StringParam(":note_body", $note->body),
                                );
                                $dbh->exec($noteQuery, $params);
                                break;
                            } else {
                                break;
                            }
                        }
                    }
                    if ($notFound) {
                        $params = array(
                            new \aportela\DatabaseWrapper\Param\StringParam(":note_id", mb_strtolower($note->id)),
                            new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower($this->id)),
                            new \aportela\DatabaseWrapper\Param\StringParam(":created_by_user_id", \HomeDocs\UserSession::getUserId()),
                            new \aportela\DatabaseWrapper\Param\StringParam(":note_body", $note->body),
                        );
                        $dbh->exec(
                            "
                                INSERT INTO DOCUMENT_NOTE
                                    (note_id, document_id, created_on_timestamp, created_by_user_id, body)
                                VALUES
                                    (:note_id, :document_id, strftime('%s', 'now'), :created_by_user_id, :note_body)
                            ",
                            $params
                        );
                    }
                }
            }
        }
    }

    public function delete(\aportela\DatabaseWrapper\DB $dbh): void
    {
        if (!empty($this->id) && mb_strlen($this->id) == 36) {
            $originalFiles = $this->getFiles($dbh);
            foreach ($originalFiles as $file) {
                $file = new \HomeDocs\File($this->rootStoragePath, $file->id);
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

    public function get(\aportela\DatabaseWrapper\DB $dbh): void
    {
        if (!empty($this->id) && mb_strlen($this->id) == 36) {
            $data = $dbh->query(
                "
                        SELECT
                            title, description, DOCUMENT_HISTORY.operation_date AS createdOnTimestamp, COALESCE(HISTORY_LAST_UPDATE.document_last_update, DOCUMENT_HISTORY.operation_date) AS lastUpdateTimestamp, DOCUMENT_HISTORY.operation_user_id AS createdByUserId
                        FROM DOCUMENT
                        INNER JOIN DOCUMENT_HISTORY ON DOCUMENT_HISTORY.document_id = DOCUMENT.id AND DOCUMENT_HISTORY.operation_type = :history_operation_add
                        LEFT JOIN (
                            SELECT DOCUMENT_HISTORY.document_id, MAX(DOCUMENT_HISTORY.operation_date) AS document_last_update
                            FROM DOCUMENT_HISTORY
                            WHERE DOCUMENT_HISTORY.document_id = :id
                            AND DOCUMENT_HISTORY.operation_type <> :history_operation_add
                            GROUP BY DOCUMENT_HISTORY.document_id
                        ) HISTORY_LAST_UPDATE ON HISTORY_LAST_UPDATE.document_id = DOCUMENT.id
                        WHERE id = :id
                ",
                array(
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":history_operation_add", \HomeDocs\DocumentHistoryOperation::OPERATION_ADD_DOCUMENT),
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower($this->id))
                )
            );
            if (is_array($data) && count($data) == 1) {
                if ($data[0]->createdByUserId == \HomeDocs\UserSession::getUserId()) {
                    $this->title = $data[0]->title;
                    $this->description = $data[0]->description;
                    $this->createdOnTimestamp = intval($data[0]->createdOnTimestamp);
                    $this->lastUpdateTimestamp = intval($data[0]->lastUpdateTimestamp);
                    $this->tags = $this->getTags($dbh);
                    $this->files = $this->getFiles($dbh);
                    $this->notes = $this->getNotes($dbh);
                    $this->history = $this->getHistory($dbh);
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

    private function getTags(\aportela\DatabaseWrapper\DB $dbh): array
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

    private function getFiles(\aportela\DatabaseWrapper\DB $dbh): array
    {
        $files = [];
        $data = $dbh->query(
            "
                    SELECT
                        FILE.id, FILE.name, FILE.size, FILE.sha1_hash AS hash, FILE.uploaded_on_timestamp AS uploadedOnTimestamp
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
                    $this->rootStoragePath,
                    $item->id,
                    $item->name,
                    intval($item->size),
                    $item->hash,
                    $item->uploadedOnTimestamp
                );
            }
        }
        return ($files);
    }

    private function getNotes(\aportela\DatabaseWrapper\DB $dbh, string $search = null): array
    {
        $notes = [];
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower($this->id))
        );
        $queryConditions = [];
        if (! empty($search)) {
            // explode into words, remove duplicated & empty elements
            $words = array_filter(array_unique(explode(" ", trim(mb_strtolower($search)))));
            $totalWords = count($words);
            if ($totalWords > 0) {
                foreach ($words as $word) {
                    $paramName = sprintf(":BODY_%03d", $totalWords);
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . $word . "%");
                    $queryConditions[] = sprintf(" DOCUMENT_NOTE.body LIKE %s ", $paramName);
                    $totalWords--;
                }
            }
        }
        $data = $dbh->query(
            sprintf(
                "
                    SELECT
                        DOCUMENT_NOTE.note_id AS noteId, DOCUMENT_NOTE.created_on_timestamp AS createdOnTimestamp, DOCUMENT_NOTE.body
                    FROM DOCUMENT_NOTE
                    WHERE DOCUMENT_NOTE.document_id = :document_id
                    %s
                    ORDER BY DOCUMENT_NOTE.created_on_timestamp DESC
                ",
                ! empty($search) ?
                    "
                    AND (
                        " .  implode(" AND ", $queryConditions) . "
                    )"
                    :
                    ""
            ),
            $params
        );
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $item) {
                $notes[] = new \HomeDocs\Note(
                    $item->noteId,
                    $item->createdOnTimestamp,
                    $item->body
                );
            }
        }
        return ($notes);
    }

    private function getHistory(\aportela\DatabaseWrapper\DB $dbh): array
    {
        $operations = [];
        $operations = $dbh->query(
            "
                    SELECT
                        DOCUMENT_HISTORY.operation_date AS operationTimestamp, DOCUMENT_HISTORY.operation_type AS operationType
                    FROM DOCUMENT_HISTORY
                    WHERE DOCUMENT_HISTORY.document_id = :document_id
                    ORDER BY DOCUMENT_HISTORY.operation_date DESC
                ",
            array(
                new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower($this->id))
            )
        );
        return ($operations);
    }

    public static function search(\aportela\DatabaseWrapper\DB $dbh, int $currentPage = 1, int $resultsPage = 16, $filter = array(), string $sortBy = "createdOnTimestamp", string $sortOrder = "DESC"): \stdClass
    {
        $data = new \stdClass();
        $data->pagination = new \stdClass();
        $data->documents = array();
        $queryConditions = array();
        $params = array(
            new \aportela\DatabaseWrapper\Param\IntegerParam(":history_operation_add", \HomeDocs\DocumentHistoryOperation::OPERATION_ADD_DOCUMENT),
            new \aportela\DatabaseWrapper\Param\IntegerParam(":history_operation_update", \HomeDocs\DocumentHistoryOperation::OPERATION_UPDATE_DOCUMENT),
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
        if (isset($filter["notesBody"]) && !empty($filter["notesBody"])) {
            // explode into words, remove duplicated & empty elements
            $words = array_filter(array_unique(explode(" ", trim(mb_strtolower($filter["notesBody"])))));
            $totalWords = count($words);
            if ($totalWords > 0) {
                $notesConditions = [];
                foreach ($words as $word) {
                    $paramName = sprintf(":NOTES_BODY_%03d", $totalWords);
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . $word . "%");
                    $notesConditions[] = sprintf(" DOCUMENT_NOTE.body LIKE %s ", $paramName);
                    $totalWords--;
                }
                $queryConditions[] = sprintf("
                    EXISTS (
                        SELECT DOCUMENT_NOTE.document_id
                        FROM DOCUMENT_NOTE
                        WHERE DOCUMENT_NOTE.DOCUMENT_ID = DOCUMENT.id
                        AND (%s)
                    )
                ", implode(" AND ", $notesConditions));
            }
        }
        if (isset($filter["fromCreationTimestampCondition"]) && $filter["fromCreationTimestampCondition"] > 0) {
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":fromCreationTimestamp", $filter["fromCreationTimestampCondition"]);
            $queryConditions[] = " DOCUMENT_HISTORY_CREATION_DATE.operation_date >= :fromCreationTimestamp ";
        }
        if (isset($filter["toCreationTimestampCondition"]) && $filter["toCreationTimestampCondition"] > 0) {
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":toCreationTimestamp", $filter["toCreationTimestampCondition"]);
            $queryConditions[] = " DOCUMENT_HISTORY_CREATION_DATE.operation_date <= :toCreationTimestamp ";
        }
        if (isset($filter["fromLastUpdateTimestampCondition"]) && $filter["fromLastUpdateTimestampCondition"] > 0) {
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":fromLastUpdateTimestamp", $filter["fromLastUpdateTimestampCondition"]);
            $queryConditions[] = " COALESCE(DOCUMENT_HISTORY_LAST_UPDATE.operation_date, DOCUMENT_HISTORY_CREATION_DATE.operation_date) >= :fromLastUpdateTimestamp ";
        }
        if (isset($filter["toLastUpdateTimestampCondition"]) && $filter["toLastUpdateTimestampCondition"] > 0) {
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":toLastUpdateTimestamp", $filter["toLastUpdateTimestampCondition"]);
            $queryConditions[] = " COALESCE(DOCUMENT_HISTORY_LAST_UPDATE.operation_date, DOCUMENT_HISTORY_CREATION_DATE.operation_date) <= :toLastUpdateTimestamp ";
        }
        if (isset($filter["fromUpdatedOnTimestampCondition"]) && $filter["fromUpdatedOnTimestampCondition"] > 0  && isset($filter["toUpdatedOnTimestampCondition"]) && $filter["toUpdatedOnTimestampCondition"] > 0) {

            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":fromUpdatedOnTimestamp", $filter["fromUpdatedOnTimestampCondition"]);
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":toUpdatedOnTimestamp", $filter["toUpdatedOnTimestampCondition"]);
            $queryConditions[] = "
                EXISTS (
                    SELECT
                        DOCUMENT_HISTORY_UPDATED_ON.document_id
                    FROM DOCUMENT_HISTORY AS DOCUMENT_HISTORY_UPDATED_ON
                    WHERE DOCUMENT_HISTORY_UPDATED_ON.document_id = DOCUMENT.id
                    AND DOCUMENT_HISTORY_UPDATED_ON.operation_date >= :fromUpdatedOnTimestamp
                    AND DOCUMENT_HISTORY_UPDATED_ON.operation_date <= :toUpdatedOnTimestamp
                )
            ";
        } else if (isset($filter["fromUpdatedOnTimestampCondition"]) && $filter["fromUpdatedOnTimestampCondition"] > 0) {

            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":fromUpdatedOnTimestamp", $filter["fromUpdatedOnTimestampCondition"]);
            $queryConditions[] = "
                EXISTS (
                    SELECT
                        DOCUMENT_HISTORY_UPDATED_ON.document_id
                    FROM DOCUMENT_HISTORY AS DOCUMENT_HISTORY_UPDATED_ON
                    WHERE DOCUMENT_HISTORY_UPDATED_ON.document_id = DOCUMENT.id
                    AND DOCUMENT_HISTORY_UPDATED_ON.operation_date >= :fromUpdatedOnTimestamp
                )
            ";
        } else if (isset($filter["toUpdatedOnTimestampCondition"]) && $filter["toUpdatedOnTimestampCondition"] > 0) {
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":toUpdatedOnTimestamp", $filter["toUpdatedOnTimestampCondition"]);
            $queryConditions[] = "
                EXISTS (
                    SELECT
                        DOCUMENT_HISTORY_UPDATED_ON.document_id
                    FROM DOCUMENT_HISTORY AS DOCUMENT_HISTORY_UPDATED_ON
                    WHERE DOCUMENT_HISTORY_UPDATED_ON.document_id = DOCUMENT.id
                    AND DOCUMENT_HISTORY_UPDATED_ON.operation_date <= :toUpdatedOnTimestamp
                )
            ";
        }
        if (isset($filter["tags"]) && is_array($filter["tags"]) && count($filter["tags"]) > 0) {
            foreach ($filter["tags"] as $i => $tag) {
                $paramName = sprintf(":TAG_%03d", $i + 1);
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, $tag);
                $queryConditions[] = sprintf(
                    "
                            EXISTS (
                                SELECT document_id
                                FROM DOCUMENT_TAG
                                WHERE DOCUMENT_TAG.document_id = DOCUMENT.id
                                AND DOCUMENT_TAG.tag IN (%s)
                            )
                        ",
                    $paramName
                );
            }
        }
        $whereCondition = count($queryConditions) > 0 ? " WHERE " .  implode(" AND ", $queryConditions) : "";

        // TODO: only LEFT JOIN LAST UPDATE IF REQUIRED BY FILTERS
        $queryCount = sprintf('
                SELECT
                    COUNT (DOCUMENT.id) AS total
                FROM DOCUMENT
                INNER JOIN DOCUMENT_HISTORY AS DOCUMENT_HISTORY_CREATION_DATE ON (
                    DOCUMENT_HISTORY_CREATION_DATE.document_id = DOCUMENT.id
                    AND DOCUMENT_HISTORY_CREATION_DATE.operation_user_id = :session_user_id
                    AND DOCUMENT_HISTORY_CREATION_DATE.operation_type = :history_operation_add
                )
                LEFT JOIN (
                    SELECT
                        DOCUMENT_HISTORY.document_id, MAX(DOCUMENT_HISTORY.operation_date) AS operation_date
                    FROM DOCUMENT_HISTORY
                    WHERE DOCUMENT_HISTORY.operation_type = :history_operation_update
                    AND DOCUMENT_HISTORY.operation_user_id = :session_user_id
                    GROUP BY DOCUMENT_HISTORY.document_id
                ) DOCUMENT_HISTORY_LAST_UPDATE ON DOCUMENT_HISTORY_LAST_UPDATE.document_id = DOCUMENT.id
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
                    $sqlSortBy = "TMP_FILE_COUNT.fileCount";
                    break;
                case "noteCount":
                    $sqlSortBy = "TMP_NOTE_COUNT.noteCount";
                    break;
                case "createdOnTimestamp":
                    $sqlSortBy = "DOCUMENT_HISTORY_CREATION_DATE.operation_date";
                    break;
                case "lastUpdateTimestamp":
                    $sqlSortBy = "COALESCE(DOCUMENT_HISTORY_LAST_UPDATE.operation_date, DOCUMENT_HISTORY_CREATION_DATE.operation_date)";
                    break;
                default:
                    $sqlSortBy = "DOCUMENT_HISTORY_CREATION_DATE.operation_date";
                    break;
            }
            $data->documents = $dbh->query(
                sprintf(
                    "
                            SELECT
                                DOCUMENT.id, DOCUMENT.title, DOCUMENT.description, DOCUMENT_HISTORY_CREATION_DATE.operation_date AS createdOnTimestamp, COALESCE(DOCUMENT_HISTORY_LAST_UPDATE.operation_date, DOCUMENT_HISTORY_CREATION_DATE.operation_date) AS lastUpdateTimestamp, TMP_FILE_COUNT.fileCount, TMP_NOTE_COUNT.noteCount
                            FROM DOCUMENT
                            INNER JOIN DOCUMENT_HISTORY AS DOCUMENT_HISTORY_CREATION_DATE ON (
                                DOCUMENT_HISTORY_CREATION_DATE.document_id = DOCUMENT.id
                                AND DOCUMENT_HISTORY_CREATION_DATE.operation_user_id = :session_user_id
                                AND DOCUMENT_HISTORY_CREATION_DATE.operation_type = :history_operation_add
                            )
                            LEFT JOIN (
                                SELECT
                                    DOCUMENT_HISTORY.document_id, MAX(DOCUMENT_HISTORY.operation_date) AS operation_date
                                FROM DOCUMENT_HISTORY
                                WHERE DOCUMENT_HISTORY.operation_type = :history_operation_update
                                GROUP BY DOCUMENT_HISTORY.document_id
                            ) DOCUMENT_HISTORY_LAST_UPDATE ON DOCUMENT_HISTORY_LAST_UPDATE.document_id = DOCUMENT.id
                            LEFT JOIN (
                                SELECT COUNT(*) AS fileCount, document_id
                                FROM DOCUMENT_FILE
                                GROUP BY document_id
                            ) TMP_FILE_COUNT ON TMP_FILE_COUNT.document_id = DOCUMENT.id
                            LEFT JOIN (
                                SELECT COUNT(*) AS noteCount, document_id
                                FROM DOCUMENT_NOTE
                                GROUP BY document_id
                            ) TMP_NOTE_COUNT ON TMP_NOTE_COUNT.document_id = DOCUMENT.id
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
                function ($item) use ($filter, $dbh) {
                    $item->createdOnTimestamp = intval($item->createdOnTimestamp);
                    $item->lastUpdateTimestamp = intval($item->lastUpdateTimestamp);
                    $item->fileCount = intval($item->fileCount);
                    $item->noteCount = intval($item->noteCount);
                    $item->matchedFragments = [];
                    if (isset($filter["title"]) && !empty($filter["title"])) {
                        $fragment = \HomeDocs\Utils::getStringFragment($item->title, $filter["title"], 64, true);
                        if (! empty($fragment)) {
                            $item->matchedFragments[] = [
                                "matchedOn" => "title",
                                "fragment" => $fragment
                            ];
                        }
                    }
                    if (isset($filter["description"]) && !empty($filter["description"])) {
                        $fragment = \HomeDocs\Utils::getStringFragment($item->description, $filter["description"], 64, true);
                        if (! empty($fragment)) {
                            $item->matchedFragments[] = [
                                "matchedOn" => "description",
                                "fragment" => $fragment
                            ];
                        }
                    }
                    if (isset($filter["notesBody"]) && !empty($filter["notesBody"])) {
                        // TODO: this NEEDS to be rewritten with more efficient method
                        $notes = (new Document($item->id))->getNotes($dbh);
                        foreach ($notes as $note) {
                            $fragment = \HomeDocs\Utils::getStringFragment($note->body, $filter["notesBody"], 64, true);
                            if (! empty($fragment)) {
                                $item->matchedFragments[] = [
                                    "matchedOn" => "note",
                                    "fragment" => $fragment
                                ];
                            }
                        }
                    }
                    return ($item);
                },
                $data->documents
            );
        }
        return ($data);
    }
}
