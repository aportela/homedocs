<?php

declare(strict_types=1);

namespace HomeDocs\Test;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class DocumentTest extends \HomeDocs\Test\BaseTest
{
    private function createValidSession(): void
    {
        $id = \HomeDocs\Utils::uuidv4();
        $u = new \HomeDocs\User($id, $id . "@server.com", "secret");
        $u->add(self::$dbh);
        \HomeDocs\UserSession::set($id, $id . "@server.com");
    }

    /**
     * Initialize the test case
     * Called for every defined test
     */
    public function setUp(): void
    {
        parent::setUp();
        //$this->createValidSession(); // make tests slower, better use only on required tests
    }

    public function testAddWithoutId(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("id");
        $d = new \HomeDocs\Document();
        $d->add(self::$dbh);
    }

    public function testAddWithoutTitle(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("title");
        $d = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4());
        $d->add(self::$dbh);
    }

    public function testAddWithInvalidTitleLength(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("title");
        $d = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), str_repeat("0", \HomeDocs\Constants::MAX_DOCUMENT_TITLE_LENGTH + 1));
        $d->add(self::$dbh);
    }

    public function testAddWithInvalidDescriptionLength(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("description");
        $d = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", str_repeat("0", \HomeDocs\Constants::MAX_DOCUMENT_DESCRIPTION_LENGTH + 1));
        $d->add(self::$dbh);
    }

    public function testAddWithInvalidTagName(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("tag");
        $d = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", "document description", null, null, ["", "tag2"]);
        $d->add(self::$dbh);
    }

    public function testAdd(): void
    {
        $this->expectNotToPerformAssertions();
        $this->createValidSession();
        $d = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", "document description", null, null, ["tag1", "tag2"]);
        $d->add(self::$dbh);
    }

    public function testUpdateWithoutTitle(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("title");
        $d = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), null, "document description", null, null, ["tag1", "tag2"]);
        $d->add(self::$dbh);
        $d->title = null;
        $d->update(self::$dbh);
    }

    public function testUpdateWithInvalidTitleLength(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("title");
        $d = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), str_repeat("0", \HomeDocs\Constants::MAX_DOCUMENT_TITLE_LENGTH + 1), "document description", null, null, ["tag1", "tag2"]);
        $d->add(self::$dbh);
        $d->title = null;
        $d->update(self::$dbh);
    }

    public function testUpdateWithInvalidDescriptionLength(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("description");
        $d = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", str_repeat("0", \HomeDocs\Constants::MAX_DOCUMENT_DESCRIPTION_LENGTH + 1), null, null, ["tag1", "tag2"]);
        $d->add(self::$dbh);
        $d->title = null;
        $d->update(self::$dbh);
    }

    public function testUpdateWithInvalidTagName(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("tag");
        $d = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", "document description", null, null, ["", "tag2"]);
        $d->add(self::$dbh);
        $d->tags = ["", ""];
        $d->update(self::$dbh);
    }

    public function testUpdate(): void
    {
        $this->expectNotToPerformAssertions();
        $this->createValidSession();
        $d = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", "document description", null, null, ["tag1", "tag2"]);
        $d->add(self::$dbh);
        $d->title = "document title updated";
        $d->description = "document description updated";
        $d->tags = ["tag1_updated", "tag2_updated"];
        $d->update(self::$dbh);
    }

    public function testDeleteWithoutId(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("id");
        $d = new \HomeDocs\Document();
        $d->delete(self::$dbh);
    }

    public function testDelete(): void
    {
        $this->expectNotToPerformAssertions();
        $this->createValidSession();
        $d = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", "document description", null, null, ["tag1", "tag2"]);
        $d->add(self::$dbh);
        $d->delete(self::$dbh);
    }

    public function testGetWithoutId(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("id");
        $d = new \HomeDocs\Document();
        $d->get(self::$dbh);
    }

    public function testGetWithNonExistentId(): void
    {
        $this->expectException(\HomeDocs\Exception\NotFoundException::class);
        $this->expectExceptionMessage("id");
        $d = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4());
        $d->get(self::$dbh);
    }

    public function testSearchWithPager(): void
    {
        $results = \HomeDocs\Document::search(self::$dbh, 1, 16, [], "createdOnTimestamp", "DESC");
        $this->assertIsObject($results);
        $this->assertIsObject($results->pagination);
        $this->assertIsArray($results->documents);
        $this->assertTrue($results->pagination->totalResults >= count($results->documents));
    }

    public function testSearchWithPagerAndTitleFilter(): void
    {
        $results = \HomeDocs\Document::search(self::$dbh, 1, 16, ["title" => "condition"], "createdOnTimestamp", "DESC");
        $this->assertIsObject($results);
        $this->assertIsObject($results->pagination);
        $this->assertIsArray($results->documents);
        $this->assertTrue($results->pagination->totalResults >= count($results->documents));
    }

    public function testSearchWithPagerAndFromTimestampFilter(): void
    {
        $results = \HomeDocs\Document::search(self::$dbh, 1, 16, ["fromCreationTimestampCondition" => 1690236000], "createdOnTimestamp", "DESC");
        $this->assertIsObject($results);
        $this->assertIsObject($results->pagination);
        $this->assertIsArray($results->documents);
        $this->assertTrue($results->pagination->totalResults >= count($results->documents));
    }

    public function testSearchWithPagerAndToTimestampFilter(): void
    {
        $results = \HomeDocs\Document::search(self::$dbh, 1, 16, ["toCreationTimestampCondition" => 1690408799], "createdOnTimestamp", "DESC");
        $this->assertIsObject($results);
        $this->assertIsObject($results->pagination);
        $this->assertIsArray($results->documents);
        $this->assertTrue($results->pagination->totalResults >= count($results->documents));
    }

    public function testSearchWithPagerAndDescriptionFilter(): void
    {
        $results = \HomeDocs\Document::search(self::$dbh, 1, 16, ["description" => "condition"], "createdOnTimestamp", "DESC");
        $this->assertIsObject($results);
        $this->assertIsObject($results->pagination);
        $this->assertIsArray($results->documents);
        $this->assertTrue($results->pagination->totalResults >= count($results->documents));
    }

    public function testSearchWithPagerAndTagsFilter(): void
    {
        $results = \HomeDocs\Document::search(self::$dbh, 1, 16, ["tags" => ["tag1", "tag2"]], "createdOnTimestamp", "DESC");
        $this->assertIsObject($results);
        $this->assertIsObject($results->pagination);
        $this->assertIsArray($results->documents);
        $this->assertTrue($results->pagination->totalResults >= count($results->documents));
    }

    public function testSearchWithoutPager(): void
    {
        $results = \HomeDocs\Document::search(self::$dbh, 1, 0, [], "createdOnTimestamp", "DESC");
        $this->assertIsObject($results);
        $this->assertIsObject($results->pagination);
        $this->assertIsArray($results->documents);
        $this->assertTrue($results->pagination->totalResults == count($results->documents));
    }

    public function testSearchRecent(): void
    {
        $this->assertIsArray(\HomeDocs\Document::searchRecent(self::$dbh));
    }
}
