<?php

declare(strict_types=1);

namespace HomeDocs\Test;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class TagTest extends \HomeDocs\Test\BaseTest
{
    public function testGetCloud(): void
    {
        $tag1 = sprintf("tag_%d", time());
        $tag2 =  sprintf("tag_%d", time() + 1);
        $this->createValidSession();
        $d = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", "document description", null, null, [$tag1, $tag2]);
        $d->add(self::$dbh);
        
        $tagCloud = \HomeDocs\Tag::getCloud(self::$dbh);
        $this->assertTrue(count($tagCloud) >= 2);
        $tags = array_values(array_filter($tagCloud, fn($obj) => $obj->tag == $tag1));
        $this->assertEquals(1, count($tags));
        $this->assertEquals($tag1, $tags[0]->tag);
        $this->assertEquals(1, $tags[0]->total);
        $tags = array_values(array_filter($tagCloud, fn($obj) => $obj->tag == $tag2));
        $this->assertEquals(1, count($tags));
        $this->assertEquals($tag2, $tags[0]->tag);
        $this->assertEquals(1, $tags[0]->total);
    }

    public function testSearch(): void
    {

        $tag1 = sprintf("tag_%d", time());
        $tag2 =  sprintf("tag_%d", time() + 1);
        $this->createValidSession();
        $d = new \HomeDocs\Document(\HomeDocs\Utils::uuidv4(), "document title", "document description", null, null, [$tag1, $tag2]);
        $d->add(self::$dbh);
        
        $tags = \HomeDocs\Tag::search(self::$dbh);
        $this->assertTrue(count($tags) >= 2);
        $this->assertContains($tag1, $tags);
        $this->assertContains($tag2, $tags);
    }
}
