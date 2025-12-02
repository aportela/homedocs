<?php

declare(strict_types=1);

namespace HomeDocs\Test;

use Rector\Naming\PhpArray\ArrayFilter;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class StatsTest extends \HomeDocs\Test\BaseTest
{
    public function testGetTotalPublishedDocuments(): void
    {
        $this->createValidSession();
        $document = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", "document description", null, null, []);
        $document->add(self::$dbh);
        $this->assertTrue(\HomeDocs\Stats::getTotalPublishedDocuments(self::$dbh, strval(\HomeDocs\UserSession::getUserId())) > 0);
    }

    public function testGetTotalUploadedAttachments(): void
    {
        $this->createValidSession();
        $document = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", "document description", null, null, []);
        $document->add(self::$dbh);
        $this->assertTrue(\HomeDocs\Stats::getTotalUploadedAttachments(self::$dbh, strval(\HomeDocs\UserSession::getUserId())) >= 0);
    }

    public function testGetTotalUploadedAttachmentsDiskUsage(): void
    {
        // TODO: simulate file uploading for adding test attachment
        $this->assertTrue(\HomeDocs\Stats::getTotalUploadedAttachmentsDiskUsage(self::$dbh, strval(\HomeDocs\UserSession::getUserId())) >= 0);
    }

    public function testGetActivityHeatMapDataWithoutTimestamp(): void
    {
        $this->createValidSession();
        $document = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", "document description", null, null, []);
        $document->add(self::$dbh);

        $data = \HomeDocs\Stats::getActivityHeatMapData(self::$dbh, strval(\HomeDocs\UserSession::getUserId()));
        $this->assertTrue(count($data) >= 1);
        foreach ($data as $item) {
            $this->assertTrue(property_exists($item, "date"));
            $this->assertTrue(is_string($item->date));
            $this->assertTrue(property_exists($item, "count"));
            $this->assertIsInt($item->count);
            $this->assertTrue($item->count > 0);
        }
    }

    public function testGetActivityHeatMapDataWithTimestamp(): void
    {
        $this->createValidSession();
        $document = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", "document description", null, null, []);
        $document->add(self::$dbh);

        $data = \HomeDocs\Stats::getActivityHeatMapData(self::$dbh, strval(\HomeDocs\UserSession::getUserId()), strtotime('-1 day', time()) * 1000);
        $this->assertTrue(count($data) >= 1);
        foreach ($data as $item) {
            $this->assertTrue(property_exists($item, "date"));
            $this->assertTrue(is_string($item->date));
            $this->assertTrue(property_exists($item, "count"));
            $this->assertIsInt($item->count);
            $this->assertTrue($item->count > 0);
        }
    }
}
