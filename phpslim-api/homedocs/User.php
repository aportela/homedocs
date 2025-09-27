<?php

declare(strict_types=1);

namespace HomeDocs;

class User
{
    public ?string $id;
    public ?string $email;
    public ?string $password;
    public ?string $passwordHash;

    public function __construct(string $id = "", string $email = "", string $password = "")
    {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
    }

    private function passwordHash(string $password = ""): string
    {
        return (password_hash($password, PASSWORD_BCRYPT, array('cost' => 12)));
    }

    private function validateAndPrepareParams(): array
    {
        if (empty($this->id) || mb_strlen($this->id) !== 36) {
            throw new \HomeDocs\Exception\InvalidParamsException("id");
        }
        if (empty($this->email) || mb_strlen($this->email) > 255 || !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new \HomeDocs\Exception\InvalidParamsException("email");
        }
        if (empty($this->password)) {
            throw new \HomeDocs\Exception\InvalidParamsException("password");
        }

        return [
            new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower($this->id)),
            new \aportela\DatabaseWrapper\Param\StringParam(":email", mb_strtolower($this->email)),
            new \aportela\DatabaseWrapper\Param\StringParam(":password_hash", $this->passwordHash($this->password)),
        ];
    }
    public function add(\aportela\DatabaseWrapper\DB $dbh): void
    {
        $params = $this->validateAndPrepareParams();
        $dbh->exec(" INSERT INTO USER (id, email, password_hash) VALUES(:id, :email, :password_hash) ", $params);
    }

    public function update(\aportela\DatabaseWrapper\DB $dbh): void
    {
        $params = $this->validateAndPrepareParams();
        $dbh->exec(" UPDATE USER SET email = :email, password_hash = :password_hash WHERE id = :id ", $params);
    }

    public function get(\aportela\DatabaseWrapper\DB $dbh): void
    {
        $results = null;
        if (!empty($this->id) && mb_strlen($this->id) == 36) {
            $results = $dbh->query(
                "
                        SELECT
                            USER.id, USER.email, USER.password_hash AS passwordHash
                        FROM USER
                        WHERE USER.id = :id
                    ",
                array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower($this->id))
                )
            );
        } elseif (!empty($this->email) && filter_var($this->email, FILTER_VALIDATE_EMAIL) && mb_strlen($this->email) <= 255) {
            $results = $dbh->query(
                "
                    SELECT
                        USER.id, USER.email, USER.password_hash AS passwordHash
                    FROM USER
                        WHERE USER.email = :email
                    ",
                array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":email", mb_strtolower($this->email))
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

    public function exists(\aportela\DatabaseWrapper\DB $dbh): bool
    {
        try {
            $this->get($dbh);
            return (true);
        } catch (\HomeDocs\Exception\NotFoundException $e) {
            return (false);
        }
    }

    public static function isEmailUsed(\aportela\DatabaseWrapper\DB $dbh, string $email): bool
    {
        $results = null;
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) && mb_strlen($email) <= 255) {
            $results = $dbh->query(
                "
                    SELECT
                        USER.id
                    FROM USER
                        WHERE USER.email = :email
                    ",
                array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":email", mb_strtolower($email))
                )
            );
        } else {
            throw new \HomeDocs\Exception\InvalidParamsException("id,email");
        }
        return (count($results) == 1);
    }

    public function signIn(\aportela\DatabaseWrapper\DB $dbh): bool
    {
        if (!empty($this->password)) {
            $this->get($dbh);
            if (password_verify($this->password, $this->passwordHash)) {
                \HomeDocs\UserSession::set($this->id, $this->email);
                return (true);
            } else {
                throw new \HomeDocs\Exception\UnauthorizedException("password");
            }
        } else {
            throw new \HomeDocs\Exception\InvalidParamsException("password");
        }
    }

    public static function signOut(): bool
    {
        \HomeDocs\UserSession::clear();
        return (true);
    }
}
