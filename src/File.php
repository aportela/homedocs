<?php

    declare(strict_types=1);

    namespace HomeDocs;

    class File {

        public $id;
        public $name;
        public $size;
        public $hash;
        public $uploadedOnTimestamp;

        public function __construct (string $id = "", string $name = "", $size = 0, $uploadedOnTimestamp = null) {
            $this->id = $id;
            $this->name = $name;
            $this->size = $size;
            $this->uploadedOnTimestamp = $uploadedOnTimestamp;
        }

        public function getStoragePath($localStoragePath): string {
            if (! empty($this->hash) && mb_strlen($this->hash) == 40) {
                return(
                    sprintf(
                        "%s%s%s%s%s%s%s",
                        rtrim($localStoragePath, DIRECTORY_SEPARATOR),
                        DIRECTORY_SEPARATOR,
                        substr($this->hash, 0, 1),
                        DIRECTORY_SEPARATOR,
                        substr($this->hash, 1, 1),
                        DIRECTORY_SEPARATOR,
                        $this->hash
                    )
                );
            } else {
                throw new \HomeDocs\Exception\InvalidParamsException("hash");
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
    }
?>