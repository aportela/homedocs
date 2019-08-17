<?php

    declare(strict_types=1);

    namespace HomeDocs;

    class User {

        public $id;
        public $email;
        public $password;
        public $passwordHash;

        public function __construct (string $id = "", string $email = "", string $password = "") {
            $this->id = $id;
            $this->email = $email;
            $this->password = $password;
        }

        private function passwordHash(string $password = ""): string {
            return(password_hash($password, PASSWORD_BCRYPT, array('cost' => 12)));
        }

        public function add(\HomeDocs\Database\DB $dbh) {
            if (! empty($this->id) && mb_strlen($this->id) == 36) {
                if (! empty($this->email) && mb_strlen($this->email) <= 255 && filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                    if (! empty($this->password)) {
                        $params = array(
                            (new \HomeDocs\Database\DBParam())->str(":id", mb_strtolower($this->id)),
                            (new \HomeDocs\Database\DBParam())->str(":email", mb_strtolower($this->email)),
                            (new \HomeDocs\Database\DBParam())->str(":password_hash", $this->passwordHash($this->password))
                        );
                        return($dbh->execute(" INSERT INTO USER (id, email, password_hash) VALUES(:id, :email, :password_hash) ", $params));
                    } else {
                        throw new \HomeDocs\Exception\InvalidParamsException("password");
                    }
                } else {
                    throw new \HomeDocs\Exception\InvalidParamsException("email");
                }
            } else {
                throw new \HomeDocs\Exception\InvalidParamsException("id");
            }
        }

        public function update(\HomeDocs\Database\DB $dbh) {
            if (! empty($this->id) && mb_strlen($this->id) == 36) {
                if (! empty($this->email) && mb_strlen($this->email) <= 255 && filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                    if (! empty($this->password)) {
                        $params = array(
                            (new \HomeDocs\Database\DBParam())->str(":id", mb_strtolower($this->id)),
                            (new \HomeDocs\Database\DBParam())->str(":email", mb_strtolower($this->email)),
                            (new \HomeDocs\Database\DBParam())->str(":password_hash", $this->passwordHash($this->password))
                        );
                        return($dbh->execute(" UPDATE USER SET email = :email, password_hash = :password_hash WHERE id = :id ", $params));
                    } else {
                        throw new \HomeDocs\Exception\InvalidParamsException("password");
                    }
                } else {
                    throw new \HomeDocs\Exception\InvalidParamsException("email");
                }
            } else {
                throw new \HomeDocs\Exception\InvalidParamsException("id");
            }
        }

        public function get(\HomeDocs\Database\DB $dbh) {
            $results = null;
            if (! empty($this->id) && mb_strlen($this->id) == 36) {
                $results = $dbh->query(
                    "
                        SELECT
                            USER.id, USER.email, USER.password_hash AS passwordHash
                        FROM USER
                        WHERE USER.id = :id
                    ",
                    array(
                        (new \HomeDocs\Database\DBParam())->str(":id", mb_strtolower($this->id))
                    )
                );
            } else if (! empty($this->email) && filter_var($this->email, FILTER_VALIDATE_EMAIL) && mb_strlen($this->email) <= 255) {
                $results = $dbh->query(
                    "
                    SELECT
                        USER.id, USER.email, USER.password_hash AS passwordHash
                    FROM USER
                        WHERE USER.email = :email
                    ",
                    array(
                        (new \HomeDocs\Database\DBParam())->str(":email", mb_strtolower($this->email))
                    )
                );
            } else {
                throw new \HomeDocs\Exception\InvalidParamsException("id,email");
            }
            if (count($results) == 1) {
                $this->id = $results[0]->id;
                $this->email = $results[0]->email;
                $this->passwordHash = $results[0]->passwordHash;
            } else {
                throw new \HomeDocs\Exception\NotFoundException("");
            }
        }

        public static function isEmailUsed(\HomeDocs\Database\DB $dbh, string $email): bool {
            $results = null;
            if (! empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) && mb_strlen($email) <= 255) {
                $results = $dbh->query(
                    "
                    SELECT
                        USER.id
                    FROM USER
                        WHERE USER.email = :email
                    ",
                    array(
                        (new \HomeDocs\Database\DBParam())->str(":email", mb_strtolower($email))
                    )
                );
            } else {
                throw new \HomeDocs\Exception\InvalidParamsException("id,email");
            }
            return(count($results) == 1);
        }

        public function signIn(\HomeDocs\Database\DB $dbh) {
            if (! empty($this->password)) {
                $this->get($dbh);
                if (password_verify($this->password, $this->passwordHash)) {
                    \HomeDocs\UserSession::set($this->id, $this->email);
                } else {
                    throw new \HomeDocs\Exception\UnauthorizedException("password");
                }
            } else {
                throw new \HomeDocs\Exception\InvalidParamsException("password");
            }
        }

        public static function signOut() {
            \HomeDocs\UserSession::clear();
        }

    }
?>
