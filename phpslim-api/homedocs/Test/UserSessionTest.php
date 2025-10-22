<?php

declare(strict_types=1);

namespace HomeDocs\Test;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class UserSessionTest extends \HomeDocs\Test\BaseTest
{
    public function testSet(): void
    {
        $id = \HomeDocs\Utils::uuidv4();
        $email = "john@do.e";
        \HomeDocs\UserSession::set($id, $email);
        $this->assertEquals($id, $_SESSION["userId"]);
        $this->assertEquals($email, $_SESSION["email"]);
    }

    public function testIsLogged(): void
    {
        \HomeDocs\UserSession::clear();
        $this->assertFalse(\HomeDocs\UserSession::isLogged());
        $id = \HomeDocs\Utils::uuidv4();
        $email = "john@do.e";
        \HomeDocs\UserSession::set($id, $email);
        $this->assertTrue(\HomeDocs\UserSession::isLogged());
    }

    public function testGetUserId(): void
    {
        $id = \HomeDocs\Utils::uuidv4();
        $email = "john@do.e";
        \HomeDocs\UserSession::set($id, $email);
        $this->assertEquals($id, \HomeDocs\UserSession::getUserId());
    }

    public function testGetEmail(): void
    {
        $id = \HomeDocs\Utils::uuidv4();
        $email = "john@do.e";
        \HomeDocs\UserSession::set($id, $email);
        $this->assertEquals($email, \HomeDocs\UserSession::getEmail());
    }
}
