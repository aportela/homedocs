<?php

declare(strict_types=1);

namespace HomeDocs\Test;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class TagTest extends \HomeDocs\Test\BaseTest
{
    public function testGetCloud(): void
    {
        $this->assertIsArray(\HomeDocs\Tag::getCloud(self::$dbh));
    }

    public function testSearch(): void
    {
        $this->assertIsArray(\HomeDocs\Tag::search(self::$dbh));
    }
}
