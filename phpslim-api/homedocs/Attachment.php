<?php

declare(strict_types=1);

namespace HomeDocs;

class Attachment
{
    private ?string $localStoragePath;

    public function __construct(?string $rootStoragePath = null, public ?string $id = null, public ?string $name = null, public ?int $size = null, public ?string $hash = null, public ?int $createdOnTimestamp = null)
    {
        if (! empty($rootStoragePath) && ! empty($this->id)) {
            $this->localStoragePath = $this->getAttachmentStoragePath($rootStoragePath);
        }
    }

    private function getAttachmentStoragePath(string $rootStoragePath): string
    {
        if (!empty($this->id) && mb_strlen($this->id) == 36) {
            return (sprintf(
                "%s%s%s%s%s%s%s",
                $rootStoragePath,
                DIRECTORY_SEPARATOR,
                substr($this->id, 0, 1),
                DIRECTORY_SEPARATOR,
                substr($this->id, 1, 1),
                DIRECTORY_SEPARATOR,
                $this->id
            )
            );
        } else {
            throw new \HomeDocs\Exception\InvalidParamsException("id");
        }
    }

    public function getLocalStoragePath(): string
    {
        return ($this->localStoragePath);
    }

    public function get(\aportela\DatabaseWrapper\DB $dbh): void
    {
        if (!empty($this->id) && mb_strlen($this->id) == 36) {
            $data = $dbh->query(
                "
                    SELECT
                        name, size, sha1_hash AS hash, ctime AS createdOnTimestamp, cuid AS uploadedByUserId
                    FROM ATTACHMENT
                    WHERE
                        id = :id
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id)
                ]
            );
            if (count($data) == 1) {
                if ($data[0]->uploadedByUserId == \HomeDocs\UserSession::getUserId()) {
                    $this->name = $data[0]->name;
                    $this->size = intval($data[0]->size);
                    $this->hash = $data[0]->hash;
                    $this->createdOnTimestamp = intval($data[0]->createdOnTimestamp);
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

    private function exists(): bool
    {
        return (file_exists($this->localStoragePath));
    }

    private function saveStorage(\Psr\Http\Message\UploadedFileInterface $uploadedFile): void
    {
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $path = pathinfo((string) $this->localStoragePath);
            if (!file_exists($path['dirname'])) {
                mkdir($path['dirname'], 0777, true);
            }
            $uploadedFile->moveTo($this->localStoragePath);
            $this->hash = sha1_file($this->localStoragePath);
        } else {
            throw new \HomeDocs\Exception\UploadException("Error: " . $uploadedFile->getError());
        }
    }

    private function saveMetadata(\aportela\DatabaseWrapper\DB $dbh): void
    {
        $this->createdOnTimestamp = intval(microtime(true) * 1000);
        $dbh->execute(
            "
                INSERT INTO ATTACHMENT
                    (id, sha1_hash, name, size, cuid, ctime)
                VALUES
                    (:id, :sha1_hash, :name, :size, :cuid, :ctime)
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower((string) $this->id)),
                new \aportela\DatabaseWrapper\Param\StringParam(":sha1_hash", $this->hash),
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $this->name),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":size", $this->size),
                new \aportela\DatabaseWrapper\Param\StringParam(":cuid", \HomeDocs\UserSession::getUserId()),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":ctime", $this->createdOnTimestamp)
            ]
        );
    }

    public function add(\aportela\DatabaseWrapper\DB $dbh, \Psr\Http\Message\UploadedFileInterface $uploadedFile): void
    {
        if (!$this->exists()) {
            $this->saveStorage($uploadedFile);
            $this->saveMetadata($dbh);
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



    private function removeMetadata(\aportela\DatabaseWrapper\DB $dbh): void
    {
        $dbh->execute(
            "
                DELETE FROM ATTACHMENT
                WHERE
                    id = :id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower((string) $this->id))
            ]
        );
    }

    public function isLinkedToDocument(\aportela\DatabaseWrapper\DB $dbh): bool
    {
        $result = $dbh->query(
            "
                SELECT
                    COUNT(document_id) AS total
                FROM DOCUMENT_ATTACHMENT
                WHERE
                    attachment_id = :id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower((string) $this->id))
            ]
        );
        return (intval($result[0]->total) > 0);
    }

    public function remove(\aportela\DatabaseWrapper\DB $dbh): void
    {
        $this->removeMetadata($dbh);
        $this->removeStorage();
    }
}
