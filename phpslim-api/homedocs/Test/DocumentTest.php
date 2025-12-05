<?php

declare(strict_types=1);

namespace HomeDocs\Test;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class DocumentTest extends \HomeDocs\Test\BaseTest
{
    public function testAddWithoutId(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("id");
        $document = new \HomeDocs\Document();
        $document->add(self::$dbh);
    }

    public function testAddWithoutTitle(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("title");
        $document = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4());
        $document->add(self::$dbh);
    }

    public function testAddWithInvalidTitleLength(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("title");
        $document = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), str_repeat("0", \HomeDocs\Constants::MAX_DOCUMENT_TITLE_LENGTH + 1));
        $document->add(self::$dbh);
    }

    public function testAddWithInvalidDescriptionLength(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("description");
        $document = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", str_repeat("0", \HomeDocs\Constants::MAX_DOCUMENT_DESCRIPTION_LENGTH + 1));
        $document->add(self::$dbh);
    }

    public function testAddWithInvalidTagName(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("tag");
        $document = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", "document description", null, null, ["", "tag2"]);
        $document->add(self::$dbh);
    }

    public function testAdd(): void
    {
        $this->expectNotToPerformAssertions();
        $this->createValidSession();
        $document = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", "document description", null, null, ["tag1", "tag2"]);
        $document->add(self::$dbh);
    }

    public function testUpdateWithoutTitle(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("title");
        $document = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), null, "document description", null, null, ["tag1", "tag2"]);
        $document->add(self::$dbh);
        $document->title = null;
        $document->update(self::$dbh);
    }

    public function testUpdateWithInvalidTitleLength(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("title");
        $document = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), str_repeat("0", \HomeDocs\Constants::MAX_DOCUMENT_TITLE_LENGTH + 1), "document description", null, null, ["tag1", "tag2"]);
        $document->add(self::$dbh);
        $document->title = null;
        $document->update(self::$dbh);
    }

    public function testUpdateWithInvalidDescriptionLength(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("description");
        $document = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", str_repeat("0", \HomeDocs\Constants::MAX_DOCUMENT_DESCRIPTION_LENGTH + 1), null, null, ["tag1", "tag2"]);
        $document->add(self::$dbh);
        $document->title = null;
        $document->update(self::$dbh);
    }

    public function testUpdateWithInvalidTagName(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("tag");
        $document = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", "document description", null, null, ["", "tag2"]);
        $document->add(self::$dbh);
        $document->tags = ["", ""];
        $document->update(self::$dbh);
    }

    public function testUpdate(): void
    {
        $this->expectNotToPerformAssertions();
        $this->createValidSession();
        $document = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", "document description", null, null, ["tag1", "tag2"]);
        $document->add(self::$dbh);
        $document->title = "document title updated";
        $document->description = "document description updated";
        $document->tags = ["tag1_updated", "tag2_updated"];
        $document->update(self::$dbh);
    }

    public function testDeleteWithoutId(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("id");
        $document = new \HomeDocs\Document();
        $document->delete(self::$dbh);
    }

    public function testDelete(): void
    {
        $this->expectNotToPerformAssertions();
        $this->createValidSession();
        $document = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", "document description", null, null, ["tag1", "tag2"]);
        $document->add(self::$dbh);
        $document->delete(self::$dbh);
    }

    public function testGetWithoutId(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("id");
        $document = new \HomeDocs\Document();
        $document->get(self::$dbh);
    }

    public function testGetWithNonExistentId(): void
    {
        $this->expectException(\HomeDocs\Exception\NotFoundException::class);
        $this->expectExceptionMessage("id");
        $document = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4());
        $document->get(self::$dbh);
    }

    public function testGet(): void
    {
        $this->expectNotToPerformAssertions();
        $this->createValidSession();
        $document = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", "document description", null, null, ["tag1", "tag2"]);
        $document->add(self::$dbh);
        $document->get(self::$dbh);
    }

    public function testSearchWithPager(): void
    {
        $results = \HomeDocs\Document::search(
            self::$dbh,
            new \aportela\DatabaseBrowserWrapper\Pager(true, 1, 16),
            new \HomeDocs\DocumentSearchFilter(["filter" => []], \HomeDocs\UserSession::getUserId() ?? ""),
            "createdAtTimestamp",
            \aportela\DatabaseBrowserWrapper\Order::ASC
        );
        $this->assertIsObject($results->pagination);
        $this->assertIsArray($results->documents);
        $this->assertTrue(($results->pagination->totalResults ?? 0) >= count($results->documents));
    }

    public function testSearchWithPagerAndTitleFilter(): void
    {
        $results = \HomeDocs\Document::search(
            self::$dbh,
            new \aportela\DatabaseBrowserWrapper\Pager(true, 1, 16),
            new \HomeDocs\DocumentSearchFilter(["filter" => ["text" => ["title" => "condition"]]], \HomeDocs\UserSession::getUserId() ?? ""),
            "createdAtTimestamp",
            \aportela\DatabaseBrowserWrapper\Order::DESC
        );
        $this->assertIsObject($results->pagination);
        $this->assertIsArray($results->documents);
        $this->assertTrue(($results->pagination->totalResults ?? 0) >= count($results->documents));
    }

    public function testSearchWithPagerAndDescriptionFilter(): void
    {
        $results = \HomeDocs\Document::search(
            self::$dbh,
            new \aportela\DatabaseBrowserWrapper\Pager(true, 1, 16),
            new \HomeDocs\DocumentSearchFilter(["filter" => ["text" => ["description" => "condition"]]], \HomeDocs\UserSession::getUserId() ?? ""),
            "createdAtTimestamp",
            \aportela\DatabaseBrowserWrapper\Order::ASC
        );
        $this->assertIsObject($results->pagination);
        $this->assertIsArray($results->documents);
        $this->assertTrue(($results->pagination->totalResults ?? 0) >= count($results->documents));
    }

    public function testSearchWithPagerAndNotesBodyFilter(): void
    {
        $results = \HomeDocs\Document::search(
            self::$dbh,
            new \aportela\DatabaseBrowserWrapper\Pager(true, 1, 16),
            new \HomeDocs\DocumentSearchFilter(["filter" => ["text" => ["notesBody" => "condition"]]], \HomeDocs\UserSession::getUserId() ?? ""),
            "createdAtTimestamp",
            \aportela\DatabaseBrowserWrapper\Order::ASC
        );
        $this->assertIsObject($results->pagination);
        $this->assertIsArray($results->documents);
        $this->assertTrue(($results->pagination->totalResults ?? 0) >= count($results->documents));
    }

    public function testSearchWithPagerAndAttachmentsFilenameFilter(): void
    {
        $results = \HomeDocs\Document::search(
            self::$dbh,
            new \aportela\DatabaseBrowserWrapper\Pager(true, 1, 16),
            new \HomeDocs\DocumentSearchFilter(["filter" => ["text" => ["attachmentsFilename" => "condition"]]], \HomeDocs\UserSession::getUserId() ?? ""),
            "createdAtTimestamp",
            \aportela\DatabaseBrowserWrapper\Order::DESC
        );
        $this->assertIsObject($results->pagination);
        $this->assertIsArray($results->documents);
        $this->assertTrue(($results->pagination->totalResults ?? 0) >= count($results->documents));
    }

    public function testSearchWithPagerAndTagsFilter(): void
    {
        $results = \HomeDocs\Document::search(
            self::$dbh,
            new \aportela\DatabaseBrowserWrapper\Pager(true, 1, 16),
            new \HomeDocs\DocumentSearchFilter(["filter" => ["tags" => ["tag1", "tag2"]]], \HomeDocs\UserSession::getUserId() ?? ""),
            "createdAtTimestamp",
            \aportela\DatabaseBrowserWrapper\Order::DESC
        );
        $this->assertIsObject($results->pagination);
        $this->assertIsArray($results->documents);
        $this->assertTrue(($results->pagination->totalResults ?? 0) >= count($results->documents));
    }

    public function testSearchWithPagerAndFromCreationTimestampFilter(): void
    {
        $results = \HomeDocs\Document::search(
            self::$dbh,
            new \aportela\DatabaseBrowserWrapper\Pager(true, 1, 16),
            new \HomeDocs\DocumentSearchFilter(["filter" => ["dates" => ["createdAt" => ["timestamps" => ["from" => 1690236000]]]]], \HomeDocs\UserSession::getUserId() ?? ""),
            "createdAtTimestamp",
            \aportela\DatabaseBrowserWrapper\Order::ASC
        );
        $this->assertIsObject($results->pagination);
        $this->assertIsArray($results->documents);
        $this->assertTrue(($results->pagination->totalResults ?? 0) >= count($results->documents));
    }

    public function testSearchWithPagerAndToCreationTimestampFilter(): void
    {
        $results = \HomeDocs\Document::search(
            self::$dbh,
            new \aportela\DatabaseBrowserWrapper\Pager(true, 1, 16),
            new \HomeDocs\DocumentSearchFilter(["filter" => ["dates" => ["createdAt" => ["timestamps" => ["to" => 1690408799]]]]], \HomeDocs\UserSession::getUserId() ?? ""),
            "createdAtTimestamp",
            \aportela\DatabaseBrowserWrapper\Order::DESC
        );
        $this->assertIsObject($results->pagination);
        $this->assertIsArray($results->documents);
        $this->assertTrue(($results->pagination->totalResults ?? 0) >= count($results->documents));
    }

    public function testSearchWithPagerAndFromLastUpdateTimestampFilter(): void
    {
        $results = \HomeDocs\Document::search(
            self::$dbh,
            new \aportela\DatabaseBrowserWrapper\Pager(true, 1, 16),
            new \HomeDocs\DocumentSearchFilter(["filter" => ["dates" => ["lastUpdateAt" => ["timestamps" => ["from" => 1690236000]]]]], \HomeDocs\UserSession::getUserId() ?? ""),
            "lastUpdateTimestamp",
            \aportela\DatabaseBrowserWrapper\Order::ASC
        );
        $this->assertIsObject($results->pagination);
        $this->assertIsArray($results->documents);
        $this->assertTrue(($results->pagination->totalResults ?? 0) >= count($results->documents));
    }

    public function testSearchWithPagerAndToLastUpdateTimestampFilter(): void
    {
        $results = \HomeDocs\Document::search(
            self::$dbh,
            new \aportela\DatabaseBrowserWrapper\Pager(true, 1, 16),
            new \HomeDocs\DocumentSearchFilter(["filter" => ["dates" => ["lastUpdateAt" => ["timestamps" => ["to" => 1690408799]]]]], \HomeDocs\UserSession::getUserId() ?? ""),
            "lastUpdateTimestamp",
            \aportela\DatabaseBrowserWrapper\Order::DESC
        );
        $this->assertIsObject($results->pagination);
        $this->assertIsArray($results->documents);
        $this->assertTrue(($results->pagination->totalResults ?? 0) >= count($results->documents));
    }

    public function testSearchWithPagerAndFromUpdatedOnTimestampFilter(): void
    {
        $results = \HomeDocs\Document::search(
            self::$dbh,
            new \aportela\DatabaseBrowserWrapper\Pager(true, 1, 16),
            new \HomeDocs\DocumentSearchFilter(["filter" => ["dates" => ["updatedAt" => ["timestamps" => ["from" => 1690236000]]]]], \HomeDocs\UserSession::getUserId() ?? ""),
            "lastUpdateTimestamp",
            \aportela\DatabaseBrowserWrapper\Order::ASC
        );
        $this->assertIsObject($results->pagination);
        $this->assertIsArray($results->documents);
        $this->assertTrue(($results->pagination->totalResults ?? 0) >= count($results->documents));
    }

    public function testSearchWithPagerAndToUpdatedOnTimestampFilter(): void
    {
        $results = \HomeDocs\Document::search(
            self::$dbh,
            new \aportela\DatabaseBrowserWrapper\Pager(true, 1, 16),
            new \HomeDocs\DocumentSearchFilter(["filter" => ["dates" => ["updatedAt" => ["timestamps" => ["to" => 1690408799]]]]], \HomeDocs\UserSession::getUserId() ?? ""),
            "lastUpdateTimestamp",
            \aportela\DatabaseBrowserWrapper\Order::DESC
        );
        $this->assertIsObject($results->pagination);
        $this->assertIsArray($results->documents);
        $this->assertTrue(($results->pagination->totalResults ?? 0) >= count($results->documents));
    }

    public function testSearchRecent(): void
    {
        $recentDocuments = \HomeDocs\Document::searchRecent(self::$dbh, \HomeDocs\UserSession::getUserId() ?? "", 8);
        $total = count($recentDocuments);
        $this->assertTrue($total <= 8);
    }
}
