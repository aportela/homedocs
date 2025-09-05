<?php

declare(strict_types=1);

namespace HomeDocs\Test;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class StatsTest extends \HomeDocs\Test\BaseTest
{
    public function testGetTotalPublishedDocuments(): void
    {
        $total = \HomeDocs\Stats::getTotalPublishedDocuments(self::$dbh);
        $this->assertIsInt($total);
        $this->assertTrue($total >= 0);
    }

    public function testGetTotalUploadedAttachments(): void
    {
        $total = \HomeDocs\Stats::getTotalUploadedAttachments(self::$dbh);
        $this->assertIsInt($total);
        $this->assertTrue($total >= 0);
    }

    public function testGetTotalUploadedAttachmentsDiskUsage(): void
    {
        $total = \HomeDocs\Stats::getTotalUploadedAttachmentsDiskUsage(self::$dbh);
        $this->assertIsInt($total);
        $this->assertTrue($total >= 0);
    }

    public function testGetActivityHeatMapData(): void
    {
        $data = \HomeDocs\Stats::getActivityHeatMapData(self::$dbh);
        $this->assertIsArray($data);
        $this->assertTrue(count($data) >= 0);
    }
}
