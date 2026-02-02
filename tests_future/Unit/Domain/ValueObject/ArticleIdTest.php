<?php

declare(strict_types=1);

namespace Vision2026\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use Vision2026\Domain\ValueObject\ArticleId;
use InvalidArgumentException;

/**
 * Unit tests for the ArticleId (ULID) value object.
 *
 * ULIDs are Universally Unique Lexicographically Sortable Identifiers.
 * They're time-sortable, meaning newer IDs sort after older ones.
 */
final class ArticleIdTest extends TestCase
{
    // =========================================================================
    // Generation Tests
    // =========================================================================

    public function testGenerateCreatesValidUlid(): void
    {
        $id = ArticleId::generate();

        $this->assertEquals(26, strlen($id->value));
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $id->value);
    }

    public function testGenerateCreatesUniqueIds(): void
    {
        $ids = [];
        for ($i = 0; $i < 100; $i++) {
            $ids[] = ArticleId::generate()->value;
        }

        $uniqueIds = array_unique($ids);
        $this->assertCount(100, $uniqueIds, 'All generated IDs should be unique');
    }

    public function testGeneratedIdsAreSortable(): void
    {
        $id1 = ArticleId::generate();
        usleep(1000); // 1ms delay
        $id2 = ArticleId::generate();
        usleep(1000);
        $id3 = ArticleId::generate();

        // Lexicographic sort should maintain chronological order
        $sorted = [$id1->value, $id2->value, $id3->value];
        sort($sorted);

        $this->assertEquals($id1->value, $sorted[0]);
        $this->assertEquals($id2->value, $sorted[1]);
        $this->assertEquals($id3->value, $sorted[2]);
    }

    // =========================================================================
    // Parsing Tests
    // =========================================================================

    public function testFromStringWithValidUlid(): void
    {
        $ulid = '01ARZ3NDEKTSV4RRFFQ69G5FAV';
        $id = ArticleId::fromString($ulid);

        $this->assertEquals($ulid, $id->value);
    }

    public function testFromStringWithLowercaseConvertsToUppercase(): void
    {
        $id = ArticleId::fromString('01arz3ndektsv4rrffq69g5fav');

        $this->assertEquals('01ARZ3NDEKTSV4RRFFQ69G5FAV', $id->value);
    }

    public function testFromStringRejectsInvalidLength(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ArticleId::fromString('TOOSHORT');
    }

    public function testFromStringRejectsInvalidCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // Contains 'I', 'L', 'O', 'U' which are not in Crockford Base32
        ArticleId::fromString('01ARZ3NDEKTSV4RRFFQILOU5FA');
    }

    // =========================================================================
    // Equality Tests
    // =========================================================================

    public function testEqualsWithSameValue(): void
    {
        $id1 = ArticleId::fromString('01ARZ3NDEKTSV4RRFFQ69G5FAV');
        $id2 = ArticleId::fromString('01ARZ3NDEKTSV4RRFFQ69G5FAV');

        $this->assertTrue($id1->equals($id2));
    }

    public function testEqualsWithDifferentValue(): void
    {
        $id1 = ArticleId::generate();
        $id2 = ArticleId::generate();

        $this->assertFalse($id1->equals($id2));
    }

    // =========================================================================
    // Timestamp Extraction Tests
    // =========================================================================

    public function testGetTimestampReturnsDateTimeImmutable(): void
    {
        $id = ArticleId::generate();
        $timestamp = $id->getTimestamp();

        $this->assertInstanceOf(\DateTimeImmutable::class, $timestamp);
    }

    public function testGetTimestampIsAccurate(): void
    {
        $before = new \DateTimeImmutable();
        $id = ArticleId::generate();
        $after = new \DateTimeImmutable();

        $timestamp = $id->getTimestamp();

        $this->assertGreaterThanOrEqual($before->getTimestamp(), $timestamp->getTimestamp());
        $this->assertLessThanOrEqual($after->getTimestamp(), $timestamp->getTimestamp());
    }

    // =========================================================================
    // String Conversion Tests
    // =========================================================================

    public function testToString(): void
    {
        $id = ArticleId::fromString('01ARZ3NDEKTSV4RRFFQ69G5FAV');

        $this->assertEquals('01ARZ3NDEKTSV4RRFFQ69G5FAV', $id->toString());
        $this->assertEquals('01ARZ3NDEKTSV4RRFFQ69G5FAV', (string) $id);
    }
}
