<?php

declare(strict_types=1);

namespace HomeDocs\Test;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class AttachmentShareTest extends \HomeDocs\Test\BaseTest
{
    public function testAddAttachmentShareWithoutAttachmentId(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("attachmentId");
        $this->createValidSession();
        $attachmentShare = new \HomeDocs\AttachmentShare("", 0, 0, 0, true);
        $attachmentShare->add(self::$dbh, "");
    }

    public function testAddAttachmentShare(): void
    {
        $this->expectNotToPerformAssertions();
        $this->createValidSession();
        $attachment = new \HomeDocs\Attachment(\HomeDocs\Utils::uuidv4(), "file.txt", 10, sha1("file.txt"), null, false);
        $attachment->saveMetadata(self::$dbh);

        $attachmentShare = new \HomeDocs\AttachmentShare("", 0, 0, 0, true);
        $attachmentShare->add(self::$dbh, $attachment->id);
    }

    public function testUpdateAttachmentShareWithoutAttachmentId(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("attachmentId");
        $this->createValidSession();
        $attachment = new \HomeDocs\Attachment(\HomeDocs\Utils::uuidv4(), "file.txt", 10, sha1("file.txt"), null, false);
        $attachment->saveMetadata(self::$dbh);

        $attachmentShare = new \HomeDocs\AttachmentShare("", 0, 0, 0, true);
        $attachmentShare->add(self::$dbh, $attachment->id);
        $attachmentShare->expiresAtTimestamp = intval(microtime(true) * 1000);
        $attachmentShare->accessLimit = 2;
        $attachmentShare->enabled = true;
        $attachmentShare->update(self::$dbh, "");
    }

    public function testUpdateAttachmentShare(): void
    {
        $this->expectNotToPerformAssertions();
        $this->createValidSession();
        $attachment = new \HomeDocs\Attachment(\HomeDocs\Utils::uuidv4(), "file.txt", 10, sha1("file.txt"), null, false);
        $attachment->saveMetadata(self::$dbh);

        $attachmentShare = new \HomeDocs\AttachmentShare("", 0, 0, 0, true);
        $attachmentShare->add(self::$dbh, $attachment->id);
        $attachmentShare->expiresAtTimestamp = intval(microtime(true) * 1000);
        $attachmentShare->accessLimit = 2;
        $attachmentShare->enabled = true;
        $attachmentShare->update(self::$dbh, $attachment->id);
    }

    public function testDeleteAttachmentShareWithoutAttachmentId(): void
    {
        $this->expectException(\HomeDocs\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("attachmentId");
        $this->createValidSession();
        $attachment = new \HomeDocs\Attachment(\HomeDocs\Utils::uuidv4(), "file.txt", 10, sha1("file.txt"), null, false);
        $attachment->saveMetadata(self::$dbh);

        $attachmentShare = new \HomeDocs\AttachmentShare("", 0, 0, 0, true);
        $attachmentShare->add(self::$dbh, $attachment->id);
        $attachmentShare->delete(self::$dbh, "");
    }

    public function testDeleteAttachmentShare(): void
    {
        $this->expectNotToPerformAssertions();
        $this->createValidSession();
        $attachment = new \HomeDocs\Attachment(\HomeDocs\Utils::uuidv4(), "file.txt", 10, sha1("file.txt"), null, false);
        $attachment->saveMetadata(self::$dbh);

        $attachmentShare = new \HomeDocs\AttachmentShare("", 0, 0, 0, true);
        $attachmentShare->add(self::$dbh, $attachment->id);
        $attachmentShare->delete(self::$dbh, $attachment->id);
    }
}
