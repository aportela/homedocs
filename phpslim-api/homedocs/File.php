<?php

declare(strict_types=1);

namespace HomeDocs;

class File
{

    public ?string $id;
    public ?string $name;
    public ?int $size;
    public ?string $hash;
    public ?int $uploadedOnTimestamp;
    private ?string $localStoragePath;

    public function __construct(string $rootStoragePath = null, string $id = "", string $name = "", int $size = 0, $hash = "", $uploadedOnTimestamp = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->size = $size;
        $this->hash = $hash;
        $this->uploadedOnTimestamp = $uploadedOnTimestamp;
        $this->localStoragePath = $this->getFileStoragePath($rootStoragePath);
    }

    private function getFileStoragePath(string $rootStoragePath): string
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
                            name, size, sha1_hash AS hash, uploaded_on_timestamp AS uploadedOnTimestamp, uploaded_by_user_id AS uploadedByUserId
                        FROM FILE
                        WHERE id = :id
                    ",
                array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id)
                )
            );
            if (is_array($data) && count($data) == 1) {
                if ($data[0]->uploadedByUserId == \HomeDocs\UserSession::getUserId()) {
                    $this->name = $data[0]->name;
                    $this->size = intval($data[0]->size);
                    $this->hash = $data[0]->hash;
                    $this->uploadedOnTimestamp = intval($data[0]->uploadedOnTimestamp);
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

    private function saveStorage(\aportela\DatabaseWrapper\DB $dbh, \Psr\Http\Message\UploadedFileInterface $uploadedFile): void
    {
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $path = pathinfo($this->localStoragePath);
            if (!file_exists($path['dirname'])) {
                mkdir($path['dirname'], 0777, true);
            }
            $uploadedFile->moveTo($this->localStoragePath);
            $this->hash = sha1_file($this->localStoragePath);
        } else {
            throw new \HomeDocs\Exception\UploadException("Error: " . $uploadedFile->getError());
        }
    }

    private function removeStorage(): void
    {
        if (file_exists(($this->localStoragePath))) {
            unlink($this->localStoragePath);
        }
    }

    public function saveMetadata(\aportela\DatabaseWrapper\DB $dbh): void
    {
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower($this->id)),
            new \aportela\DatabaseWrapper\Param\StringParam(":sha1_hash", $this->hash),
            new \aportela\DatabaseWrapper\Param\StringParam(":name", $this->name),
            new \aportela\DatabaseWrapper\Param\IntegerParam(":size", $this->size),
            new \aportela\DatabaseWrapper\Param\StringParam(":uploaded_by_user_id", \HomeDocs\UserSession::getUserId())
        );
        $dbh->exec(
            "
                INSERT INTO FILE
                    (id, sha1_hash, name, size, uploaded_by_user_id, uploaded_on_timestamp)
                VALUES
                    (:id, :sha1_hash, :name, :size, :uploaded_by_user_id, strftime('%s', 'now'))
            ",
            $params
        );
    }

    public function removeMetadata(\aportela\DatabaseWrapper\DB $dbh): void
    {
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower($this->id))
        );
        $dbh->exec(
            "
                DELETE FROM FILE WHERE id = :id
            ",
            $params
        );
    }


    public function add(\aportela\DatabaseWrapper\DB $dbh, \Psr\Http\Message\UploadedFileInterface $uploadedFile): void
    {
        if (!$this->exists()) {
            $this->saveStorage($dbh, $uploadedFile);
            $this->saveMetadata($dbh);
        } else {
            throw new \HomeDocs\Exception\AlreadyExistsException("id");
        }
    }

    public function remove(\aportela\DatabaseWrapper\DB $dbh): void
    {
        $this->removeMetadata($dbh);
        $this->removeStorage();
    }
}
