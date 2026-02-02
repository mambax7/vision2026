<?php

declare(strict_types=1);

namespace Vision2026\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use Vision2026\Domain\ValueObject\ArticleContent;

/**
 * Unit tests for the ArticleContent value object.
 *
 * Tests content validation, reading time calculation,
 * word count, and excerpt generation.
 */
final class ArticleContentTest extends TestCase
{
    // =========================================================================
    // Creation and Validation Tests
    // =========================================================================

    public function testCreateWithValidContent(): void
    {
        $content = ArticleContent::fromString(str_repeat('Word ', 50));

        $this->assertInstanceOf(ArticleContent::class, $content);
        $this->assertNotEmpty($content->value);
    }

    public function testTrimsWhitespace(): void
    {
        $content = ArticleContent::fromString("  Content with spaces  \n\n");

        $this->assertSame("Content with spaces", $content->value);
    }

    public function testRejectsEmptyContent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Article content cannot be empty');

        ArticleContent::fromString('');
    }

    public function testRejectsWhitespaceOnlyContent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Article content cannot be empty');

        ArticleContent::fromString("   \n\n\t  ");
    }

    public function testRejectsTooShortContent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Article content must be at least 50 characters');

        ArticleContent::fromString('Too short');
    }

    public function testAcceptsMinimumLengthContent(): void
    {
        $content = ArticleContent::fromString(str_repeat('A', 50));

        $this->assertEquals(50, strlen($content->value));
    }

    // =========================================================================
    // Word Count Tests
    // =========================================================================

    public function testWordCountForSimpleContent(): void
    {
        $content = ArticleContent::fromString(str_repeat('Word ', 10));

        $this->assertEquals(10, $content->wordCount());
    }

    public function testWordCountIgnoresMultipleSpaces(): void
    {
        $content = ArticleContent::fromString('One  two   three    four');

        $this->assertEquals(4, $content->wordCount());
    }

    public function testWordCountWithNewlines(): void
    {
        $content = ArticleContent::fromString("Line one\nLine two\nLine three test content words");

        $this->assertEquals(8, $content->wordCount());
    }

    public function testWordCountWithPunctuation(): void
    {
        $content = ArticleContent::fromString('Hello, world! How are you doing? This is a test. ' . str_repeat('Word ', 5));

        $this->assertEquals(15, $content->wordCount());
    }

    // =========================================================================
    // Reading Time Tests
    // =========================================================================

    public function testReadingTimeForShortContent(): void
    {
        // 50 words at 200 words/min = 0.25 minutes = 1 minute (rounded up)
        $content = ArticleContent::fromString(str_repeat('Word ', 50));

        $this->assertEquals(1, $content->readingTimeMinutes());
    }

    public function testReadingTimeForMediumContent(): void
    {
        // 400 words at 200 words/min = 2 minutes
        $content = ArticleContent::fromString(str_repeat('Word ', 400));

        $this->assertEquals(2, $content->readingTimeMinutes());
    }

    public function testReadingTimeForLongContent(): void
    {
        // 1000 words at 200 words/min = 5 minutes
        $content = ArticleContent::fromString(str_repeat('Word ', 1000));

        $this->assertEquals(5, $content->readingTimeMinutes());
    }

    public function testReadingTimeRoundsUp(): void
    {
        // 250 words at 200 words/min = 1.25 minutes = 2 minutes (rounded up)
        $content = ArticleContent::fromString(str_repeat('Word ', 250));

        $this->assertEquals(2, $content->readingTimeMinutes());
    }

    // =========================================================================
    // Excerpt Tests
    // =========================================================================

    public function testExcerptWithShortContent(): void
    {
        $shortText = str_repeat('Word ', 20);
        $content = ArticleContent::fromString($shortText);

        $excerpt = $content->excerpt(100);

        // Short content should be returned as-is without ellipsis
        $this->assertEquals(trim($shortText), $excerpt);
    }

    public function testExcerptTruncatesLongContent(): void
    {
        $longText = str_repeat('This is a very long sentence with many words. ', 10);
        $content = ArticleContent::fromString($longText);

        $excerpt = $content->excerpt(100);

        $this->assertLessThanOrEqual(104, strlen($excerpt)); // 100 + '...'
        $this->assertStringEndsWith('...', $excerpt);
    }

    public function testExcerptBreaksAtWordBoundary(): void
    {
        $content = ArticleContent::fromString('The quick brown fox jumps over the lazy dog. This is additional content that should be truncated.');

        $excerpt = $content->excerpt(30);

        // Should break at word boundary, not mid-word
        $this->assertStringNotContainsString('brow', $excerpt);
        $this->assertStringEndsWith('...', $excerpt);
    }

    public function testExcerptWithCustomLength(): void
    {
        $content = ArticleContent::fromString(str_repeat('Word ', 100));

        $shortExcerpt = $content->excerpt(50);
        $longExcerpt = $content->excerpt(200);

        $this->assertLessThan(strlen($longExcerpt), strlen($shortExcerpt));
    }

    public function testExcerptPreservesCompleteWords(): void
    {
        $content = ArticleContent::fromString('Hello world this is a test of the excerpt functionality with many words');

        $excerpt = $content->excerpt(20);

        // Should not cut words in half
        $words = explode(' ', rtrim($excerpt, '...'));
        foreach ($words as $word) {
            $this->assertNotEmpty(trim($word));
        }
    }

    // =========================================================================
    // Equality Tests
    // =========================================================================

    public function testEqualityWithSameContent(): void
    {
        $content1 = ArticleContent::fromString(str_repeat('Same content ', 10));
        $content2 = ArticleContent::fromString(str_repeat('Same content ', 10));

        $this->assertTrue($content1->equals($content2));
    }

    public function testEqualityWithDifferentContent(): void
    {
        $content1 = ArticleContent::fromString(str_repeat('Content one ', 10));
        $content2 = ArticleContent::fromString(str_repeat('Content two ', 10));

        $this->assertFalse($content1->equals($content2));
    }

    // =========================================================================
    // Edge Cases Tests
    // =========================================================================

    public function testContentWithUnicodeCharacters(): void
    {
        $content = ArticleContent::fromString('日本語のコンテンツです。' . str_repeat('これはテストです。', 10));

        $this->assertInstanceOf(ArticleContent::class, $content);
        $this->assertGreaterThan(0, $content->wordCount());
    }

    public function testContentWithHtmlEntities(): void
    {
        $content = ArticleContent::fromString('Content with &amp; &lt; &gt; &quot; entities. ' . str_repeat('Word ', 10));

        $this->assertInstanceOf(ArticleContent::class, $content);
        $this->assertStringContainsString('&amp;', $content->value);
    }

    public function testContentWithSpecialCharacters(): void
    {
        $content = ArticleContent::fromString('Special chars: @#$%^&*()_+-=[]{}|;:,.<>? ' . str_repeat('Word ', 10));

        $this->assertInstanceOf(ArticleContent::class, $content);
        $this->assertGreaterThan(10, $content->wordCount());
    }

    /**
     * @dataProvider validContentProvider
     */
    public function testAcceptsVariousValidContent(string $input, int $expectedMinWords): void
    {
        $content = ArticleContent::fromString($input);

        $this->assertInstanceOf(ArticleContent::class, $content);
        $this->assertGreaterThanOrEqual($expectedMinWords, $content->wordCount());
    }

    public static function validContentProvider(): array
    {
        return [
            'simple text' => [str_repeat('Word ', 50), 50],
            'with newlines' => [str_repeat("Line one\nLine two\n", 10), 40],
            'with punctuation' => [str_repeat('Hello, world! ', 20), 40],
            'mixed content' => ['This is a test.' . str_repeat(' More content here.', 15), 45],
        ];
    }
}
