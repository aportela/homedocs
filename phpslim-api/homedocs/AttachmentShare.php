<?php

declare(strict_types=1);

namespace HomeDocs;

class AttachmentShare
{
    public string $id;

    public int $createdAtTimestamp;
    public int|null $lastAccessTimestamp;
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
                    INTO SHARED_ATTACHMENT
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
                    UPDATE SHARED_ATTACHMENT
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
                    FROM SHARED_ATTACHMENT
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
                        SA.id,
                        SA.cuid as creatorId,
                        SA.attachment_id AS attachmentId,
                        SA.ctime AS createdAtTimestamp,
                        SA.etime AS expiresAtTimestamp,
                        SA.ltime AS lastAccessTimestamp,
                        SA.access_limit AS accessLimit,
                        SA.access_count AS accessCount,
                        SA.enabled
                    FROM SHARED_ATTACHMENT SA
                    WHERE
                        SA.attachment_id = :attachment_id
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":attachment_id", $attachmentId),
                ]
            );
        } elseif ($this->id !== '' && $this->id !== '0') {
            $results = $db->query(
                "
                    SELECT
                        SA.id,
                        SA.cuid as creatorId,
                        SA.attachment_id AS attachmentId,
                        SA.ctime AS createdAtTimestamp,
                        SA.etime AS expiresAtTimestamp,
                        SA.ltime AS lastAccessTimestamp,
                        SA.access_limit AS accessLimit,
                        SA.access_count AS accessCount,
                        SA.enabled
                    FROM SHARED_ATTACHMENT SA
                    WHERE
                        SA.id = :id
                ",
                [
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
            $this->attachment->size = property_exists($results[0], "attachmentFileSize") && is_string($results[0]->attachmentFileSize) ? $results[0]->attachmentFileSize : null;
            $this->document = new \stdClass();
            $this->document->id = null;
            $this->document->title = null;
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
                UPDATE SHARED_ATTACHMENT
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
            "id" => "SHARED_ATTACHMENT.id",
            "createdAtTimestamp" => "SHARED_ATTACHMENT.ctime",
            "expiresAtTimestamp" => "SHARED_ATTACHMENT.etime",
            "lastAccessTimestamp" => "SHARED_ATTACHMENT.ltime",
            "accessLimit" => "SHARED_ATTACHMENT.access_limit",
            "accessCount" => "SHARED_ATTACHMENT.access_count",
            "enabled" => "SHARED_ATTACHMENT.enabled",
            "attachmentId" => "SHARED_ATTACHMENT.attachment_id",
            "attachmentFileName" => "ATTACHMENT.name",
            "attachmentFileSize" => "ATTACHMENT.size",
            "documentId" => "DOCUMENT_ATTACHMENT.document_id",
            "documentTitle" => "DOCUMENT.title"
        ];
        $fieldCountDefinition = [
            "total" => "COUNT (SHARED_ATTACHMENT.id)",
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
        $queryConditions = [];
        $params = [
            new \aportela\DatabaseWrapper\Param\IntegerParam(":history_operation_add", \HomeDocs\DocumentHistoryOperation::OPERATION_ADD_DOCUMENT),
            new \aportela\DatabaseWrapper\Param\StringParam(":session_user_id", \HomeDocs\UserSession::getUserId()),
        ];

        $whereCondition = $queryConditions !== [] ? " WHERE " . implode(" AND ", $queryConditions) : "";
        $browser->addDBQueryParams($params);
        $query = $browser->buildQuery(
            sprintf(
                "
                    SELECT
                        %%s
                    FROM SHARED_ATTACHMENT
                    INNER JOIN ATTACHMENT ON ATTACHMENT.id = SHARED_ATTACHMENT.attachment_id
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
                    %%s
                    %%s
                ",
                $whereCondition
            )
        );
        $queryCount = $browser->buildQueryCount(
            sprintf(
                "
                    SELECT
                        %%s
                    FROM SHARED_ATTACHMENT
                    INNER JOIN ATTACHMENT ON ATTACHMENT.id = SHARED_ATTACHMENT.attachment_id
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
                ",
                $whereCondition
            )
        );
        $browserResults = $browser->launch($query, $queryCount);
        $data = new \stdClass();
        foreach ($browserResults->items as $item) {
            $at = new \HomeDocs\AttachmentShare($item->id, $item->createdAtTimestamp, $item->expiresAtTimestamp, $item->accessLimit, $item->enabled);
            $at->attachment = new \stdClass();
            $at->attachment->id = $item->attachmentId;
            $at->attachment->name = $item->attachmentFileName;
            $at->attachment->size = $item->attachmentFileSize;
            $at->document = new \stdClass();
            $at->document->id = $item->documentId;
            $at->document->title = $item->documentTitle;
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
