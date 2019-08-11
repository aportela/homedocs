<?php

    declare(strict_types=1);

    namespace HomeDocs;

    class File {

        public $id;
        public $name;
        public $size;
        public $uploadedTimestamp;

        public function __construct (string $id = "", string $name = "", $size = 0, $uploadedTimestamp = null) {
            $this->id = $id;
            $this->name = $name;
            $this->size = $size;
            $this->uploadedTimestamp = $uploadedTimestamp;
        }
    }
?>