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
        if (! empty($id) && mb_strlen(($id) === 36)) {
            $this->id = $id;
            $this->createdAtTimestamp = $createdAtTimestamp > 0 ? $createdAtTimestamp : intval(microtime(true) * 1000);
            $this->expiresAtTimestamp = $expiresAtTimestamp;
            $this->accessLimit = $accessLimit > 0 ? $accessLimit : 0;
            $this->enabled = $enabled;
        } else {
            throw new \HomeDocs\Exception\InvalidParamsException("id");
        }
    }

    public function add(\aportela\DatabaseWrapper\DB $db, string $attachmentId): bool
    {
        if (! empty($attachmentId) && mb_strlen(($attachmentId) === 36)) {
            return ($db->execute(
                "
                    INSERT
                    INTO SHARED_ATTACHMENT
                        (id, cuid, attachment_id, ctime, etime, access_limit, access_count, enabled)
                    VALUES
                        (:id, :cuid, :attachment_id, :ctime, :etime, :access_limit, :access_count, :enabled)
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
                    new \aportela\DatabaseWrapper\Param\StringParam(":cuid", \HomeDocs\UserSession::getUserId()),
                    new \aportela\DatabaseWrapper\Param\StringParam(":attachment_id", $attachmentId),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":ctime", $this->createdAtTimestamp),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":etime", $this->expiresAtTimestamp > 0 ? $this->expiresAtTimestamp : 0),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":access_limit", $this->accessLimit > 0 ? $this->accessLimit : 0),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":access_count", $this->accessCount > 0 ? $this->accessCount : 0),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":enabled", $this->enabled ? 1 : 0),
                ]
            ));
        } else {
            throw new \HomeDocs\Exception\InvalidParamsException("attachmentId");
        }
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

    public function update(\aportela\DatabaseWrapper\DB $db): bool
    {
        return ($db->execute(
            "
                UPDATE SHARED_ATTACHMENT
                SET
                    etime = :etime,
                    access_limit = :access_limit,
                    enabled = :enabled
                WHERE
                    id = :id
                AND
                    cuid = :cuid
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
                new \aportela\DatabaseWrapper\Param\StringParam(":cuid", \HomeDocs\UserSession::getUserId()),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":etime", $this->expiresAtTimestamp > 0 ? $this->expiresAtTimestamp : 0),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":access_limit", $this->accessLimit > 0 ? $this->accessLimit : 0),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":enabled", $this->enabled ? 1 : 0),
            ]
        ));
    }

    public function delete(\aportela\DatabaseWrapper\DB $db): bool
    {
        return ($db->execute(
            "
                DELETE
                FROM SHARED_ATTACHMENT
                WHERE id = :id AND cuid = :cuid
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
                new \aportela\DatabaseWrapper\Param\StringParam(":cuid", \HomeDocs\UserSession::getUserId())
            ]
        ));
    }

    public function get(\aportela\DatabaseWrapper\DB $db, ?string $attachmentId)
    {
        if (! empty($attachmentId)) {
            $results = $db->query("", []);
        } else if (! empty($this->id)) {
            $results = $db->query("", []);
        } else {
            throw new \HomeDocs\Exception\InvalidParamsException("id");
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
}
