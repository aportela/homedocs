<?php

declare(strict_types=1);

namespace HomeDocs\Test;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class UserTest extends \HomeDocs\Test\BaseTest
{
    private function isSignUpAllowed(): bool
    {
        $allowed = self::$settings['common']['allowSignUp'];
        if (is_bool($allowed)) {
            return ($allowed);
        } else {
            return (false);
        }
    }

    public function testAddWithoutId(): void
    {
        if ($this->isSignUpAllowed()) {
            $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("id");
            $id = \HomeDocs\Utils::uuidv4();
            new \HomeDocs\User("", $id . "@localhost.localnet", "secret")->add(self::$dbh);
        } else {
            $this->markTestSkipped("This test can not be run (allowSignUp disabled in settings)");
        }
    }

    public function testAddWithoutEmail(): void
    {
        if ($this->isSignUpAllowed()) {
            $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("email");
            $id = \HomeDocs\Utils::uuidv4();
            new \HomeDocs\User($id, "", "secret")->add(self::$dbh);
        } else {
            $this->markTestSkipped("This test can not be run (allowSignUp disabled in settings)");
        }
    }

    public function testAddWithoutValidEmailLength(): void
    {
        if ($this->isSignUpAllowed()) {
            $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("email");
            $id = \HomeDocs\Utils::uuidv4();
            new \HomeDocs\User($id, str_repeat($id, 10) . "@localhost.localnet", "secret")->add(self::$dbh);
        } else {
            $this->markTestSkipped("This test can not be run (allowSignUp disabled in settings)");
        }
    }

    public function testAddWithoutValidEmail(): void
    {
        if ($this->isSignUpAllowed()) {
            $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("email");
            $id = \HomeDocs\Utils::uuidv4();
            new \HomeDocs\User($id, $id, "secret")->add(self::$dbh);
        } else {
            $this->markTestSkipped("This test can not be run (allowSignUp disabled in settings)");
        }
    }

    public function testAddWithoutPassword(): void
    {
        if ($this->isSignUpAllowed()) {
            $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("password");
            $id = \HomeDocs\Utils::uuidv4();
            new \HomeDocs\User($id, $id . "@localhost.localnet", "")->add(self::$dbh);
        } else {
            $this->markTestSkipped("This test can not be run (allowSignUp disabled in settings)");
        }
    }

    public function testAdd(): void
    {
        if ($this->isSignUpAllowed()) {
            $this->expectNotToPerformAssertions();
            $id = \HomeDocs\Utils::uuidv4();
            new \HomeDocs\User($id, $id . "@localhost.localnet", "secret")->add(self::$dbh);
        } else {
            $this->markTestSkipped("This test can not be run (allowSignUp disabled in settings)");
        }
    }

    public function testUpdateWithoutId(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("id");
        $id = \HomeDocs\Utils::uuidv4();
        new \HomeDocs\User("", $id . "@localhost.localnet", "secret")->update(self::$dbh);
    }

    public function testUpdateWithoutEmail(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("email");
        $id = \HomeDocs\Utils::uuidv4();
        new \HomeDocs\User($id, "", "secret")->update(self::$dbh);
    }

    public function testUpdateWithoutValidEmailLength(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("email");
        $id = \HomeDocs\Utils::uuidv4();
        new \HomeDocs\User($id, str_repeat($id, 10) . "@localhost.localnet", "secret")->update(self::$dbh);
    }

    public function testUpdateWithoutValidEmail(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("email");
        $id = \HomeDocs\Utils::uuidv4();
        new \HomeDocs\User($id, $id, "secret")->update(self::$dbh);
    }

    public function testUpdate(): void
    {
        $this->expectNotToPerformAssertions();
        $id = \HomeDocs\Utils::uuidv4();
        $user = new \HomeDocs\User($id, $id . "@server.com", "secret");
        $user->add(self::$dbh);
        $user->login(self::$dbh);
        $user->update(self::$dbh);
    }

    public function testGetWithoutIdOrEmail(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("id,email");
        new \HomeDocs\User("", "", "secret")->get(self::$dbh);
    }

    public function testGetWithoutValidEmailLength(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("id,email");
        $id = \HomeDocs\Utils::uuidv4();
        new \HomeDocs\User("", str_repeat($id, 10) . "@server.com", "secret")->get(self::$dbh);
    }

    public function testGetWithoutValidEmail(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("id,email");
        $id = \HomeDocs\Utils::uuidv4();
        new \HomeDocs\User("", $id, "secret")->get(self::$dbh);
    }

    public function testGetWithNonExistentId(): void
    {
        $this->expectException(\HomeDocs\Exception\NotFoundException::class);
        $id = \HomeDocs\Utils::uuidv4();
        new \HomeDocs\User($id, $id, "secret")->get(self::$dbh);
    }

    public function testGetWithNonExistentEmail(): void
    {
        $this->expectException(\HomeDocs\Exception\NotFoundException::class);
        $id = \HomeDocs\Utils::uuidv4();
        new \HomeDocs\User($id, $id . "@server.com", "secret")->get(self::$dbh);
    }

    public function testGet(): void
    {
        $id = \HomeDocs\Utils::uuidv4();
        $user = new \HomeDocs\User($id, $id . "@server.com", "secret");
        $user->add(self::$dbh);
        $user->get(self::$dbh);
        $this->assertTrue($id == $user->id);
    }

    public function testExists(): void
    {
        $id = \HomeDocs\Utils::uuidv4();
        $user = new \HomeDocs\User($id, $id . "@server.com", "secret");
        $user->add(self::$dbh);
        $this->assertTrue($user->exists(self::$dbh));
    }

    public function testNotExists(): void
    {
        $id = \HomeDocs\Utils::uuidv4();
        $user = new \HomeDocs\User($id, $id . "@server.com", "secret");
        $this->assertFalse($user->exists(self::$dbh));
    }

    public function testExistsEmailWithNonExistentEmail(): void
    {
        $id = \HomeDocs\Utils::uuidv4();
        $this->assertFalse(\HomeDocs\User::isEmailUsed(self::$dbh, $id . "@server.com"));
    }

    public function testExistsEmailWithExistentEmail(): void
    {
        $id = \HomeDocs\Utils::uuidv4();
        $user = new \HomeDocs\User($id, $id . "@server.com", "secret");
        $user->add(self::$dbh);
        $this->assertIsString($user->email);
        $this->assertTrue(\HomeDocs\User::isEmailUsed(self::$dbh, $user->email));
    }

    public function testLoginWithoutIdOrEmail(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("id,email");
        new \HomeDocs\User("", "", "secret")->login(self::$dbh);
    }

    public function testLoginWithoutPassword(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("password");
        $id = \HomeDocs\Utils::uuidv4();
        new \HomeDocs\User($id, $id . "@server.com", "")->login(self::$dbh);
    }

    public function testLoginWithoutExistentEmail(): void
    {
        $this->expectException(\HomeDocs\Exception\NotFoundException::class);
        $id = \HomeDocs\Utils::uuidv4();
        new \HomeDocs\User($id, $id . "@server.com", "secret")->login(self::$dbh);
    }

    public function testLoginWithoutValidEmailLength(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("email");
        $id = \HomeDocs\Utils::uuidv4();
        $user = new \HomeDocs\User($id, $id, "secret");
        $user->add(self::$dbh);
        $user->email = str_repeat($id, 10) . "@server.com";
        $user->login(self::$dbh);
    }

    public function testLoginWithoutValidEmail(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("email");
        $id = \HomeDocs\Utils::uuidv4();
        $user = new \HomeDocs\User($id, $id, "secret");
        $user->add(self::$dbh);
        $user->email = $id;
        $user->login(self::$dbh);
    }

    public function testLoginWithInvalidPassword(): void
    {
        $this->expectException(\HomeDocs\Exception\UnauthorizedException::class);
        $this->expectExceptionMessage("password");
        $id = \HomeDocs\Utils::uuidv4();
        $user = new \HomeDocs\User($id, $id . "@server.com", "secret");
        $user->add(self::$dbh);
        $user->password = "other";
        $user->login(self::$dbh);
    }

    public function testLogin(): void
    {
        $id = \HomeDocs\Utils::uuidv4();
        $user = new \HomeDocs\User($id, $id . "@server.com", "secret");
        $user->add(self::$dbh);
        $this->assertTrue($user->login(self::$dbh));
    }

    public function testLogout(): void
    {
        $this->assertTrue(\HomeDocs\User::logout());
    }
}
