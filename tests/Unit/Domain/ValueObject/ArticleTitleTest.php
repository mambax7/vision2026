<?php

declare(strict_types=1);

namespace Vision2026\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use Vision2026\Domain\ValueObject\ArticleTitle;
use InvalidArgumentException;

/**
 * Unit tests for the ArticleTitle value object.
 *
 * Value objects are perfect for unit testing because they're:
 * - Immutable (no side effects)
 * - Self-validating (enforce invariants)
 * - Pure (same input = same output)
 */
final class ArticleTitleTest extends TestCase
{
    // =========================================================================
    // Valid Title Tests
    // =========================================================================

    public function testCreateWithValidTitle(): void
    {
        $title = ArticleTitle::fromString('Hello World');

        $this->assertEquals('Hello World', $title->value);
    }

    public function testTitleIsTrimmed(): void
    {
        $title = ArticleTitle::fromString('  Hello World  ');

        $this->assertEquals('Hello World', $title->value);
    }

    public function testMultipleSpacesNormalized(): void
    {
        $title = ArticleTitle::fromString('Hello    World');

        $this->assertEquals('Hello World', $title->value);
    }

    public function testUnicodeSupport(): void
    {
        $title = ArticleTitle::fromString('日本語タイトル');

        $this->assertEquals('日本語タイトル', $title->value);
    }

    public function testEmojiSupport(): void
    {
        $title = ArticleTitle::fromString('🚀 Launch Day!');

        $this->assertEquals('🚀 Launch Day!', $title->value);
    }

    // =========================================================================
    // Validation Tests
    // =========================================================================

    public function testRejectsTooShortTitle(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('at least ' . ArticleTitle::MIN_LENGTH);

        ArticleTitle::fromString('Hi');
    }

    public function testRejectsTooLongTitle(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exceed ' . ArticleTitle::MAX_LENGTH);

        ArticleTitle::fromString(str_repeat('a', ArticleTitle::MAX_LENGTH + 1));
    }

    public function testAcceptsExactMinLength(): void
    {
        $title = ArticleTitle::fromString(str_repeat('a', ArticleTitle::MIN_LENGTH));

        $this->assertEquals(ArticleTitle::MIN_LENGTH, $title->length());
    }

    public function testAcceptsExactMaxLength(): void
    {
        $title = ArticleTitle::fromString(str_repeat('a', ArticleTitle::MAX_LENGTH));

        $this->assertEquals(ArticleTitle::MAX_LENGTH, $title->length());
    }

    // =========================================================================
    // Equality Tests
    // =========================================================================

    public function testEqualityWithSameValue(): void
    {
        $title1 = ArticleTitle::fromString('Hello');
        $title2 = ArticleTitle::fromString('Hello');

        $this->assertTrue($title1->equals($title2));
    }

    public function testInequalityWithDifferentValue(): void
    {
        $title1 = ArticleTitle::fromString('Hello');
        $title2 = ArticleTitle::fromString('World');

        $this->assertFalse($title1->equals($title2));
    }

    // =========================================================================
    // Behavior Tests
    // =========================================================================

    public function testLengthCalculation(): void
    {
        $title = ArticleTitle::fromString('Hello');

        $this->assertEquals(5, $title->length());
    }

    public function testUnicodeLengthCalculation(): void
    {
        $title = ArticleTitle::fromString('日本語');

        $this->assertEquals(3, $title->length()); // 3 characters, not 9 bytes
    }

    public function testTruncateShortTitle(): void
    {
        $title = ArticleTitle::fromString('Short');

        $this->assertEquals('Short', $title->truncate(50));
    }

    public function testTruncateLongTitle(): void
    {
        $title = ArticleTitle::fromString('This is a very long title that needs to be truncated');

        $truncated = $title->truncate(20);

        $this->assertLessThanOrEqual(20, strlen($truncated));
        $this->assertStringEndsWith('...', $truncated);
    }

    public function testToSlugBasic(): void
    {
        $title = ArticleTitle::fromString('Hello World');

        $this->assertEquals('hello-world', $title->toSlug());
    }

    public function testToSlugWithSpecialCharacters(): void
    {
        $title = ArticleTitle::fromString("What's New in PHP 8.2?");

        $slug = $title->toSlug();

        $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/', $slug);
    }

    public function testToStringReturnsValue(): void
    {
        $title = ArticleTitle::fromString('Test Title');

        $this->assertEquals('Test Title', (string) $title);
    }
}
