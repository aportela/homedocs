<?php

declare(strict_types=1);

namespace HomeDocs;

class Document
{
    /**
     * @param array<string> $tags
     * @param array<\HomeDocs\Attachment> $attachments
     * @param array<\HomeDocs\Note> $notes
     * @param array<\HomeDocs\DocumentHistoryOperation> $history
     */
    public function __construct(public ?string $id = null, public ?string $title = null, public ?string $description = null, public ?int $createdOnTimestamp = null, public ?int $lastUpdateTimestamp = null, public array $tags = [], public array $attachments = [], public array $notes = [], public array $history = []) {}

    /**
     * @return array<mixed>
     */
    public static function searchRecent(\aportela\DatabaseWrapper\DB $db, int $count = 16): array
    {
        $results = $db->query(
            sprintf(
                "
                    WITH DOCUMENTS_ATTACHMENTS AS (
                        SELECT
                            DOCUMENT_ATTACHMENT.document_id, COUNT(*) AS attachmentCount
                        FROM DOCUMENT_ATTACHMENT
                        INNER JOIN DOCUMENT_HISTORY ON DOCUMENT_HISTORY.document_id = DOCUMENT_ATTACHMENT.document_id AND DOCUMENT_HISTORY.operation_type = :history_operation_add AND DOCUMENT_HISTORY.cuid = :session_user_id
                        GROUP BY
                            DOCUMENT_ATTACHMENT.document_id
                    ),
                    DOCUMENTS_NOTES AS (
                        SELECT
                            DOCUMENT_NOTE.document_id, COUNT(*) AS noteCount
                        FROM DOCUMENT_NOTE
                        INNER JOIN DOCUMENT_HISTORY ON DOCUMENT_HISTORY.document_id = DOCUMENT_NOTE.document_id AND DOCUMENT_HISTORY.operation_type = :history_operation_add AND DOCUMENT_HISTORY.cuid = :session_user_id
                        GROUP BY
                            DOCUMENT_NOTE.document_id
                    ),
                    DOCUMENTS_TAGS AS (
                        SELECT
                            document_id, GROUP_CONCAT(tag, ',') AS tags
                        FROM (
                            SELECT DOCUMENT_TAG.document_id, DOCUMENT_TAG.tag
                            FROM DOCUMENT_TAG
                            INNER JOIN DOCUMENT_HISTORY ON DOCUMENT_HISTORY.document_id = DOCUMENT_TAG.document_id AND DOCUMENT_HISTORY.operation_type = :history_operation_add AND DOCUMENT_HISTORY.cuid = :session_user_id
                            GROUP BY
                                DOCUMENT_TAG.document_id,
                                DOCUMENT_TAG.tag
                            ORDER BY
                                DOCUMENT_TAG.tag
                        )
                        GROUP BY document_id
                    ),
                    DOCUMENTS_LAST_HISTORY_OPERATION AS (
                        SELECT
                            document_id, MAX(ctime) AS max_ctime
                        FROM DOCUMENT_HISTORY
                        GROUP BY
                            document_id
                    )
                    SELECT
                        DOCUMENT.id,
                        DOCUMENT.title,
                        DOCUMENT.description,
                        CAST(DOCUMENTS_LAST_HISTORY_OPERATION.max_ctime AS INT) AS updatedAtTimestamp,
                        COALESCE(DOCUMENTS_ATTACHMENTS.attachmentCount, 0) AS attachmentCount,
                        COALESCE(DOCUMENTS_NOTES.noteCount, 0) AS noteCount,
                        DOCUMENTS_TAGS.tags
                    FROM DOCUMENT
                    INNER JOIN DOCUMENT_HISTORY ON DOCUMENT_HISTORY.document_id = DOCUMENT.id AND DOCUMENT_HISTORY.operation_type = :history_operation_add AND DOCUMENT_HISTORY.cuid = :session_user_id
                    LEFT JOIN DOCUMENTS_ATTACHMENTS ON DOCUMENTS_ATTACHMENTS.document_id = DOCUMENT.id
                    LEFT JOIN DOCUMENTS_TAGS ON DOCUMENTS_TAGS.document_id = DOCUMENT.id
                    LEFT JOIN DOCUMENTS_NOTES ON DOCUMENTS_NOTES.document_id = DOCUMENT.id
                    LEFT JOIN DOCUMENTS_LAST_HISTORY_OPERATION ON DOCUMENTS_LAST_HISTORY_OPERATION.document_id = DOCUMENT.id
                    ORDER BY
                        DOCUMENTS_LAST_HISTORY_OPERATION.max_ctime DESC
                    LIMIT %d;
                ",
                $count
            ),
            [
                new \aportela\DatabaseWrapper\Param\IntegerParam(":history_operation_add", \HomeDocs\DocumentHistoryOperation::OPERATION_ADD_DOCUMENT),
                new \aportela\DatabaseWrapper\Param\StringParam(":session_user_id", \HomeDocs\UserSession::getUserId())
            ]
        );
        return (array_map(
            function (object $item): object {
                if (property_exists($item, "updatedAtTimestamp") && is_numeric($item->updatedAtTimestamp)) {
                    $item->updatedAtTimestamp = intval($item->updatedAtTimestamp);
                }

                if (property_exists($item, "attachmentCount") && is_numeric($item->attachmentCount)) {
                    $item->attachmentCount = intval($item->attachmentCount);
                }

                if (property_exists($item, "noteCount") && is_numeric($item->noteCount)) {
                    $item->noteCount = intval($item->noteCount);
                }

                if (property_exists($item, "tags") && is_string($item->tags)) {
                    $item->tags = $item->tags !== '' && $item->tags !== '0' ? explode(",", $item->tags) : [];
                }

                return ($item);
            },
            $results
        ));
    }

    private function validate(): void
    {
        if (!in_array($this->id, [null, '', '0'], true) && mb_strlen($this->id) === \HomeDocs\Constants::UUID_V4_LENGTH) {
            if (!in_array($this->title, [null, '', '0'], true) && mb_strlen($this->title) <= \HomeDocs\Constants::MAX_DOCUMENT_TITLE_LENGTH) {
                if ((!in_array($this->description, [null, '', '0'], true) && mb_strlen($this->description) <= \HomeDocs\Constants::MAX_DOCUMENT_DESCRIPTION_LENGTH) || in_array($this->description, [null, '', '0'], true)) {
                    foreach ($this->tags as $tag) {
                        if (empty($tag)) {
                            throw new \HomeDocs\Exception\InvalidParamsException("tags");
                        } elseif (mb_strlen($tag) > \HomeDocs\Constants::MAX_DOCUMENT_TAG_LENGTH) {
                            throw new \HomeDocs\Exception\InvalidParamsException("tags");
                        }
                    }

                    foreach ($this->notes as $note) {
                        if (! empty($note->id) && mb_strlen((string) $note->id) === \HomeDocs\Constants::UUID_V4_LENGTH) {
                            if (! (!empty($note->body) && mb_strlen((string) $note->body) <= \HomeDocs\Constants::MAX_DOCUMENT_NOTE_BODY_LENGTH)) {
                                throw new \HomeDocs\Exception\InvalidParamsException("noteBody");
                            }
                        } else {
                            $note->id = \HomeDocs\Utils::uuidv4();
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

    public function add(\aportela\DatabaseWrapper\DB $db): void
    {
        $this->validate();
        $params = [
            (new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower((string) $this->id))),
            (new \aportela\DatabaseWrapper\Param\StringParam(":title", $this->title)),
        ];
        if (!in_array($this->description, [null, '', '0'], true)) {
            $params[] = (new \aportela\DatabaseWrapper\Param\StringParam(":description", $this->description));
        } else {
            $params[] = (new \aportela\DatabaseWrapper\Param\NullParam(":description"));
        }

        if ($db->execute("
                INSERT INTO DOCUMENT
                    (id, title, description)
                VALUES
                    (:id, :title, :description)
            ", $params) && $db->execute(
            "
                INSERT INTO DOCUMENT_HISTORY
                    (document_id, ctime, operation_type, cuid)
                VALUES
                    (:document_id, :ctime, :operation_type, :cuid)
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower((string) $this->id)),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":ctime", intval(microtime(true) * 1000)),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":operation_type", \HomeDocs\DocumentHistoryOperation::OPERATION_ADD_DOCUMENT),
                new \aportela\DatabaseWrapper\Param\StringParam(":cuid", \HomeDocs\UserSession::getUserId())
            ]
        )) {
            foreach ($this->tags as $tag) {
                if (!empty($tag)) {
                    $db->execute(
                        "
                                INSERT INTO DOCUMENT_TAG
                                    (document_id, tag)
                                VALUES
                                    (:document_id, :tag)
                            ",
                        [
                            new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower((string) $this->id)),
                            new \aportela\DatabaseWrapper\Param\StringParam(":tag", mb_strtolower($tag))
                        ]
                    );
                } else {
                    throw new \HomeDocs\Exception\InvalidParamsException("tag");
                }
            }

            foreach ($this->attachments as $attachment) {
                if (!empty($attachment->id)) {
                    $db->execute(
                        "
                                INSERT INTO DOCUMENT_ATTACHMENT
                                    (document_id, attachment_id)
                                VALUES
                                    (:document_id, :attachment_id)
                            ",
                        [
                            new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower((string) $this->id)),
                            new \aportela\DatabaseWrapper\Param\StringParam(":attachment_id", mb_strtolower((string) $attachment->id))
                        ]
                    );
                } else {
                    throw new \HomeDocs\Exception\InvalidParamsException("attachment_id");
                }
            }

            foreach ($this->notes as $note) {
                $db->execute(
                    "
                            INSERT INTO DOCUMENT_NOTE
                                (note_id, document_id, ctime, cuid, body)
                            VALUES
                                (:note_id, :document_id, :ctime, :cuid, :note_body)
                        ",
                    [
                        new \aportela\DatabaseWrapper\Param\StringParam(":note_id", mb_strtolower((string) $note->id)),
                        new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower((string) $this->id)),
                        new \aportela\DatabaseWrapper\Param\IntegerParam(":ctime", intval(microtime(true) * 1000)),
                        new \aportela\DatabaseWrapper\Param\StringParam(":cuid", \HomeDocs\UserSession::getUserId()),
                        new \aportela\DatabaseWrapper\Param\StringParam(":note_body", $note->body),
                    ]
                );
            }
        }
    }

    public function update(\aportela\DatabaseWrapper\DB $db): void
    {
        $this->validate();
        $params = [
            new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower((string) $this->id)),
            new \aportela\DatabaseWrapper\Param\StringParam(":title", $this->title)
        ];
        if (!in_array($this->description, [null, '', '0'], true)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":description", $this->description);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":description");
        }

        if ($db->execute("
                UPDATE DOCUMENT SET
                    title = :title,
                    description = :description
                WHERE
                    id = :id
            ", $params) && $db->execute(
            "
                    INSERT INTO DOCUMENT_HISTORY
                        (document_id, ctime, operation_type, cuid)
                    VALUES
                        (:document_id, :ctime, :operation_type, :cuid)
                ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower((string) $this->id)),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":ctime", intval(microtime(true) * 1000)),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":operation_type", \HomeDocs\DocumentHistoryOperation::OPERATION_UPDATE_DOCUMENT),
                new \aportela\DatabaseWrapper\Param\StringParam(":cuid", \HomeDocs\UserSession::getUserId())
            ]
        )) {
            $db->execute(
                "
                        DELETE FROM DOCUMENT_TAG
                        WHERE
                            document_id = :document_id
                    ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower((string) $this->id)),
                ]
            );
            foreach ($this->tags as $tag) {
                if (!empty($tag)) {
                    $db->execute(
                        "
                                INSERT INTO DOCUMENT_TAG
                                    (document_id, tag)
                                VALUES
                                    (:document_id, :tag)
                            ",
                        [
                            new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower((string) $this->id)),
                            new \aportela\DatabaseWrapper\Param\StringParam(":tag", mb_strtolower($tag))
                        ]
                    );
                } else {
                    throw new \HomeDocs\Exception\InvalidParamsException("tag");
                }
            }

            $originalAttachments = $this->getAttachments($db);
            foreach ($originalAttachments as $originalAttachment) {
                $notFound = true;
                foreach ($this->attachments as $attachment) {
                    if ($attachment->id == $originalAttachment->id) {
                        $notFound = false;
                    }
                }

                if ($notFound) {
                    $db->execute(
                        "
                                DELETE FROM DOCUMENT_ATTACHMENT
                                WHERE
                                    document_id = :document_id
                                AND
                                    attachment_id = :attachment_id
                            ",
                        [
                            new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower((string) $this->id)),
                            new \aportela\DatabaseWrapper\Param\StringParam(":attachment_id", mb_strtolower((string) $originalAttachment->id))
                        ]
                    );
                    $originalAttachment->remove($db);
                }
            }

            foreach ($this->attachments as $attachment) {
                if (!empty($attachment->id)) {
                    $notFound = true;
                    foreach ($originalAttachments as $originalAttachment) {
                        if ($attachment->id == $originalAttachment->id) {
                            $notFound = false;
                        }
                    }

                    if ($notFound) {
                        $db->execute(
                            "
                                    INSERT INTO DOCUMENT_ATTACHMENT
                                        (document_id, attachment_id)
                                    VALUES
                                        (:document_id, :attachment_id)
                                ",
                            [
                                new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower((string) $this->id)),
                                new \aportela\DatabaseWrapper\Param\StringParam(":attachment_id", mb_strtolower((string) $attachment->id))
                            ]
                        );
                    }
                } else {
                    throw new \HomeDocs\Exception\InvalidParamsException("attachmentId");
                }
            }

            $originalNotes = $this->getNotes($db);
            foreach ($originalNotes as $originalNote) {
                $notFound = true;
                foreach ($this->notes as $note) {
                    if ($note->id == $originalNote->id) {
                        $notFound = false;
                    }
                }

                if ($notFound) {
                    $db->execute(
                        "
                                DELETE FROM DOCUMENT_NOTE
                                WHERE
                                    document_id = :document_id
                                AND
                                    note_id = :note_id
                            ",
                        [
                            new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower((string) $this->id)),
                            new \aportela\DatabaseWrapper\Param\StringParam(":note_id", mb_strtolower((string) $originalNote->id))
                        ]
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
                            $db->execute(
                                "
                                    UPDATE DOCUMENT_NOTE SET
                                        body = :note_body
                                    WHERE
                                        note_id = :note_id
                                    AND
                                        document_id =:document_id
                                    AND
                                        cuid = :cuid
                                    ",
                                [
                                    new \aportela\DatabaseWrapper\Param\StringParam(":note_body", $note->body),
                                    new \aportela\DatabaseWrapper\Param\StringParam(":note_id", mb_strtolower($note->id)),
                                    new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower((string) $this->id)),
                                    new \aportela\DatabaseWrapper\Param\StringParam(":cuid", \HomeDocs\UserSession::getUserId())
                                ]
                            );
                            break;
                        } else {
                            break;
                        }
                    }
                }

                if ($notFound) {
                    $db->execute(
                        "
                                INSERT INTO DOCUMENT_NOTE
                                    (note_id, document_id, ctime, cuid, body)
                                VALUES
                                    (:note_id, :document_id, :ctime, :cuid, :note_body)
                            ",
                        [
                            new \aportela\DatabaseWrapper\Param\StringParam(":note_id", mb_strtolower((string) $note->id)),
                            new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower((string) $this->id)),
                            new \aportela\DatabaseWrapper\Param\IntegerParam(":ctime", intval(microtime(true) * 1000)),
                            new \aportela\DatabaseWrapper\Param\StringParam(":cuid", \HomeDocs\UserSession::getUserId()),
                            new \aportela\DatabaseWrapper\Param\StringParam(":note_body", $note->body)
                        ]
                    );
                }
            }
        }
    }

    public function delete(\aportela\DatabaseWrapper\DB $db): void
    {
        if (!in_array($this->id, [null, '', '0'], true) && mb_strlen($this->id) === \HomeDocs\Constants::UUID_V4_LENGTH) {
            $params = [
                new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower($this->id)),
            ];
            $db->execute(
                "
                    DELETE FROM DOCUMENT_TAG
                    WHERE
                        document_id = :document_id
                ",
                $params
            );
            $db->execute(
                "
                    DELETE FROM DOCUMENT_ATTACHMENT
                    WHERE
                        document_id = :document_id
                ",
                $params
            );
            $originalAttachments = $this->getAttachments($db);
            foreach ($originalAttachments as $originalAttachment) {
                $originalAttachment->remove($db);
            }

            $db->execute(
                "
                    DELETE FROM DOCUMENT_NOTE
                    WHERE
                        document_id = :document_id
                ",
                $params
            );
            $db->execute(
                "
                    DELETE FROM DOCUMENT_HISTORY
                    WHERE
                        document_id = :document_id
                ",
                $params
            );
            $db->execute(
                "
                    DELETE FROM DOCUMENT
                    WHERE
                        id = :document_id
                ",
                $params
            );
        } else {
            throw new \HomeDocs\Exception\InvalidParamsException("id");
        }
    }

    public function get(\aportela\DatabaseWrapper\DB $db): void
    {
        if (!in_array($this->id, [null, '', '0'], true) && mb_strlen($this->id) === \HomeDocs\Constants::UUID_V4_LENGTH) {
            $data = $db->query(
                "
                    SELECT
                        title, description, DOCUMENT_HISTORY.ctime AS createdOnTimestamp, COALESCE(HISTORY_LAST_UPDATE.document_last_update, DOCUMENT_HISTORY.ctime) AS lastUpdateTimestamp, DOCUMENT_HISTORY.cuid AS createdByUserId
                    FROM DOCUMENT
                    INNER JOIN DOCUMENT_HISTORY ON DOCUMENT_HISTORY.document_id = DOCUMENT.id AND DOCUMENT_HISTORY.operation_type = :history_operation_add
                    LEFT JOIN (
                        SELECT
                            DOCUMENT_HISTORY.document_id, MAX(DOCUMENT_HISTORY.ctime) AS document_last_update
                        FROM DOCUMENT_HISTORY
                        WHERE
                            DOCUMENT_HISTORY.document_id = :id
                        AND
                            DOCUMENT_HISTORY.operation_type <> :history_operation_add
                        GROUP BY
                            DOCUMENT_HISTORY.document_id
                    ) HISTORY_LAST_UPDATE ON HISTORY_LAST_UPDATE.document_id = DOCUMENT.id
                    WHERE id = :id
                ",
                [
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":history_operation_add", \HomeDocs\DocumentHistoryOperation::OPERATION_ADD_DOCUMENT),
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower($this->id))
                ]
            );
            if (count($data) === 1) {
                if (($data[0]->createdByUserId ?? null) == \HomeDocs\UserSession::getUserId()) {
                    $this->title = property_exists($data[0], "title") && is_string($data[0]->title) ? $data[0]->title : null;
                    $this->description = property_exists($data[0], "description") && is_string($data[0]->description) ? $data[0]->description : null;
                    $this->createdOnTimestamp = property_exists($data[0], "createdOnTimestamp") && is_numeric($data[0]->createdOnTimestamp) ? intval($data[0]->createdOnTimestamp) : 0;
                    $this->lastUpdateTimestamp = property_exists($data[0], "lastUpdateTimestamp") && is_numeric($data[0]->lastUpdateTimestamp) ? intval($data[0]->lastUpdateTimestamp) : 0;
                    $this->tags = $this->getTags($db);
                    $this->attachments = $this->getAttachments($db);
                    $this->notes = $this->getNotes($db);
                    $this->history = $this->getHistory($db);
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

    /**
     * @return array<string>
     */
    private function getTags(\aportela\DatabaseWrapper\DB $db): array
    {
        $tags = [];
        $data = $db->query(
            "
                SELECT
                    tag
                FROM DOCUMENT_TAG
                INNER JOIN DOCUMENT ON DOCUMENT.id = DOCUMENT_TAG.document_id
                WHERE
                    DOCUMENT_TAG.document_id = :document_id
                ORDER BY
                    DOCUMENT_TAG.tag
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower((string) $this->id))
            ]
        );
        foreach ($data as $item) {
            if (property_exists($item, "tag") && is_string($item->tag)) {
                $tags[] = $item->tag;
            }
        }

        return ($tags);
    }

    /**
     * @return array<\HomeDocs\Attachment>
     */
    private function getAttachments(\aportela\DatabaseWrapper\DB $db, ?string $search = null): array
    {
        $attachments = [];
        $params = [
            new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower((string) $this->id))
        ];
        $queryConditions = [];
        if (!in_array($search, [null, '', '0'], true)) {
            // explode into words, remove duplicated & empty elements
            $words = array_filter(array_unique(explode(" ", trim(mb_strtolower($search)))));
            $totalWords = count($words);
            if ($totalWords > 0) {
                foreach ($words as $word) {
                    $paramName = sprintf(":ATTACHMENT_NAME_%03d", $totalWords);
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . $word . "%");
                    $queryConditions[] = sprintf(" ATTACHMENT.name LIKE %s ", $paramName);
                    --$totalWords;
                }
            }
        }

        $data = $db->query(
            sprintf(
                "
                SELECT
                    ATTACHMENT.id, ATTACHMENT.name, ATTACHMENT.size, ATTACHMENT.sha1_hash AS hash, ATTACHMENT.ctime AS createdAtTimestamp
                FROM DOCUMENT_ATTACHMENT
                INNER JOIN DOCUMENT ON DOCUMENT.id = DOCUMENT_ATTACHMENT.document_id
                LEFT JOIN ATTACHMENT ON ATTACHMENT.id = DOCUMENT_ATTACHMENT.attachment_id
                WHERE
                    DOCUMENT_ATTACHMENT.document_id = :document_id
                %s
                ORDER BY
                    ATTACHMENT.ctime DESC,
                    ATTACHMENT.name
            ",
                in_array($search, [null, '', '0'], true) ?
                    ""
                    :
                    "
                    AND (
                        " .  implode(" AND ", $queryConditions) . "
                    )"
            ),
            $params
        );
        foreach ($data as $item) {
            $attachments[] = new \HomeDocs\Attachment(
                property_exists($item, "id") && is_string($item->id) ? $item->id : "",
                property_exists($item, "name") && is_string($item->name) ? $item->name : null,
                property_exists($item, "size") && is_numeric($item->size) ? intval($item->size) : 0,
                property_exists($item, "hash") && is_string($item->hash) ? $item->hash : null,
                property_exists($item, "createdAtTimestamp") && is_numeric($item->createdAtTimestamp) ? intval($item->createdAtTimestamp) : 0
            );
        }

        return ($attachments);
    }

    /**
     * @return array<\HomeDocs\Note>
     */
    private function getNotes(\aportela\DatabaseWrapper\DB $db, ?string $search = null): array
    {
        $notes = [];
        $params = [
            new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower((string) $this->id))
        ];
        $queryConditions = [];
        if (!in_array($search, [null, '', '0'], true)) {
            // explode into words, remove duplicated & empty elements
            $words = array_filter(array_unique(explode(" ", trim(mb_strtolower($search)))));
            $totalWords = count($words);
            if ($totalWords > 0) {
                foreach ($words as $word) {
                    $paramName = sprintf(":BODY_%03d", $totalWords);
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . $word . "%");
                    $queryConditions[] = sprintf(" DOCUMENT_NOTE.body LIKE %s ", $paramName);
                    --$totalWords;
                }
            }
        }

        $data = $db->query(
            sprintf(
                "
                    SELECT
                        DOCUMENT_NOTE.note_id AS noteId, DOCUMENT_NOTE.ctime AS createdAtTimestamp, DOCUMENT_NOTE.body
                    FROM DOCUMENT_NOTE
                    WHERE
                        DOCUMENT_NOTE.document_id = :document_id
                    %s
                    ORDER BY
                        DOCUMENT_NOTE.ctime DESC
                ",
                in_array($search, [null, '', '0'], true) ?
                    ""
                    :
                    "
                    AND (
                        " .  implode(" AND ", $queryConditions) . "
                    )"
            ),
            $params
        );
        foreach ($data as $item) {
            $notes[] = new \HomeDocs\Note(
                property_exists($item, "noteId") && is_string($item->noteId) ? $item->noteId : null,
                property_exists($item, "createdAtTimestamp") && is_numeric($item->createdAtTimestamp) ? intval($item->createdAtTimestamp) : 0,
                property_exists($item, "body") && is_string($item->body) ? $item->body : null
            );
        }

        return ($notes);
    }

    /**
     * @return array<\HomeDocs\DocumentHistoryOperation>
     */
    private function getHistory(\aportela\DatabaseWrapper\DB $db): array
    {
        $operations = [];
        $data = $db->query(
            "
                SELECT
                    DOCUMENT_HISTORY.ctime AS operationTimestamp, DOCUMENT_HISTORY.operation_type AS operationType
                FROM DOCUMENT_HISTORY
                WHERE
                    DOCUMENT_HISTORY.document_id = :document_id
                ORDER BY
                    DOCUMENT_HISTORY.ctime DESC
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":document_id", mb_strtolower((string) $this->id))
            ]
        );
        foreach ($data as $item) {
            $operations[] = new \HomeDocs\DocumentHistoryOperation(
                property_exists($item, "operationTimestamp") && is_numeric($item->operationTimestamp) ? intval($item->operationTimestamp) : 0,
                property_exists($item, "operationType") && is_numeric($item->operationType) ? intval($item->operationType) : null,
            );
        }

        return ($operations);
    }

    /**
     * @param array<mixed> $filter
     */
    public static function search(\aportela\DatabaseWrapper\DB $db, \aportela\DatabaseBrowserWrapper\Pager $pager, array $filter = [], string $sortBy = "createdOnTimestamp", \aportela\DatabaseBrowserWrapper\Order $sortOrder = \aportela\DatabaseBrowserWrapper\Order::DESC): \stdClass
    {
        $fieldDefinitions = [
            "id" => "DOCUMENT.id",
            "title" => "DOCUMENT.title",
            "description" => "DOCUMENT.description",
            "createdOnTimestamp" => "DOCUMENT_HISTORY_CREATION_DATE.ctime",
            "lastUpdateTimestamp" => "COALESCE(DOCUMENT_HISTORY_LAST_UPDATE.ctime, DOCUMENT_HISTORY_CREATION_DATE.ctime)",
            "attachmentCount" => "COALESCE(TMP_ATTACHMENT_COUNT.attachmentCount, 0)",
            "noteCount" => "COALESCE(TMP_NOTE_COUNT.noteCount, 0)",
        ];
        $fieldCountDefinition = [
            "total" => "COUNT (DOCUMENT.id)"
        ];
        $sortItems = [];
        $sortItems[] = match ($sortBy) {
            "title", "description", "attachmentCount", "noteCount", "createdOnTimestamp", "lastUpdateTimestamp" => new \aportela\DatabaseBrowserWrapper\SortItem($sortBy, $sortOrder, true),
            default => new \aportela\DatabaseBrowserWrapper\SortItem("createdOnTimestamp", $sortOrder, true),
        };
        // after launch search we need to make some changes foreach result
        $afterBrowse = function (\aportela\DatabaseBrowserWrapper\BrowserResults $browserResults) use ($filter, $db): void {
            array_map(
                function (object $item) use ($filter, $db): \stdClass {
                    // fix warnings on matchedFragments property
                    if (! $item instanceof \stdClass) {
                        throw new \RuntimeException("Invalid");
                    }

                    if (property_exists($item, "createdOnTimestamp") && is_numeric($item->createdOnTimestamp)) {
                        $item->createdOnTimestamp =  intval($item->createdOnTimestamp);
                    }

                    if (property_exists($item, "lastUpdateTimestamp") && is_numeric($item->lastUpdateTimestamp)) {
                        $item->lastUpdateTimestamp =  intval($item->lastUpdateTimestamp);
                    }

                    if (property_exists($item, "attachmentCount") && is_numeric($item->attachmentCount)) {
                        $item->attachmentCount = intval($item->attachmentCount);
                    }

                    if (property_exists($item, "noteCount") && is_numeric($item->noteCount)) {
                        $item->noteCount = intval($item->noteCount);
                    }

                    $item->matchedFragments = [];
                    if (isset($filter["title"]) && is_string($filter["title"]) && ($filter["title"] !== '' && $filter["title"] !== '0') && property_exists($item, "title") && is_string($item->title)) {
                        $fragment = \HomeDocs\Utils::getStringFragment($item->title, $filter["title"], 64, true);
                        if (!in_array($fragment, [null, '', '0'], true)) {
                            $item->matchedFragments[] = [
                                "matchedOn" => "title",
                                "fragment" => $fragment
                            ];
                        }
                    }

                    if (isset($filter["description"]) && is_string($filter["description"]) && ($filter["description"] !== '' && $filter["description"] !== '0') && property_exists($item, "description") && is_string($item->description)) {
                        $fragment = \HomeDocs\Utils::getStringFragment($item->description, $filter["description"], 64, true);
                        if (!in_array($fragment, [null, '', '0'], true)) {
                            $item->matchedFragments[] = [
                                "matchedOn" => "description",
                                "fragment" => $fragment
                            ];
                        }
                    }

                    if (isset($filter["notesBody"]) && is_string($filter["notesBody"]) && ($filter["notesBody"] !== '' && $filter["notesBody"] !== '0') && property_exists($item, "id")) {
                        // TODO: this NEEDS to be rewritten with more efficient method
                        $notes = new \HomeDocs\Document(is_string($item->id) ? $item->id : null)->getNotes($db, $filter["notesBody"]);
                        foreach ($notes as $note) {
                            $fragment = \HomeDocs\Utils::getStringFragment(is_string($note->body) ? $note->body : "", $filter["notesBody"], 64, true);
                            if (!in_array($fragment, [null, '', '0'], true)) {
                                $item->matchedFragments[] = [
                                    "matchedOn" => "note body",
                                    "fragment" => $fragment
                                ];
                            }
                        }
                    }

                    if (isset($filter["attachmentsFilename"]) && is_string($filter["attachmentsFilename"]) && ($filter["attachmentsFilename"] !== '' && $filter["attachmentsFilename"] !== '0') && property_exists($item, "id")) {
                        // TODO: this NEEDS to be rewritten with more efficient method
                        $attachments = new \HomeDocs\Document(is_string($item->id) ? $item->id : null)->getAttachments($db, $filter["attachmentsFilename"]);
                        foreach ($attachments as $attachment) {
                            $fragment = \HomeDocs\Utils::getStringFragment(is_string($attachment->name) ? $attachment->name : "", $filter["attachmentsFilename"], 64, true);
                            if (!in_array($fragment, [null, '', '0'], true)) {
                                $item->matchedFragments[] = [
                                    "matchedOn" => "attachment filename",
                                    "fragment" => $fragment
                                ];
                            }
                        }
                    }

                    return ($item);
                },
                $browserResults->items
            );
        };
        $browser = new \aportela\DatabaseBrowserWrapper\Browser(
            $db,
            $fieldDefinitions,
            $fieldCountDefinition,
            $pager,
            new \aportela\DatabaseBrowserWrapper\Sort($sortItems),
            new \aportela\DatabaseBrowserWrapper\Filter(),
            $afterBrowse
        );
        $queryConditions = [];
        $params = [
            new \aportela\DatabaseWrapper\Param\IntegerParam(":history_operation_add", \HomeDocs\DocumentHistoryOperation::OPERATION_ADD_DOCUMENT),
            new \aportela\DatabaseWrapper\Param\IntegerParam(":history_operation_update", \HomeDocs\DocumentHistoryOperation::OPERATION_UPDATE_DOCUMENT),
            new \aportela\DatabaseWrapper\Param\StringParam(":session_user_id", \HomeDocs\UserSession::getUserId())
        ];
        if (isset($filter["title"]) && is_string($filter["title"])) {
            // explode into words, remove duplicated & empty elements
            $words = array_filter(array_unique(explode(" ", trim(mb_strtolower($filter["title"])))));
            $totalWords = count($words);
            if ($totalWords > 0) {
                foreach ($words as $word) {
                    $paramName = sprintf(":TITLE_%03d", $totalWords);
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . $word . "%");
                    $queryConditions[] = sprintf(" DOCUMENT.title LIKE %s ", $paramName);
                    --$totalWords;
                }
            }
        }

        if (isset($filter["description"]) && is_string($filter["description"])) {
            // explode into words, remove duplicated & empty elements
            $words = array_filter(array_unique(explode(" ", trim(mb_strtolower($filter["description"])))));
            $totalWords = count($words);
            if ($totalWords > 0) {
                foreach ($words as $word) {
                    $paramName = sprintf(":DESCRIPTION_%03d", $totalWords);
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . $word . "%");
                    $queryConditions[] = sprintf(" DOCUMENT.description LIKE %s ", $paramName);
                    --$totalWords;
                }
            }
        }

        if (isset($filter["notesBody"]) && is_string($filter["notesBody"])) {
            // explode into words, remove duplicated & empty elements
            $words = array_filter(array_unique(explode(" ", trim(mb_strtolower($filter["notesBody"])))));
            $totalWords = count($words);
            if ($totalWords > 0) {
                $notesConditions = [];
                foreach ($words as $word) {
                    $paramName = sprintf(":NOTES_BODY_%03d", $totalWords);
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . $word . "%");
                    $notesConditions[] = sprintf(" DOCUMENT_NOTE.body LIKE %s ", $paramName);
                    --$totalWords;
                }

                $queryConditions[] = sprintf("
                    EXISTS (
                        SELECT
                            DOCUMENT_NOTE.document_id
                        FROM DOCUMENT_NOTE
                        WHERE
                            DOCUMENT_NOTE.document_id = DOCUMENT.id
                        AND
                            (%s)
                    )
                ", implode(" AND ", $notesConditions));
            }
        }

        if (isset($filter["attachmentsFilename"]) && is_string($filter["attachmentsFilename"])) {
            // explode into words, remove duplicated & empty elements
            $words = array_filter(array_unique(explode(" ", trim(mb_strtolower($filter["attachmentsFilename"])))));
            $totalWords = count($words);
            if ($totalWords > 0) {
                $notesConditions = [];
                foreach ($words as $word) {
                    $paramName = sprintf(":ATTACHMENTS_FILENAME_%03d", $totalWords);
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . $word . "%");
                    $notesConditions[] = sprintf(" ATTACHMENT.name LIKE %s ", $paramName);
                    --$totalWords;
                }

                $queryConditions[] = sprintf("
                    EXISTS (
                        SELECT
                            DOCUMENT_ATTACHMENT.document_id
                        FROM DOCUMENT_ATTACHMENT
                        INNER JOIN ATTACHMENT ON ATTACHMENT.id = DOCUMENT_ATTACHMENT.attachment_id
                        WHERE
                            DOCUMENT_ATTACHMENT.document_id = DOCUMENT.id
                        AND
                            (%s)
                    )
                ", implode(" AND ", $notesConditions));
            }
        }

        if (isset($filter["fromCreationTimestampCondition"]) && $filter["fromCreationTimestampCondition"] > 0) {
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":fromCreationTimestamp", $filter["fromCreationTimestampCondition"]);
            $queryConditions[] = " DOCUMENT_HISTORY_CREATION_DATE.ctime >= :fromCreationTimestamp ";
        }

        if (isset($filter["toCreationTimestampCondition"]) && $filter["toCreationTimestampCondition"] > 0) {
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":toCreationTimestamp", $filter["toCreationTimestampCondition"]);
            $queryConditions[] = " DOCUMENT_HISTORY_CREATION_DATE.ctime <= :toCreationTimestamp ";
        }

        if (isset($filter["fromLastUpdateTimestampCondition"]) && $filter["fromLastUpdateTimestampCondition"] > 0) {
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":fromLastUpdateTimestamp", $filter["fromLastUpdateTimestampCondition"]);
            $queryConditions[] = " COALESCE(DOCUMENT_HISTORY_LAST_UPDATE.ctime, DOCUMENT_HISTORY_CREATION_DATE.ctime) >= :fromLastUpdateTimestamp ";
        }

        if (isset($filter["toLastUpdateTimestampCondition"]) && $filter["toLastUpdateTimestampCondition"] > 0) {
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":toLastUpdateTimestamp", $filter["toLastUpdateTimestampCondition"]);
            $queryConditions[] = " COALESCE(DOCUMENT_HISTORY_LAST_UPDATE.ctime, DOCUMENT_HISTORY_CREATION_DATE.ctime) <= :toLastUpdateTimestamp ";
        }

        if (isset($filter["fromUpdatedOnTimestampCondition"]) && $filter["fromUpdatedOnTimestampCondition"] > 0  && isset($filter["toUpdatedOnTimestampCondition"]) && $filter["toUpdatedOnTimestampCondition"] > 0) {

            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":fromUpdatedOnTimestamp", $filter["fromUpdatedOnTimestampCondition"]);
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":toUpdatedOnTimestamp", $filter["toUpdatedOnTimestampCondition"]);
            $queryConditions[] = "
                EXISTS (
                    SELECT
                        DOCUMENT_HISTORY_UPDATED_ON.document_id
                    FROM DOCUMENT_HISTORY AS DOCUMENT_HISTORY_UPDATED_ON
                    WHERE
                        DOCUMENT_HISTORY_UPDATED_ON.document_id = DOCUMENT.id
                    AND
                        DOCUMENT_HISTORY_UPDATED_ON.ctime >= :fromUpdatedOnTimestamp
                    AND
                        DOCUMENT_HISTORY_UPDATED_ON.ctime <= :toUpdatedOnTimestamp
                )
            ";
        } elseif (isset($filter["fromUpdatedOnTimestampCondition"]) && $filter["fromUpdatedOnTimestampCondition"] > 0) {

            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":fromUpdatedOnTimestamp", $filter["fromUpdatedOnTimestampCondition"]);
            $queryConditions[] = "
                EXISTS (
                    SELECT
                        DOCUMENT_HISTORY_UPDATED_ON.document_id
                    FROM DOCUMENT_HISTORY AS DOCUMENT_HISTORY_UPDATED_ON
                    WHERE
                        DOCUMENT_HISTORY_UPDATED_ON.document_id = DOCUMENT.id
                    AND
                        DOCUMENT_HISTORY_UPDATED_ON.ctime >= :fromUpdatedOnTimestamp
                )
            ";
        } elseif (isset($filter["toUpdatedOnTimestampCondition"]) && $filter["toUpdatedOnTimestampCondition"] > 0) {
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":toUpdatedOnTimestamp", $filter["toUpdatedOnTimestampCondition"]);
            $queryConditions[] = "
                EXISTS (
                    SELECT
                        DOCUMENT_HISTORY_UPDATED_ON.document_id
                    FROM DOCUMENT_HISTORY AS DOCUMENT_HISTORY_UPDATED_ON
                    WHERE
                        DOCUMENT_HISTORY_UPDATED_ON.document_id = DOCUMENT.id
                    AND
                        DOCUMENT_HISTORY_UPDATED_ON.ctime <= :toUpdatedOnTimestamp
                )
            ";
        }

        if (isset($filter["tags"]) && is_array($filter["tags"]) && $filter["tags"] !== []) {
            foreach ($filter["tags"] as $i => $tag) {
                $paramName = sprintf(":TAG_%03d", $i + 1);
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, $tag);
                $queryConditions[] = sprintf(
                    "
                            EXISTS (
                                SELECT
                                    document_id
                                FROM DOCUMENT_TAG
                                WHERE
                                    DOCUMENT_TAG.document_id = DOCUMENT.id
                                AND
                                    DOCUMENT_TAG.tag IN (%s)
                            )
                        ",
                    $paramName
                );
            }
        }

        $whereCondition = $queryConditions !== [] ? " WHERE " .  implode(" AND ", $queryConditions) : "";
        $browser->addDBQueryParams($params);
        $query = $browser->buildQuery(
            sprintf(
                "
                    SELECT
                        %%s
                    FROM DOCUMENT
                    INNER JOIN DOCUMENT_HISTORY AS DOCUMENT_HISTORY_CREATION_DATE ON (
                        DOCUMENT_HISTORY_CREATION_DATE.document_id = DOCUMENT.id
                        AND DOCUMENT_HISTORY_CREATION_DATE.cuid = :session_user_id
                        AND DOCUMENT_HISTORY_CREATION_DATE.operation_type = :history_operation_add
                    )
                    LEFT JOIN (
                        SELECT
                            DOCUMENT_HISTORY.document_id, MAX(DOCUMENT_HISTORY.ctime) AS ctime
                        FROM DOCUMENT_HISTORY
                        WHERE
                            DOCUMENT_HISTORY.operation_type = :history_operation_update
                        GROUP BY
                            DOCUMENT_HISTORY.document_id
                    ) DOCUMENT_HISTORY_LAST_UPDATE ON DOCUMENT_HISTORY_LAST_UPDATE.document_id = DOCUMENT.id
                    LEFT JOIN (
                        SELECT
                            COUNT(*) AS attachmentCount, document_id
                        FROM DOCUMENT_ATTACHMENT
                        GROUP BY
                            document_id
                    ) TMP_ATTACHMENT_COUNT ON TMP_ATTACHMENT_COUNT.document_id = DOCUMENT.id
                    LEFT JOIN (
                        SELECT
                            COUNT(*) AS noteCount, document_id
                        FROM DOCUMENT_NOTE
                        GROUP BY
                            document_id
                    ) TMP_NOTE_COUNT ON TMP_NOTE_COUNT.document_id = DOCUMENT.id
                    %s
                    %%s
                    %%s
                ",
                $whereCondition
            )
        );
        // TODO: only LEFT JOIN LAST UPDATE IF REQUIRED BY FILTERS
        $queryCount = $browser->buildQueryCount(
            sprintf(
                "
                    SELECT
                        %%s
                    FROM DOCUMENT
                    INNER JOIN DOCUMENT_HISTORY AS DOCUMENT_HISTORY_CREATION_DATE ON (
                        DOCUMENT_HISTORY_CREATION_DATE.document_id = DOCUMENT.id
                        AND DOCUMENT_HISTORY_CREATION_DATE.cuid = :session_user_id
                        AND DOCUMENT_HISTORY_CREATION_DATE.operation_type = :history_operation_add
                    )
                    LEFT JOIN (
                        SELECT
                            DOCUMENT_HISTORY.document_id, MAX(DOCUMENT_HISTORY.ctime) AS ctime
                        FROM DOCUMENT_HISTORY
                        WHERE
                            DOCUMENT_HISTORY.operation_type = :history_operation_update
                        AND
                            DOCUMENT_HISTORY.cuid = :session_user_id
                        GROUP BY
                            DOCUMENT_HISTORY.document_id
                    ) DOCUMENT_HISTORY_LAST_UPDATE ON DOCUMENT_HISTORY_LAST_UPDATE.document_id = DOCUMENT.id
                    %s
                ",
                $whereCondition
            )
        );
        $browserResults = $browser->launch($query, $queryCount);
        // TODO: reuse $browserResults object ?
        $data = new \stdClass();
        $data->documents = $browserResults->items;
        $data->pagination = new \stdClass();
        $data->pagination->currentPage = $pager->getCurrentPageIndex();
        $data->pagination->resultsPage = $pager->getResultsPage();
        $data->pagination->totalResults = $browserResults->pager->getTotalResults();
        $data->pagination->totalPages = $browserResults->pager->getTotalPages();
        return ($data);
    }
}
