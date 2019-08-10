<?php

      declare(strict_types=1);

      namespace HomeDocs\Database;

      class Version {

        private $dbh;
        private $databaseType;

        private $installQueries = array(
            "PDO_SQLITE" => array(
                '
                    CREATE TABLE [VERSION] (
                        [num]	NUMERIC NOT NULL UNIQUE,
                        [date]	INTEGER NOT NULL,
                        PRIMARY KEY([num])
                    );
                ',
                '
                    INSERT INTO VERSION VALUES ("1.00", strftime("%s", "now"));
                ',
                '
                    PRAGMA journal_mode=WAL;
                '
            )
        );

        private $upgradeQueries = array(
            "PDO_SQLITE" => array(
                "1.01" => array(
                    '
                        CREATE TABLE `USER` (
                            `id` VARCHAR(36) NOT NULL,
                            `email` VARCHAR(255) NOT NULL UNIQUE,
                            `password_hash` VARCHAR(60) NOT NULL,
                            PRIMARY KEY (`id`)
                        );
                    '
                ),
                "1.02" => array(
                    '
                        CREATE TABLE `FILE` (
                            `id` VARCHAR(36) NOT NULL,
                            `sha1_hash` VARCHAR(40) NOT NULL,
                            `name` VARCHAR(256) NOT NULL,
                            `size` INTEGER NOT NULL,
                            `uploaded_by` VARCHAR(36) NOT NULL,
                            `uploaded_on` INTEGER NOT NULL,
                            PRIMARY KEY (`id`)
                        );
                    ',
                    '
                        CREATE TABLE `DOCUMENT` (
                            `id` VARCHAR(36) NOT NULL,
                            `title` VARCHAR(128) NOT NULL,
                            `description` VARCHAR(4096) NULL,
                            `created_by` VARCHAR(36) NOT NULL,
                            `uploaded_on` INTEGER NOT NULL,
                            PRIMARY KEY (`id`)
                        );
                    ',
                    '
                        CREATE TABLE `DOCUMENT_FILE` (
                            `document_id` VARCHAR(36) NOT NULL,
                            `file_id` VARCHAR(36) NOT NULL,
                            PRIMARY KEY (`document_id`, `file_id`)
                        );
                    ',
                    '
                        CREATE TABLE `DOCUMENT_TAG` (
                            `document_id` VARCHAR(36) NOT NULL,
                            `tag` VARCHAR(32) NOT NULL,
                            PRIMARY KEY (`document_id`, `tag`)
                        )
                    '
                )
            )
        );

        public function __construct (\HomeDocs\Database\DB $dbh, string $databaseType) {
            $this->dbh = $dbh;
            $this->databaseType = $databaseType;
        }

        public function __destruct() { }

        public function get() {
            $query = ' SELECT num FROM VERSION ORDER BY num DESC LIMIT 1; ';
            $results = $this->dbh->query($query, array());
            if ($results && count($results) == 1) {
                return($results[0]->num);
            } else {
                throw new \HomeDocs\Exception\NotFoundException("invalid database version");
            }
        }

        private function set(float $number) {
            $params = array(
                (new \HomeDocs\Database\DBParam())->str(":num", (string) $number)
            );
            $query = '
                INSERT INTO VERSION
                    (num, date)
                VALUES
                    (:num, strftime("%s", "now"));
            ';
            return($this->dbh->execute($query, $params));
        }

        public function install() {
            if (isset($this->installQueries[$this->databaseType])) {
                foreach($this->installQueries[$this->databaseType] as $query) {
                    $this->dbh->execute($query);
                }
            } else {
                throw new \Exception("Unsupported database type: " . $this->databaseType);
            }
        }

        public function upgrade() {
            if (isset($this->upgradeQueries[$this->databaseType])) {
                $result = array(
                    "successVersions" => array(),
                    "failedVersions" => array()
                );
                $actualVersion = $this->get();
                $errors = false;
                foreach($this->upgradeQueries[$this->databaseType] as $version => $queries) {
                    if (! $errors && $version > $actualVersion) {
                        try {
                            $this->dbh->beginTransaction();
                            foreach($queries as $query) {
                                $this->dbh->execute($query);
                            }
                            $this->set(floatval($version));
                            $this->dbh->commit();
                            $result["successVersions"][] = $version;
                        } catch (\PDOException $e) {
                            echo $e->getMessage();
                            $this->dbh->rollBack();
                            $errors = true;
                            $result["failedVersions"][] = $version;
                        }
                    } else if ($errors) {
                        $result["failedVersions"][] = $version;
                    }
                }
                return($result);
            } else {
                throw new \Exception("Unsupported database type: " . $this->databaseType);
            }
        }

        public function hasUpgradeAvailable() {
            $actualVersion = $this->get();
            $errors = false;
            foreach($this->upgradeQueries[$this->databaseType] as $version => $queries) {
                if ($version > $actualVersion) {
                    return(true);
                }
            }
            return(false);
        }

    }

?>