<?php

declare(strict_types=1);

namespace HomeDocs\Test;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class UserSessionTest extends \HomeDocs\Test\BaseTest
{
    public function testIsLoggedWithoutSession(): void
    {
        \HomeDocs\User::signOut();
        $this->assertFalse(\HomeDocs\UserSession::isLogged());
    }

    public function testIsLogged(): void
    {
        $id = \HomeDocs\Utils::uuidv4();
        $u = new \HomeDocs\User($id, $id . "@server.com", "secret");
        $u->add(self::$dbh);
        $u->signIn(self::$dbh);
        $this->assertTrue(\HomeDocs\UserSession::isLogged());
    }

    public function testGetUserIdWithoutSession(): void
    {
        \HomeDocs\User::signOut();
        $this->assertEmpty(\HomeDocs\UserSession::getUserId());
    }

    public function testGetUserId(): void
    {
        \HomeDocs\User::signOut();
        $id = \HomeDocs\Utils::uuidv4();
        $u = new \HomeDocs\User($id, $id . "@server.com", "secret");
        $u->add(self::$dbh);
        $u->signIn(self::$dbh);
        $this->assertEquals($u->id, \HomeDocs\UserSession::getUserId());
    }

    public function testGetEmailWithoutSession(): void
    {
        \HomeDocs\User::signOut();
        $this->assertEmpty(\HomeDocs\UserSession::getEmail());
    }

    public function testGetEmail(): void
    {
        \HomeDocs\User::signOut();
        $id = \HomeDocs\Utils::uuidv4();
        $u = new \HomeDocs\User($id, $id . "@server.com", "secret");
        $u->add(self::$dbh);
        $u->signIn(self::$dbh);
        $this->assertEquals($u->email, \HomeDocs\UserSession::getEmail());
    }
}
