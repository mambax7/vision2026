<?php

declare(strict_types=1);

namespace Vision2026\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use Vision2026\Domain\ValueObject\ArticleSlug;
use Vision2026\Domain\ValueObject\ArticleTitle;

/**
 * Unit tests for the ArticleSlug value object.
 *
 * Tests slug generation from titles, validation rules,
 * and URL-safe formatting.
 */
final class ArticleSlugTest extends TestCase
{
    // =========================================================================
    // Generation from Title Tests
    // =========================================================================

    public function testGenerateFromSimpleTitle(): void
    {
        $title = ArticleTitle::fromString('Hello World');
        $slug = ArticleSlug::fromTitle($title);

        $this->assertEquals('hello-world', $slug->value);
    }

    public function testGenerateFromTitleWithSpaces(): void
    {
        $title = ArticleTitle::fromString('This Is A Test Article');
        $slug = ArticleSlug::fromTitle($title);

        $this->assertEquals('this-is-a-test-article', $slug->value);
    }

    public function testGenerateFromTitleWithSpecialCharacters(): void
    {
        $title = ArticleTitle::fromString('Hello, World! How Are You?');
        $slug = ArticleSlug::fromTitle($title);

        $this->assertEquals('hello-world-how-are-you', $slug->value);
    }

    public function testGenerateFromTitleWithMultipleSpaces(): void
    {
        $title = ArticleTitle::fromString('Hello    World   Test');
        $slug = ArticleSlug::fromTitle($title);

        $this->assertEquals('hello-world-test', $slug->value);
    }

    public function testGenerateFromTitleWithHyphens(): void
    {
        $title = ArticleTitle::fromString('Pre-existing-hyphens Here');
        $slug = ArticleSlug::fromTitle($title);

        $this->assertEquals('pre-existing-hyphens-here', $slug->value);
    }

    public function testGenerateFromTitleWithNumbers(): void
    {
        $title = ArticleTitle::fromString('Article 123 Test 456');
        $slug = ArticleSlug::fromTitle($title);

        $this->assertEquals('article-123-test-456', $slug->value);
    }

    // =========================================================================
    // Direct Creation Tests
    // =========================================================================

    public function testCreateFromValidString(): void
    {
        $slug = ArticleSlug::fromString('valid-slug-here');

        $this->assertEquals('valid-slug-here', $slug->value);
    }

    public function testCreateFromStringWithNumbers(): void
    {
        $slug = ArticleSlug::fromString('article-123-test');

        $this->assertEquals('article-123-test', $slug->value);
    }

    public function testRejectsEmptySlug(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Article slug cannot be empty');

        ArticleSlug::fromString('');
    }

    public function testRejectsWhitespaceOnlySlug(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Article slug cannot be empty');

        ArticleSlug::fromString('   ');
    }

    public function testRejectsTooShortSlug(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Article slug must be at least 3 characters');

        ArticleSlug::fromString('ab');
    }

    public function testRejectsTooLongSlug(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Article slug cannot exceed 255 characters');

        ArticleSlug::fromString(str_repeat('a', 256));
    }

    public function testRejectsSlugWithUppercase(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Article slug must contain only lowercase letters, numbers, and hyphens');

        ArticleSlug::fromString('Invalid-Uppercase');
    }

    public function testRejectsSlugWithSpaces(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Article slug must contain only lowercase letters, numbers, and hyphens');

        ArticleSlug::fromString('invalid slug');
    }

    public function testRejectsSlugWithSpecialCharacters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Article slug must contain only lowercase letters, numbers, and hyphens');

        ArticleSlug::fromString('invalid@slug');
    }

    public function testRejectsSlugWithUnderscores(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Article slug must contain only lowercase letters, numbers, and hyphens');

        ArticleSlug::fromString('invalid_slug');
    }

    public function testRejectsSlugStartingWithHyphen(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Article slug cannot start or end with a hyphen');

        ArticleSlug::fromString('-invalid-slug');
    }

    public function testRejectsSlugEndingWithHyphen(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Article slug cannot start or end with a hyphen');

        ArticleSlug::fromString('invalid-slug-');
    }

    public function testRejectsSlugWithConsecutiveHyphens(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Article slug cannot contain consecutive hyphens');

        ArticleSlug::fromString('invalid--slug');
    }

    // =========================================================================
    // Minimum Length Tests
    // =========================================================================

    public function testAcceptsMinimumLengthSlug(): void
    {
        $slug = ArticleSlug::fromString('abc');

        $this->assertEquals('abc', $slug->value);
    }

    public function testAcceptsMaximumLengthSlug(): void
    {
        $longSlug = str_repeat('a', 255);
        $slug = ArticleSlug::fromString($longSlug);

        $this->assertEquals($longSlug, $slug->value);
    }

    // =========================================================================
    // Unicode and Special Cases Tests
    // =========================================================================

    public function testGenerateFromTitleWithUnicodeCharacters(): void
    {
        $title = ArticleTitle::fromString('Café Über München');
        $slug = ArticleSlug::fromTitle($title);

        // Unicode should be transliterated or removed
        $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/', $slug->value);
    }

    public function testGenerateFromTitleWithAmpersand(): void
    {
        $title = ArticleTitle::fromString('Cats & Dogs');
        $slug = ArticleSlug::fromTitle($title);

        $this->assertEquals('cats-dogs', $slug->value);
    }

    public function testGenerateFromTitleWithApostrophe(): void
    {
        $title = ArticleTitle::fromString("It's a Beautiful Day");
        $slug = ArticleSlug::fromTitle($title);

        $this->assertEquals('its-a-beautiful-day', $slug->value);
    }

    public function testGenerateFromTitleWithQuotes(): void
    {
        $title = ArticleTitle::fromString('"Quoted Title" Here');
        $slug = ArticleSlug::fromTitle($title);

        $this->assertEquals('quoted-title-here', $slug->value);
    }

    // =========================================================================
    // Equality Tests
    // =========================================================================

    public function testEqualityWithSameSlug(): void
    {
        $slug1 = ArticleSlug::fromString('same-slug');
        $slug2 = ArticleSlug::fromString('same-slug');

        $this->assertTrue($slug1->equals($slug2));
    }

    public function testEqualityWithDifferentSlugs(): void
    {
        $slug1 = ArticleSlug::fromString('slug-one');
        $slug2 = ArticleSlug::fromString('slug-two');

        $this->assertFalse($slug1->equals($slug2));
    }

    // =========================================================================
    // URL Safety Tests
    // =========================================================================

    public function testSlugIsUrlSafe(): void
    {
        $title = ArticleTitle::fromString('Test Article: How to Use XOOPS 2026!');
        $slug = ArticleSlug::fromTitle($title);

        // Should be safe to use in URLs without encoding
        $this->assertEquals($slug->value, urldecode($slug->value));
    }

    public function testSlugCanBeUsedInUrls(): void
    {
        $slug = ArticleSlug::fromString('my-test-article-123');

        $url = "http://example.com/articles/{$slug->value}";

        $this->assertStringContainsString('my-test-article-123', $url);
        $this->assertStringNotContainsString(' ', $url);
    }

    // =========================================================================
    // Data Provider Tests
    // =========================================================================

    /**
     * @dataProvider validSlugProvider
     */
    public function testAcceptsValidSlugs(string $input): void
    {
        $slug = ArticleSlug::fromString($input);

        $this->assertEquals($input, $slug->value);
    }

    public static function validSlugProvider(): array
    {
        return [
            'simple' => ['hello'],
            'with-hyphen' => ['hello-world'],
            'with-numbers' => ['article-123'],
            'long-slug' => ['this-is-a-very-long-slug-with-many-words'],
            'numbers-only' => ['12345'],
            'mixed' => ['test-123-article'],
            'minimum-length' => ['abc'],
        ];
    }

    /**
     * @dataProvider invalidSlugProvider
     */
    public function testRejectsInvalidSlugs(string $input): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ArticleSlug::fromString($input);
    }

    public static function invalidSlugProvider(): array
    {
        return [
            'uppercase' => ['Hello'],
            'with-space' => ['hello world'],
            'with-underscore' => ['hello_world'],
            'with-special-char' => ['hello@world'],
            'starting-hyphen' => ['-hello'],
            'ending-hyphen' => ['hello-'],
            'double-hyphen' => ['hello--world'],
            'too-short' => ['ab'],
            'empty' => [''],
        ];
    }
}
