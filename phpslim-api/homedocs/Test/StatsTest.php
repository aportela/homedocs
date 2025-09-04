<?php

declare(strict_types=1);

namespace HomeDocs\Test;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class StatsTest extends \HomeDocs\Test\BaseTest
{
    public function testDocumentsCount(): void
    {
        $total = \HomeDocs\Stats::documentsCount(self::$dbh);
        $this->assertIsInt($total);
        $this->assertTrue($total >= 0);
    }

    public function testAttachmentsCount(): void
    {
        $total = \HomeDocs\Stats::attachmentsCount(self::$dbh);
        $this->assertIsInt($total);
        $this->assertTrue($total >= 0);
    }

    public function testAttachmentsDiskSize(): void
    {
        $total = \HomeDocs\Stats::attachmentsDiskSize(self::$dbh);
        $this->assertIsInt($total);
        $this->assertTrue($total >= 0);
    }
}
