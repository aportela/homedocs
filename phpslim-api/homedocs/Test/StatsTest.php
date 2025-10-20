<?php

declare(strict_types=1);

namespace HomeDocs\Test;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class StatsTest extends \HomeDocs\Test\BaseTest
{
    public function testGetTotalPublishedDocuments(): void
    {
        $this->createValidSession();
        $d = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", "document description", null, null, []);
        $d->add(self::$dbh);
        $this->assertTrue(\HomeDocs\Stats::getTotalPublishedDocuments(self::$dbh) > 0);
    }

    public function testGetTotalUploadedAttachments(): void
    {
        $this->createValidSession();
        $d = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", "document description", null, null, []);
        $d->add(self::$dbh);
        $this->assertTrue(\HomeDocs\Stats::getTotalUploadedAttachments(self::$dbh) >= 0);
    }

    public function testGetTotalUploadedAttachmentsDiskUsage(): void
    {
        // TODO: simulate file uploading for adding test attachment
        $this->assertTrue(\HomeDocs\Stats::getTotalUploadedAttachmentsDiskUsage(self::$dbh) >= 0);
    }

    public function testGetActivityHeatMapDataWithoutTimestamp(): void
    {
        $this->createValidSession();
        $d = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", "document description", null, null, []);
        $d->add(self::$dbh);
        $data = \HomeDocs\Stats::getActivityHeatMapData(self::$dbh);
        $this->assertTrue(count($data) >= 1);
        $results = array_values(array_filter($data, function ($obj) {
            return ($obj["date"] == date("Y-m-d"));
        }));
        $this->assertEquals(1, count($results));
        $this->assertEquals(date("Y-m-d"), $results[0]["date"]);
        $this->assertTrue($results[0]["count"] > 0);
    }

    public function testGetActivityHeatMapDataWithTimestamp(): void
    {
        $this->createValidSession();
        $d = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", "document description", null, null, []);
        $d->add(self::$dbh);
        $data = \HomeDocs\Stats::getActivityHeatMapData(self::$dbh, strtotime('-1 day', time()));
        $this->assertTrue(count($data) >= 1);
        $this->assertTrue(count($data) >= 1);
        $results = array_values(array_filter($data, function ($obj) {
            return ($obj["date"] == date("Y-m-d"));
        }));
        $this->assertEquals(1, count($results));
        $this->assertEquals(date("Y-m-d"), $results[0]["date"]);
        $this->assertTrue($results[0]["count"] > 0);
    }
}
