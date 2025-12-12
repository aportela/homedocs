<?php

declare(strict_types=1);

namespace HomeDocs;

class ShareAttachment
{
    public string $id;
    public int $createdAtTimestamp;
    public int $expiresAtTimestamp;
    public int $accessLimit;
    public int $accessCount;
    public bool $enabled;

    public function __construct(string $id, int $createdAtTimestamp, int $expiresAtTimestamp, int $accessLimit, bool $enabled)
    {
        if (empty($id)) {
            $this->id = sprintf("%s%014d", password_hash(bin2hex(random_bytes(1024)), CRYPT_BLOWFISH), intval(microtime(true) * 1000));
        } else {
            $this->id = $id;
        }
        $this->createdAtTimestamp = $createdAtTimestamp > 0 ? $createdAtTimestamp : intval(microtime(true) * 1000);
        $this->expiresAtTimestamp = $expiresAtTimestamp;
        $this->accessLimit = $accessLimit > 0 ? $accessLimit : 0;
        $this->enabled = $enabled;
    }

    public function add(\aportela\DatabaseWrapper\DB $db, string $attachmentId): bool
    {
        if (! empty($attachmentId) && mb_strlen($attachmentId) === 36) {
            return ($db->execute(
                "
                    INSERT
                    INTO SHARED_ATTACHMENT
                        (id, cuid, attachment_id, ctime, etime, access_limit, access_count, enabled)
                    VALUES
                        (:id, :cuid, :attachment_id, :ctime, :etime, :access_limit, 0, :enabled)
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
                    new \aportela\DatabaseWrapper\Param\StringParam(":cuid", \HomeDocs\UserSession::getUserId()),
                    new \aportela\DatabaseWrapper\Param\StringParam(":attachment_id", $attachmentId),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":ctime", $this->createdAtTimestamp),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":etime", $this->expiresAtTimestamp > 0 ? $this->expiresAtTimestamp : 0),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":access_limit", $this->accessLimit > 0 ? $this->accessLimit : 0),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":enabled", $this->enabled ? 1 : 0),
                ]
            ));
        } else {
            throw new \HomeDocs\Exception\InvalidParamsException("attachmentId");
        }
    }

    public function update(\aportela\DatabaseWrapper\DB $db, string $attachmentId): bool
    {
        if (! empty($attachmentId) && mb_strlen($attachmentId) === 36) {
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
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":etime", $this->expiresAtTimestamp > 0 ? $this->expiresAtTimestamp : 0),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":access_limit", $this->accessLimit > 0 ? $this->accessLimit : 0),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":enabled", $this->enabled ? 1 : 0),
                ]
            ));
        } else {
            throw new \HomeDocs\Exception\InvalidParamsException("attachmentId");
        }
    }

    public function delete(\aportela\DatabaseWrapper\DB $db, string $attachmentId): bool
    {
        if (! empty($attachmentId) && mb_strlen($attachmentId) === 36) {
            return ($db->execute(
                "
                    DELETE
                    FROM SHARED_ATTACHMENT
                    WHERE attachment_id = :attachment_id AND cuid = :cuid
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":attachment_id", $attachmentId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":cuid", \HomeDocs\UserSession::getUserId())
                ]
            ));
        } else {
            throw new \HomeDocs\Exception\InvalidParamsException("attachmentId");
        }
    }

    public function get(\aportela\DatabaseWrapper\DB $db, string|null $attachmentId)
    {
        $results = [];
        if (! empty($attachmentId)) {
            $results = $db->query(
                "
                    SELECT
                        SA.id,
                        SA.cuid as creatorId,
                        SA.attachment_id AS attachmentId,
                        SA.ctime AS createdAtTimestamp,
                        SA.etime AS expiresAtTimestamp,
                        SA.access_limit AS accessLimit,
                        SA.access_count AS accessCount,
                        SA.enabled
                    FROM SHARED_ATTACHMENT SA
                    WHERE
                        SA.attachment_id = :attachment_id
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":attachment_id", $attachmentId)
                ]
            );
        } else if (! empty($this->id)) {
            $results = $db->query(
                "
                    SELECT
                        SA.id,
                        SA.cuid as creatorId,
                        SA.attachment_id AS attachmentId,
                        SA.ctime AS createdAtTimestamp,
                        SA.etime AS expiresAtTimestamp,
                        SA.access_limit AS accessLimit,
                        SA.access_count AS accessCount,
                        SA.enabled
                    FROM SHARED_ATTACHMENT SA
                    WHERE
                        SA.id = :id
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id)
                ]
            );
        } else {
            throw new \HomeDocs\Exception\InvalidParamsException("id");
        }
        if (count($results) == 1) {
            $this->id = property_exists($results[0], "id") && is_string($results[0]->id) ? $results[0]->id : "";
            $this->createdAtTimestamp = property_exists($results[0], "createdAtTimestamp") && is_numeric($results[0]->createdAtTimestamp) ? intval($results[0]->createdAtTimestamp) : 0;
            $this->expiresAtTimestamp = property_exists($results[0], "expiresAtTimestamp") && is_numeric($results[0]->expiresAtTimestamp) ? intval($results[0]->expiresAtTimestamp) : 0;
            $this->accessLimit = property_exists($results[0], "accessLimit") && is_numeric($results[0]->accessLimit) ? intval($results[0]->accessLimit) : 0;
            $this->accessCount = property_exists($results[0], "accessCount") && is_numeric($results[0]->accessCount) ? intval($results[0]->accessCount) : 0;
            $this->enabled = property_exists($results[0], "enabled") && is_numeric($results[0]->enabled) ? intval($results[0]->enabled) === 1 : false;
        } else {
            throw new \HomeDocs\Exception\NotFoundException("id");
        }
    }

    public function isEnabled()
    {
        return ($this->enabled);
    }

    public function hasExceedAccessLimit()
    {
        return ($this->accessLimit > 0 && $this->accessCount >= $this->accessLimit);
    }

    public function isExpired()
    {
        return ($this->expiresAtTimestamp > 0 && $this->expiresAtTimestamp < intval(microtime(true) * 1000));
    }

    public function incrementAccessCount(\aportela\DatabaseWrapper\DB $db): bool
    {
        return ($db->execute(
            "
                UPDATE SHARED_ATTACHMENT
                SET
                    access_count = access_count + 1
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
                        etime < :current_timestamp
                    )
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000)),
            ]
        ));
    }
}
