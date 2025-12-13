<?php

declare(strict_types=1);

namespace HomeDocs;

class Attachment
{
    private string $localStoragePath;

    public function __construct(public string $id, public ?string $name = null, public ?int $size = null, public ?string $hash = null, public ?int $createdAtTimestamp = null, public bool $shared = false)
    {
        if (!in_array($this->id, [null, '', '0'], true) && mb_strlen($this->id) === 36) {
            $this->localStoragePath = sprintf(
                "%s%s%s%s%s%s%s",
                new \HomeDocs\Settings()->getStoragePath(),
                DIRECTORY_SEPARATOR,
                substr($this->id, 0, 1),
                DIRECTORY_SEPARATOR,
                substr($this->id, 1, 1),
                DIRECTORY_SEPARATOR,
                $this->id
            );
        } else {
            throw new \HomeDocs\Exception\InvalidParamsException("id");
        }
    }

    public function getLocalStoragePath(): string
    {
        return ($this->localStoragePath);
    }

    public function get(\aportela\DatabaseWrapper\DB $db): void
    {
        $data = $db->query(
            "
                    SELECT
                        A.name, A.size, A.sha1_hash AS hash, A.ctime AS createdAtTimestamp, A.cuid AS uploadedByUserId, ATTACHMENT_SHARE.attachment_id AS shareAttachmentId
                    FROM ATTACHMENT A
                    LEFT JOIN ATTACHMENT_SHARE ATTACHMENT_SHARE ON ATTACHMENT_SHARE.attachment_id = A.id
                    WHERE
                        A.id = :id
                ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
            ]
        );
        if (count($data) === 1) {
            if (($data[0]->uploadedByUserId ?? null) === \HomeDocs\UserSession::getUserId()) {
                $this->name = property_exists($data[0], "name") && is_string($data[0]->name) ? $data[0]->name : null;
                $this->size = property_exists($data[0], "size") && is_numeric($data[0]->size) ? intval($data[0]->size) : 0;
                $this->hash = property_exists($data[0], "hash") && is_string($data[0]->hash) ? $data[0]->hash : null;
                $this->createdAtTimestamp = property_exists($data[0], "createdAtTimestamp") && is_numeric($data[0]->createdAtTimestamp) ? intval($data[0]->createdAtTimestamp) : 0;
                $this->shared = property_exists($data[0], "shareAttachmentId") && is_string($data[0]->shareAttachmentId) && ($data[0]->shareAttachmentId !== '' && $data[0]->shareAttachmentId !== '0');
            } else {
                throw new \HomeDocs\Exception\AccessDeniedException("id");
            }
        } else {
            throw new \HomeDocs\Exception\NotFoundException("id");
        }
    }

    private function exists(): bool
    {
        return (file_exists($this->localStoragePath));
    }

    private function saveStorage(\Psr\Http\Message\UploadedFileInterface $uploadedFile): void
    {
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $path = pathinfo($this->localStoragePath);
            if (isset($path['dirname']) && !file_exists($path['dirname'])) {
                mkdir($path['dirname'], 0o777, true);
            }

            $uploadedFile->moveTo($this->localStoragePath);
            $shaHash = sha1_file($this->localStoragePath);
            if (is_string($shaHash)) {
                $this->hash = $shaHash;
            } else {
                throw new \HomeDocs\Exception\UploadException("Error: sha1 hash failed");
            }
        } else {
            throw new \HomeDocs\Exception\UploadException("Error: " . $uploadedFile->getError());
        }
    }

    private function saveMetadata(\aportela\DatabaseWrapper\DB $db): void
    {
        $this->createdAtTimestamp = intval(microtime(true) * 1000);
        $db->execute(
            "
                INSERT INTO ATTACHMENT
                    (id, sha1_hash, name, size, cuid, ctime)
                VALUES
                    (:id, :sha1_hash, :name, :size, :cuid, :ctime)
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower($this->id)),
                new \aportela\DatabaseWrapper\Param\StringParam(":sha1_hash", $this->hash),
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $this->name),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":size", $this->size),
                new \aportela\DatabaseWrapper\Param\StringParam(":cuid", \HomeDocs\UserSession::getUserId()),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":ctime", $this->createdAtTimestamp),
            ]
        );
    }

    public function add(\aportela\DatabaseWrapper\DB $db, \Psr\Http\Message\UploadedFileInterface $uploadedFile): void
    {
        if (!$this->exists()) {
            $this->saveStorage($uploadedFile);
            $this->saveMetadata($db);
        } else {
            throw new \HomeDocs\Exception\AlreadyExistsException("id");
        }
    }

    private function removeStorage(): bool
    {
        if (file_exists(($this->localStoragePath))) {
            unlink($this->localStoragePath);
            return (true);
        } else {
            return (false);
        }
    }

    private function removeMetadata(\aportela\DatabaseWrapper\DB $db): void
    {
        $db->execute(
            "
                DELETE FROM ATTACHMENT
                WHERE
                    id = :id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower($this->id)),
            ]
        );
    }

    public function isLinkedToDocument(\aportela\DatabaseWrapper\DB $db): bool
    {
        $result = $db->query(
            "
                SELECT
                    COUNT(document_id) AS total
                FROM DOCUMENT_ATTACHMENT
                WHERE
                    attachment_id = :id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower($this->id)),
            ]
        );
        return (property_exists($result[0], "total") && is_numeric($result[0]->total) && intval($result[0]->total) > 0);
    }

    public function remove(\aportela\DatabaseWrapper\DB $db): void
    {
        $this->removeMetadata($db);
        $this->removeStorage();
    }
}
