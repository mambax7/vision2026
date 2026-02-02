<?php

declare(strict_types=1);

namespace Vision2026\Tests\Unit\Infrastructure\Persistence;

use PHPUnit\Framework\TestCase;
use Vision2026\Infrastructure\Persistence\ArticleMapper;
use Vision2026\Domain\Entity\Article;
use Vision2026\Domain\Entity\ArticleStatus;
use Vision2026\Domain\ValueObject\ArticleId;
use Vision2026\Domain\ValueObject\ArticleTitle;
use Vision2026\Domain\ValueObject\ArticleContent;
use Vision2026\Domain\ValueObject\ArticleSlug;
use Vision2026\Domain\ValueObject\AuthorId;

/**
 * Unit tests for the ArticleMapper.
 *
 * Tests bidirectional mapping between database rows
 * and Article entities (row ↔ entity).
 */
final class ArticleMapperTest extends TestCase
{
    private ArticleMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new ArticleMapper();
    }

    // =========================================================================
    // Entity to Row Mapping Tests
    // =========================================================================

    public function testToRowMapsDraftArticle(): void
    {
        // Arrange
        $article = Article::create(
            id: ArticleId::fromString('01HQZX123456789ABCDEFGHIJK'),
            title: ArticleTitle::fromString('Test Article'),
            content: ArticleContent::fromString(str_repeat('Content here. ', 10)),
            authorId: AuthorId::fromInt(42)
        );

        // Act
        $row = $this->mapper->toRow($article);

        // Assert
        $this->assertEquals('01HQZX123456789ABCDEFGHIJK', $row['id']);
        $this->assertEquals('Test Article', $row['title']);
        $this->assertEquals('test-article', $row['slug']);
        $this->assertEquals('draft', $row['status']);
        $this->assertEquals(42, $row['author_id']);
        $this->assertNull($row['published_at']);
        $this->assertIsInt($row['created_at']);
        $this->assertIsInt($row['updated_at']);
    }

    public function testToRowMapsPublishedArticle(): void
    {
        // Arrange
        $article = Article::create(
            id: ArticleId::generate(),
            title: ArticleTitle::fromString('Published Article'),
            content: ArticleContent::fromString(str_repeat('Content. ', 10)),
            authorId: AuthorId::fromInt(1)
        );
        $article->publish();

        // Act
        $row = $this->mapper->toRow($article);

        // Assert
        $this->assertEquals('published', $row['status']);
        $this->assertIsInt($row['published_at']);
        $this->assertGreaterThan(0, $row['published_at']);
    }

    public function testToRowMapsArchivedArticle(): void
    {
        // Arrange
        $article = Article::create(
            id: ArticleId::generate(),
            title: ArticleTitle::fromString('Archived Article'),
            content: ArticleContent::fromString(str_repeat('Content. ', 10)),
            authorId: AuthorId::fromInt(1)
        );
        $article->archive();

        // Act
        $row = $this->mapper->toRow($article);

        // Assert
        $this->assertEquals('archived', $row['status']);
    }

    public function testToRowIncludesAllRequiredFields(): void
    {
        // Arrange
        $article = Article::create(
            id: ArticleId::generate(),
            title: ArticleTitle::fromString('Complete Article'),
            content: ArticleContent::fromString(str_repeat('Full content. ', 20)),
            authorId: AuthorId::fromInt(99)
        );

        // Act
        $row = $this->mapper->toRow($article);

        // Assert
        $this->assertArrayHasKey('id', $row);
        $this->assertArrayHasKey('title', $row);
        $this->assertArrayHasKey('slug', $row);
        $this->assertArrayHasKey('content', $row);
        $this->assertArrayHasKey('status', $row);
        $this->assertArrayHasKey('author_id', $row);
        $this->assertArrayHasKey('created_at', $row);
        $this->assertArrayHasKey('updated_at', $row);
        $this->assertArrayHasKey('published_at', $row);
    }

    public function testToRowConvertsTimestampsToUnix(): void
    {
        // Arrange
        $article = Article::create(
            id: ArticleId::generate(),
            title: ArticleTitle::fromString('Test'),
            content: ArticleContent::fromString(str_repeat('Content. ', 10)),
            authorId: AuthorId::fromInt(1)
        );

        // Act
        $row = $this->mapper->toRow($article);

        // Assert
        $this->assertIsInt($row['created_at']);
        $this->assertGreaterThan(0, $row['created_at']);
        $this->assertLessThanOrEqual(time(), $row['created_at']);
    }

    // =========================================================================
    // Row to Entity Mapping Tests
    // =========================================================================

    public function testFromRowCreatesDraftArticle(): void
    {
        // Arrange
        $now = time();
        $row = [
            'id' => '01HQZX123456789ABCDEFGHIJK',
            'title' => 'Database Article',
            'slug' => 'database-article',
            'content' => str_repeat('Content from DB. ', 10),
            'status' => 'draft',
            'author_id' => 55,
            'created_at' => $now,
            'updated_at' => $now,
            'published_at' => null,
        ];

        // Act
        $article = $this->mapper->fromRow($row);

        // Assert
        $this->assertEquals('Database Article', $article->getTitle()->value);
        $this->assertEquals('database-article', $article->getSlug()->value);
        $this->assertTrue($article->isDraft());
        $this->assertFalse($article->isPublished());
        $this->assertTrue($article->isOwnedBy(AuthorId::fromInt(55)));
    }

    public function testFromRowCreatesPublishedArticle(): void
    {
        // Arrange
        $now = time();
        $row = [
            'id' => ArticleId::generate()->toString(),
            'title' => 'Published DB Article',
            'slug' => 'published-db-article',
            'content' => str_repeat('Published content. ', 10),
            'status' => 'published',
            'author_id' => 1,
            'created_at' => $now - 3600,
            'updated_at' => $now,
            'published_at' => $now,
        ];

        // Act
        $article = $this->mapper->fromRow($row);

        // Assert
        $this->assertTrue($article->isPublished());
        $this->assertFalse($article->isDraft());
        $this->assertNotNull($article->getPublishedAt());
    }

    public function testFromRowCreatesArchivedArticle(): void
    {
        // Arrange
        $now = time();
        $row = [
            'id' => ArticleId::generate()->toString(),
            'title' => 'Archived DB Article',
            'slug' => 'archived-db-article',
            'content' => str_repeat('Archived content. ', 10),
            'status' => 'archived',
            'author_id' => 1,
            'created_at' => $now,
            'updated_at' => $now,
            'published_at' => null,
        ];

        // Act
        $article = $this->mapper->fromRow($row);

        // Assert
        $this->assertTrue($article->isArchived());
        $this->assertFalse($article->isDraft());
        $this->assertFalse($article->isPublished());
    }

    public function testFromRowPreservesArticleId(): void
    {
        // Arrange
        $articleId = '01HQZX987654321ZYXWVUTSRQP';
        $row = [
            'id' => $articleId,
            'title' => 'Test',
            'slug' => 'test',
            'content' => str_repeat('Content. ', 10),
            'status' => 'draft',
            'author_id' => 1,
            'created_at' => time(),
            'updated_at' => time(),
            'published_at' => null,
        ];

        // Act
        $article = $this->mapper->fromRow($row);

        // Assert
        $this->assertEquals($articleId, $article->id->toString());
    }

    public function testFromRowConvertsUnixTimestamps(): void
    {
        // Arrange
        $createdAt = strtotime('2024-01-15 10:00:00');
        $updatedAt = strtotime('2024-01-16 14:30:00');

        $row = [
            'id' => ArticleId::generate()->toString(),
            'title' => 'Test',
            'slug' => 'test',
            'content' => str_repeat('Content. ', 10),
            'status' => 'draft',
            'author_id' => 1,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'published_at' => null,
        ];

        // Act
        $article = $this->mapper->fromRow($row);

        // Assert
        $this->assertEquals($createdAt, $article->getCreatedAt()->getTimestamp());
        $this->assertEquals($updatedAt, $article->getUpdatedAt()->getTimestamp());
    }

    // =========================================================================
    // Round-trip Tests
    // =========================================================================

    public function testRoundTripPreservesArticleData(): void
    {
        // Arrange
        $originalArticle = Article::create(
            id: ArticleId::fromString('01HQZX123456789ABCDEFGHIJK'),
            title: ArticleTitle::fromString('Round Trip Test'),
            content: ArticleContent::fromString(str_repeat('Test content. ', 15)),
            authorId: AuthorId::fromInt(77)
        );

        // Act
        $row = $this->mapper->toRow($originalArticle);
        $restoredArticle = $this->mapper->fromRow($row);

        // Assert
        $this->assertEquals($originalArticle->id->toString(), $restoredArticle->id->toString());
        $this->assertEquals($originalArticle->getTitle()->value, $restoredArticle->getTitle()->value);
        $this->assertEquals($originalArticle->getSlug()->value, $restoredArticle->getSlug()->value);
        $this->assertEquals($originalArticle->getContent()->value, $restoredArticle->getContent()->value);
        $this->assertEquals($originalArticle->getStatus(), $restoredArticle->getStatus());
    }

    public function testRoundTripWithPublishedArticle(): void
    {
        // Arrange
        $original = Article::create(
            id: ArticleId::generate(),
            title: ArticleTitle::fromString('Published Test'),
            content: ArticleContent::fromString(str_repeat('Content. ', 10)),
            authorId: AuthorId::fromInt(1)
        );
        $original->publish();

        // Act
        $row = $this->mapper->toRow($original);
        $restored = $this->mapper->fromRow($row);

        // Assert
        $this->assertTrue($restored->isPublished());
        $this->assertNotNull($restored->getPublishedAt());
        $this->assertEquals(
            $original->getPublishedAt()->getTimestamp(),
            $restored->getPublishedAt()->getTimestamp()
        );
    }

    public function testRoundTripPreservesAuthorId(): void
    {
        // Arrange
        $authorId = AuthorId::fromInt(999);
        $original = Article::create(
            id: ArticleId::generate(),
            title: ArticleTitle::fromString('Author Test'),
            content: ArticleContent::fromString(str_repeat('Content. ', 10)),
            authorId: $authorId
        );

        // Act
        $row = $this->mapper->toRow($original);
        $restored = $this->mapper->fromRow($row);

        // Assert
        $this->assertTrue($restored->isOwnedBy($authorId));
    }

    // =========================================================================
    // Edge Cases Tests
    // =========================================================================

    public function testFromRowHandlesNullPublishedAt(): void
    {
        // Arrange
        $row = [
            'id' => ArticleId::generate()->toString(),
            'title' => 'Test',
            'slug' => 'test',
            'content' => str_repeat('Content. ', 10),
            'status' => 'draft',
            'author_id' => 1,
            'created_at' => time(),
            'updated_at' => time(),
            'published_at' => null,
        ];

        // Act
        $article = $this->mapper->fromRow($row);

        // Assert
        $this->assertNull($article->getPublishedAt());
    }

    public function testToRowHandlesLongContent(): void
    {
        // Arrange
        $longContent = str_repeat('Word ', 1000);
        $article = Article::create(
            id: ArticleId::generate(),
            title: ArticleTitle::fromString('Long Content Article'),
            content: ArticleContent::fromString($longContent),
            authorId: AuthorId::fromInt(1)
        );

        // Act
        $row = $this->mapper->toRow($article);

        // Assert
        $this->assertEquals(trim($longContent), $row['content']);
    }

    public function testFromRowHandlesSpecialCharactersInContent(): void
    {
        // Arrange
        $specialContent = "Content with 'quotes' and \"double quotes\" and special chars: <>&";
        $row = [
            'id' => ArticleId::generate()->toString(),
            'title' => 'Special Chars',
            'slug' => 'special-chars',
            'content' => str_repeat($specialContent . ' ', 5),
            'status' => 'draft',
            'author_id' => 1,
            'created_at' => time(),
            'updated_at' => time(),
            'published_at' => null,
        ];

        // Act
        $article = $this->mapper->fromRow($row);

        // Assert
        $this->assertStringContainsString("'quotes'", $article->getContent()->value);
        $this->assertStringContainsString('"double quotes"', $article->getContent()->value);
    }

    /**
     * @dataProvider statusProvider
     */
    public function testMapsAllArticleStatuses(string $status, ArticleStatus $expectedStatus): void
    {
        // Arrange
        $row = [
            'id' => ArticleId::generate()->toString(),
            'title' => 'Test',
            'slug' => 'test',
            'content' => str_repeat('Content. ', 10),
            'status' => $status,
            'author_id' => 1,
            'created_at' => time(),
            'updated_at' => time(),
            'published_at' => $status === 'published' ? time() : null,
        ];

        // Act
        $article = $this->mapper->fromRow($row);

        // Assert
        $this->assertEquals($expectedStatus, $article->getStatus());
    }

    public static function statusProvider(): array
    {
        return [
            'draft' => ['draft', ArticleStatus::Draft],
            'published' => ['published', ArticleStatus::Published],
            'archived' => ['archived', ArticleStatus::Archived],
        ];
    }
}
