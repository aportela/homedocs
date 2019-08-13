<?php

    declare(strict_types=1);

    namespace HomeDocs;

    class File {

        public $id;
        public $name;
        public $size;
        public $hash;
        public $uploadedOnTimestamp;
        public $localStoragePath;

        public function __construct (string $id = "", string $name = "", int $size = 0, $hash = "", $uploadedOnTimestamp = null) {
            $this->id = $id;
            $this->name = $name;
            $this->size = $size;
            $this->hash = $hash;
            $this->uploadedOnTimestamp = $uploadedOnTimestamp;
            $this->localStoragePath = $this->getFileStoragePath();
        }

        private function getFileStoragePath(): string {
            if (! empty($this->id) && mb_strlen($this->id) == 36) {
                $localStoragePath = dirname(__DIR__) . DIRECTORY_SEPARATOR  . "data" . DIRECTORY_SEPARATOR . "storage";
                return(
                    sprintf(
                        "%s%s%s%s%s%s%s",
                        rtrim($localStoragePath, DIRECTORY_SEPARATOR),
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

        public function get (\HomeDocs\Database\DB $dbh) {
            if (! empty($this->id) && mb_strlen($this->id) == 36) {
                $data = $dbh->query(
                    "
                        SELECT
                            name, size, sha1_hash AS hash, uploaded_on_timestamp AS uploadedOnTimestamp, uploaded_by_user_id AS uploadedByUserId
                        FROM FILE
                        WHERE id = :id
                    ",
                    array(
                        (new \HomeDocs\Database\DBParam())->str(":id", $this->id)
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

        private function exists(): bool {
            return(file_exists($this->localStoragePath));
        }

        private function saveStorage(\HomeDocs\Database\DB $dbh, \Slim\Http\UploadedFile $uploadedFile) {
            if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                $uploadedFile->moveTo($this->localStoragePath);
                $this->hash = sha1_file($this->localStoragePath);
            } else {
                throw new \HomeDocs\Exception\UploadException("Error: " . $uploadedFile->getError());
            }
        }

        public function saveMetadata(\HomeDocs\Database\DB $dbh) {
            $params = array(
                (new \HomeDocs\Database\DBParam())->str(":id", mb_strtolower($this->id)),
                (new \HomeDocs\Database\DBParam())->str(":sha1_hash", $this->hash),
                (new \HomeDocs\Database\DBParam())->str(":name", $this->name),
                (new \HomeDocs\Database\DBParam())->int(":size", $this->size),
                (new \HomeDocs\Database\DBParam())->str(":uploaded_by_user_id", \HomeDocs\UserSession::getUserId())
            );
            return(
                $dbh->execute(
                    "
                        INSERT INTO FILE
                            (id, sha1_hash, name, size, uploaded_by_user_id, uploaded_on_timestamp)
                        VALUES
                            (:id, :sha1_hash, :name, :size, :uploaded_by_user_id, strftime('%s', 'now'))
                    ",
                    $params
                )
            );
        }

        public function add(\HomeDocs\Database\DB $dbh, \Slim\Http\UploadedFile $uploadedFile) {
            if (! $this->exists()) {
                $this->saveStorage($dbh, $uploadedFile);
                $this->saveMetadata($dbh);
            } else {
                throw new \HomeDocs\Exception\AlreadyExistsException("id");
            }
        }
    }
?>