<?php

declare(strict_types=1);

namespace HomeDocs;

class AttachmentShare
{
    public string $id;

    public int $createdAtTimestamp;

    public ?int $lastAccessTimestamp = null;

    public int $accessLimit;

    public int $accessCount;

    public \stdClass $attachment;

    public \stdClass $document;

    public function __construct(string $id, int $createdAtTimestamp, public int $expiresAtTimestamp, int $accessLimit, public bool $enabled)
    {
        if ($id === '' || $id === '0') {
            $this->id = sprintf("%s%014d", password_hash(bin2hex(random_bytes(1024)), CRYPT_BLOWFISH), intval(microtime(true) * 1000));
        } else {
            $this->id = $id;
        }

        $this->createdAtTimestamp = $createdAtTimestamp > 0 ? $createdAtTimestamp : intval(microtime(true) * 1000);
        $this->accessLimit = max($accessLimit, 0);
    }

    public function add(\aportela\DatabaseWrapper\DB $db, string $attachmentId): bool
    {
        if ($attachmentId !== '' && $attachmentId !== '0' && mb_strlen($attachmentId) === 36) {
            return ($db->execute(
                "
                    INSERT
                    INTO ATTACHMENT_SHARE
                        (id, cuid, attachment_id, ctime, etime, ltime, access_limit, access_count, enabled)
                    VALUES
                        (:id, :cuid, :attachment_id, :ctime, :etime, NULL, :access_limit, 0, :enabled)
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
                    new \aportela\DatabaseWrapper\Param\StringParam(":cuid", \HomeDocs\UserSession::getUserId()),
                    new \aportela\DatabaseWrapper\Param\StringParam(":attachment_id", $attachmentId),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":ctime", $this->createdAtTimestamp),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":etime", max($this->expiresAtTimestamp, 0)),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":access_limit", max($this->accessLimit, 0)),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":enabled", $this->enabled ? 1 : 0),
                ]
            ));
        } else {
            throw new \HomeDocs\Exception\InvalidParamsException("attachmentId");
        }
    }

    public function update(\aportela\DatabaseWrapper\DB $db, string $attachmentId): bool
    {
        if ($attachmentId !== '' && $attachmentId !== '0' && mb_strlen($attachmentId) === 36) {
            return ($db->execute(
                "
                    UPDATE ATTACHMENT_SHARE
                    SET
                        etime = :etime,
                        access_limit = :access_limit,
                        enabled = :enabled
                    WHERE
                        attachment_id = :attachment_id
                    AND
                        cuid = :cuid
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":attachment_id", $attachmentId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":cuid", \HomeDocs\UserSession::getUserId()),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":etime", max($this->expiresAtTimestamp, 0)),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":access_limit", max($this->accessLimit, 0)),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":enabled", $this->enabled ? 1 : 0),
                ]
            ));
        } else {
            throw new \HomeDocs\Exception\InvalidParamsException("attachmentId");
        }
    }

    public function delete(\aportela\DatabaseWrapper\DB $db, string $attachmentId): bool
    {
        if ($attachmentId !== '' && $attachmentId !== '0' && mb_strlen($attachmentId) === 36) {
            return ($db->execute(
                "
                    DELETE
                    FROM ATTACHMENT_SHARE
                    WHERE attachment_id = :attachment_id AND cuid = :cuid
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":attachment_id", $attachmentId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":cuid", \HomeDocs\UserSession::getUserId()),
                ]
            ));
        } else {
            throw new \HomeDocs\Exception\InvalidParamsException("attachmentId");
        }
    }

    public function get(\aportela\DatabaseWrapper\DB $db, ?string $attachmentId): void
    {
        $results = [];
        if (!in_array($attachmentId, [null, '', '0'], true)) {
            $results = $db->query(
                "
                    SELECT
                        ATTACHMENT_SHARE.id,
                        ATTACHMENT_SHARE.cuid as creatorId,
                        ATTACHMENT_SHARE.attachment_id AS attachmentId,
                        ATTACHMENT.name AS attachmentFileName,
                        ATTACHMENT.size AS attachmentFileSize,
                        ATTACHMENT_SHARE.ctime AS createdAtTimestamp,
                        ATTACHMENT_SHARE.etime AS expiresAtTimestamp,
                        ATTACHMENT_SHARE.ltime AS lastAccessTimestamp,
                        ATTACHMENT_SHARE.access_limit AS accessLimit,
                        ATTACHMENT_SHARE.access_count AS accessCount,
                        ATTACHMENT_SHARE.enabled,
                        DOCUMENT.id AS documentId,
                        DOCUMENT.title AS documentTitle
                    FROM ATTACHMENT_SHARE
                    INNER JOIN ATTACHMENT ON ATTACHMENT.id = ATTACHMENT_SHARE.attachment_id
                    INNER JOIN DOCUMENT_ATTACHMENT ON DOCUMENT_ATTACHMENT.attachment_id = ATTACHMENT.id
                    INNER JOIN DOCUMENT ON DOCUMENT.id = DOCUMENT_ATTACHMENT.document_id
                    INNER JOIN DOCUMENT_HISTORY ON (
                        DOCUMENT_HISTORY.document_id = DOCUMENT_ATTACHMENT.document_id
                        AND
                        DOCUMENT_HISTORY.operation_type = :history_operation_add
                        AND
                        DOCUMENT_HISTORY.cuid = :session_user_id
                    )
                    WHERE
                        ATTACHMENT_SHARE.attachment_id = :attachment_id
                ",
                [
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":history_operation_add", \HomeDocs\DocumentHistoryOperation::OPERATION_ADD_DOCUMENT),
                    new \aportela\DatabaseWrapper\Param\StringParam(":session_user_id", \HomeDocs\UserSession::getUserId()),
                    new \aportela\DatabaseWrapper\Param\StringParam(":attachment_id", $attachmentId),
                ]
            );
        } elseif ($this->id !== '' && $this->id !== '0') {
            $results = $db->query(
                "
                    SELECT
                        ATTACHMENT_SHARE.id,
                        ATTACHMENT_SHARE.cuid as creatorId,
                        ATTACHMENT_SHARE.attachment_id AS attachmentId,
                        ATTACHMENT.name AS attachmentFileName,
                        ATTACHMENT.size AS attachmentFileSize,
                        ATTACHMENT_SHARE.ctime AS createdAtTimestamp,
                        ATTACHMENT_SHARE.etime AS expiresAtTimestamp,
                        ATTACHMENT_SHARE.ltime AS lastAccessTimestamp,
                        ATTACHMENT_SHARE.access_limit AS accessLimit,
                        ATTACHMENT_SHARE.access_count AS accessCount,
                        ATTACHMENT_SHARE.enabled,
                        DOCUMENT.id AS documentId,
                        DOCUMENT.title AS documentTitle
                    FROM ATTACHMENT_SHARE
                    INNER JOIN ATTACHMENT ON ATTACHMENT.id = ATTACHMENT_SHARE.attachment_id
                    INNER JOIN DOCUMENT_ATTACHMENT ON DOCUMENT_ATTACHMENT.attachment_id = ATTACHMENT.id
                    INNER JOIN DOCUMENT ON DOCUMENT.id = DOCUMENT_ATTACHMENT.document_id
                    INNER JOIN DOCUMENT_HISTORY ON (
                        DOCUMENT_HISTORY.document_id = DOCUMENT_ATTACHMENT.document_id
                        AND
                        DOCUMENT_HISTORY.operation_type = :history_operation_add
                        AND
                        DOCUMENT_HISTORY.cuid = :session_user_id
                    )
                    WHERE
                        ATTACHMENT_SHARE.id = :id
                ",
                [
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":history_operation_add", \HomeDocs\DocumentHistoryOperation::OPERATION_ADD_DOCUMENT),
                    new \aportela\DatabaseWrapper\Param\StringParam(":session_user_id", \HomeDocs\UserSession::getUserId()),
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
                ]
            );
        } else {
            throw new \HomeDocs\Exception\InvalidParamsException("id");
        }

        if (count($results) === 1) {
            $this->id = property_exists($results[0], "id") && is_string($results[0]->id) ? $results[0]->id : "";
            $this->createdAtTimestamp = property_exists($results[0], "createdAtTimestamp") && is_numeric($results[0]->createdAtTimestamp) ? intval($results[0]->createdAtTimestamp) : 0;
            $this->expiresAtTimestamp = property_exists($results[0], "expiresAtTimestamp") && is_numeric($results[0]->expiresAtTimestamp) ? intval($results[0]->expiresAtTimestamp) : 0;
            $this->lastAccessTimestamp = property_exists($results[0], "lastAccessTimestamp") && is_numeric($results[0]->lastAccessTimestamp) ? intval($results[0]->lastAccessTimestamp) : null;
            $this->accessLimit = property_exists($results[0], "accessLimit") && is_numeric($results[0]->accessLimit) ? intval($results[0]->accessLimit) : 0;
            $this->accessCount = property_exists($results[0], "accessCount") && is_numeric($results[0]->accessCount) ? intval($results[0]->accessCount) : 0;
            $this->enabled = property_exists($results[0], "enabled") && is_numeric($results[0]->enabled) && intval($results[0]->enabled) === 1;
            $this->attachment = new \stdClass();
            $this->attachment->id = property_exists($results[0], "attachmentId") && is_string($results[0]->attachmentId) ? $results[0]->attachmentId : null;
            $this->attachment->name = property_exists($results[0], "attachmentFileName") && is_string($results[0]->attachmentFileName) ? $results[0]->attachmentFileName : null;
            $this->attachment->size = property_exists($results[0], "attachmentFileSize") && is_numeric($results[0]->attachmentFileSize) ? intval($results[0]->attachmentFileSize) : null;
            $this->document = new \stdClass();
            $this->document->id = property_exists($results[0], "documentId") && is_string($results[0]->documentId) ? $results[0]->documentId : null;
            $this->document->title = property_exists($results[0], "documentTitle") && is_string($results[0]->documentTitle) ? $results[0]->documentTitle : null;
        } else {
            throw new \HomeDocs\Exception\NotFoundException("id");
        }
    }

    public function isEnabled(): bool
    {
        return ($this->enabled);
    }

    public function hasExceedAccessLimit(): bool
    {
        return ($this->accessLimit > 0 && $this->accessCount >= $this->accessLimit);
    }

    public function isExpired(): bool
    {
        return ($this->expiresAtTimestamp > 0 && $this->expiresAtTimestamp < intval(microtime(true) * 1000));
    }

    public function incrementAccessCount(\aportela\DatabaseWrapper\DB $db): bool
    {
        return ($db->execute(
            "
                UPDATE ATTACHMENT_SHARE
                SET
                    access_count = access_count + 1,
                    ltime = :current_timestamp
                WHERE
                    id = :id
                AND
                    enabled = 1
                AND
                    (
                        access_limit = 0
                        OR
                        access_count < access_limit
                    )
                AND
                    (
                        etime = 0
                        OR
                        etime > :current_timestamp
                    )
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000)),
            ]
        ));
    }

    public static function search(\aportela\DatabaseWrapper\DB $db, \aportela\DatabaseBrowserWrapper\Pager $pager, string $sortField = "createdAtTimestamp", \aportela\DatabaseBrowserWrapper\Order $sortOrder = \aportela\DatabaseBrowserWrapper\Order::DESC): \stdClass
    {
        $fieldDefinitions = [
            "id" => "ATTACHMENT_SHARE.id",
            "createdAtTimestamp" => "ATTACHMENT_SHARE.ctime",
            "expiresAtTimestamp" => "ATTACHMENT_SHARE.etime",
            "lastAccessTimestamp" => "ATTACHMENT_SHARE.ltime",
            "accessLimit" => "ATTACHMENT_SHARE.access_limit",
            "accessCount" => "ATTACHMENT_SHARE.access_count",
            "enabled" => "ATTACHMENT_SHARE.enabled",
            "attachmentId" => "ATTACHMENT_SHARE.attachment_id",
            "attachmentFileName" => "ATTACHMENT.name",
            "attachmentFileSize" => "ATTACHMENT.size",
            "documentId" => "DOCUMENT_ATTACHMENT.document_id",
            "documentTitle" => "DOCUMENT.title",
        ];
        $fieldCountDefinition = [
            "total" => "COUNT (ATTACHMENT_SHARE.id)",
        ];
        $sortItems = [];
        $sortItems[] = match ($sortField) {
            "createdAtTimestamp", "expiresAtTimestamp", "lastAccessTimestamp", "accessLimit", "accessCount", "enabled", => new \aportela\DatabaseBrowserWrapper\SortItem($sortField, $sortOrder, true),
            default => new \aportela\DatabaseBrowserWrapper\SortItem("createdAtTimestamp", $sortOrder, true),
        };
        // after launch search we need to make some changes foreach result
        $afterBrowse = function (\aportela\DatabaseBrowserWrapper\BrowserResults $browserResults): void {
            array_map(
                function (object $item): \stdClass {
                    // fix warnings on matchedFragments property
                    if (! $item instanceof \stdClass) {
                        throw new \RuntimeException("Invalid");
                    }

                    if (property_exists($item, "createdAtTimestamp") && is_numeric($item->createdAtTimestamp)) {
                        $item->createdAtTimestamp =  intval($item->createdAtTimestamp);
                    }

                    if (property_exists($item, "expiresAtTimestamp") && is_numeric($item->expiresAtTimestamp)) {
                        $item->expiresAtTimestamp =  intval($item->expiresAtTimestamp);
                    }

                    if (property_exists($item, "accessLimit") && is_numeric($item->accessLimit)) {
                        $item->accessLimit =  intval($item->accessLimit);
                    }

                    if (property_exists($item, "accessCount") && is_numeric($item->accessCount)) {
                        $item->accessCount =  intval($item->accessCount);
                    }

                    if (property_exists($item, "enabled") && is_numeric($item->enabled)) {
                        $item->enabled =  intval($item->enabled) === 1;
                    }

                    if (property_exists($item, "attachmentFileSize") && is_numeric($item->attachmentFileSize)) {
                        $item->attachmentFileSize = intval($item->attachmentFileSize);
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
        $params = [
            new \aportela\DatabaseWrapper\Param\IntegerParam(":history_operation_add", \HomeDocs\DocumentHistoryOperation::OPERATION_ADD_DOCUMENT),
            new \aportela\DatabaseWrapper\Param\StringParam(":session_user_id", \HomeDocs\UserSession::getUserId()),
        ];

        $whereCondition = "";
        $browser->addDBQueryParams($params);
        $query = $browser->buildQuery(
            "
                SELECT
                    %s
                FROM ATTACHMENT_SHARE
                INNER JOIN ATTACHMENT ON ATTACHMENT.id = ATTACHMENT_SHARE.attachment_id
                INNER JOIN DOCUMENT_ATTACHMENT ON DOCUMENT_ATTACHMENT.attachment_id = ATTACHMENT.id
                INNER JOIN DOCUMENT ON DOCUMENT.id = DOCUMENT_ATTACHMENT.document_id
                INNER JOIN DOCUMENT_HISTORY ON (
                    DOCUMENT_HISTORY.document_id = DOCUMENT_ATTACHMENT.document_id
                    AND
                    DOCUMENT_HISTORY.operation_type = :history_operation_add
                    AND
                    DOCUMENT_HISTORY.cuid = :session_user_id
                )
                %s
                %s
            "
        );
        $queryCount = $browser->buildQueryCount(
            "
                SELECT
                    %s
                FROM ATTACHMENT_SHARE
                INNER JOIN ATTACHMENT ON ATTACHMENT.id = ATTACHMENT_SHARE.attachment_id
                INNER JOIN DOCUMENT_ATTACHMENT ON DOCUMENT_ATTACHMENT.attachment_id = ATTACHMENT.id
                INNER JOIN DOCUMENT ON DOCUMENT.id = DOCUMENT_ATTACHMENT.document_id
                INNER JOIN DOCUMENT_HISTORY ON (
                    DOCUMENT_HISTORY.document_id = DOCUMENT_ATTACHMENT.document_id
                    AND
                    DOCUMENT_HISTORY.operation_type = :history_operation_add
                    AND
                    DOCUMENT_HISTORY.cuid = :session_user_id
                )
            "
        );
        $browserResults = $browser->launch($query, $queryCount);
        $data = new \stdClass();
        $data->sharedAttachments = [];
        foreach ($browserResults->items as $item) {
            $at = new \HomeDocs\AttachmentShare(
                property_exists($item, "id") && is_string($item->id) ? $item->id : "",
                property_exists($item, "createdAtTimestamp") && is_numeric($item->createdAtTimestamp) ? intval($item->createdAtTimestamp) : 0,
                property_exists($item, "expiresAtTimestamp") && is_numeric($item->expiresAtTimestamp) ? intval($item->expiresAtTimestamp) : 0,
                property_exists($item, "accessLimit") && is_numeric($item->accessLimit) ? intval($item->accessLimit) : 0,
                property_exists($item, "enabled") && is_bool($item->enabled) ? $item->enabled : false,
            );
            $at->accessCount = property_exists($item, "accessCount") && is_numeric($item->accessCount) ? intval($item->accessCount) : 0;
            $at->lastAccessTimestamp = property_exists($item, "lastAccessTimestamp") && is_numeric($item->lastAccessTimestamp) ? intval($item->lastAccessTimestamp) : null;
            $at->attachment = new \stdClass();
            $at->attachment->id = $item->attachmentId ?? null;
            $at->attachment->name = $item->attachmentFileName ?? null;
            $at->attachment->size = $item->attachmentFileSize ?? 0;
            $at->document = new \stdClass();
            $at->document->id = $item->documentId ?? null;
            $at->document->title = $item->documentTitle ?? null;
            $data->sharedAttachments[] = $at;
        }

        $data->pagination = new \stdClass();
        $data->pagination->currentPage = $pager->getCurrentPageIndex();
        $data->pagination->resultsPage = $pager->getResultsPage();
        $data->pagination->totalResults = $browserResults->pager->getTotalResults();
        $data->pagination->totalPages = $browserResults->pager->getTotalPages();
        return ($data);
    }
}
