<?php

declare(strict_types=1);

namespace Vision2026\Tests\Unit\Domain\Entity;

use PHPUnit\Framework\TestCase;
use Vision2026\Domain\Entity\Article;
use Vision2026\Domain\Entity\ArticleStatus;
use Vision2026\Domain\ValueObject\ArticleId;
use Vision2026\Domain\ValueObject\ArticleTitle;
use Vision2026\Domain\ValueObject\ArticleContent;
use Vision2026\Domain\ValueObject\AuthorId;
use Vision2026\Domain\Event\ArticleCreated;
use Vision2026\Domain\Event\ArticlePublished;
use Vision2026\Domain\Exception\InvalidStatusTransition;

/**
 * Unit tests for the Article entity.
 *
 * NOTICE: These tests require NO database, NO XOOPS, NO framework.
 * This is the power of Clean Architecture - pure domain logic
 * can be tested in complete isolation.
 *
 * Run with: vendor/bin/phpunit tests/Unit/Domain/Entity/ArticleTest.php
 */
final class ArticleTest extends TestCase
{
    // =========================================================================
    // Test Data Helpers
    // =========================================================================

    private function createArticle(): Article
    {
        return Article::create(
            id: ArticleId::generate(),
            title: ArticleTitle::fromString('Test Article Title'),
            content: ArticleContent::fromString(str_repeat('This is test content. ', 10)),
            authorId: AuthorId::fromInt(1),
        );
    }

    // =========================================================================
    // Creation Tests
    // =========================================================================

    public function testCreateArticleWithValidData(): void
    {
        $id = ArticleId::generate();
        $title = ArticleTitle::fromString('My First Article');
        $content = ArticleContent::fromString(str_repeat('Lorem ipsum dolor sit amet. ', 5));
        $authorId = AuthorId::fromInt(42);

        $article = Article::create($id, $title, $content, $authorId);

        $this->assertTrue($article->id->equals($id));
        $this->assertEquals('My First Article', $article->getTitle()->value);
        $this->assertEquals(ArticleStatus::Draft, $article->getStatus());
        $this->assertTrue($article->isDraft());
        $this->assertFalse($article->isPublished());
    }

    public function testNewArticleIsDraft(): void
    {
        $article = $this->createArticle();

        $this->assertTrue($article->isDraft());
        $this->assertFalse($article->isPublished());
        $this->assertFalse($article->isArchived());
        $this->assertTrue($article->isEditable());
    }

    public function testNewArticleRecordsCreatedEvent(): void
    {
        $article = $this->createArticle();

        $events = $article->pullDomainEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(ArticleCreated::class, $events[0]);
    }

    public function testPullDomainEventsClearsEventList(): void
    {
        $article = $this->createArticle();

        $firstPull = $article->pullDomainEvents();
        $secondPull = $article->pullDomainEvents();

        $this->assertCount(1, $firstPull);
        $this->assertCount(0, $secondPull);
    }

    // =========================================================================
    // Status Transition Tests
    // =========================================================================

    public function testPublishDraftArticle(): void
    {
        $article = $this->createArticle();
        $this->assertTrue($article->canPublish());

        $article->publish();

        $this->assertTrue($article->isPublished());
        $this->assertFalse($article->isDraft());
        $this->assertNotNull($article->getPublishedAt());
    }

    public function testPublishRecordsPublishedEvent(): void
    {
        $article = $this->createArticle();
        $article->pullDomainEvents(); // Clear creation event

        $article->publish();
        $events = $article->pullDomainEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(ArticlePublished::class, $events[0]);
    }

    public function testCannotPublishAlreadyPublishedArticle(): void
    {
        $article = $this->createArticle();
        $article->publish();

        $this->expectException(InvalidStatusTransition::class);
        $article->publish();
    }

    public function testCannotPublishArchivedArticle(): void
    {
        $article = $this->createArticle();
        $article->archive();

        $this->assertFalse($article->canPublish());
        $this->expectException(InvalidStatusTransition::class);
        $article->publish();
    }

    public function testArchiveDraftArticle(): void
    {
        $article = $this->createArticle();

        $article->archive();

        $this->assertTrue($article->isArchived());
        $this->assertFalse($article->isDraft());
    }

    public function testArchivePublishedArticle(): void
    {
        $article = $this->createArticle();
        $article->publish();

        $article->archive();

        $this->assertTrue($article->isArchived());
        $this->assertFalse($article->isPublished());
    }

    public function testCannotArchiveAlreadyArchivedArticle(): void
    {
        $article = $this->createArticle();
        $article->archive();

        $this->expectException(InvalidStatusTransition::class);
        $article->archive();
    }

    // =========================================================================
    // Update Tests
    // =========================================================================

    public function testUpdateDraftArticle(): void
    {
        $article = $this->createArticle();
        $newTitle = ArticleTitle::fromString('Updated Title');
        $newContent = ArticleContent::fromString(str_repeat('Updated content here. ', 10));

        $article->update($newTitle, $newContent);

        $this->assertEquals('Updated Title', $article->getTitle()->value);
        $this->assertNotNull($article->getUpdatedAt());
    }

    public function testCannotUpdatePublishedArticle(): void
    {
        $article = $this->createArticle();
        $article->publish();

        $this->expectException(InvalidStatusTransition::class);
        $article->update(
            ArticleTitle::fromString('New Title'),
            ArticleContent::fromString(str_repeat('New content. ', 10)),
        );
    }

    // =========================================================================
    // Ownership Tests
    // =========================================================================

    public function testArticleOwnership(): void
    {
        $authorId = AuthorId::fromInt(42);
        $article = Article::create(
            ArticleId::generate(),
            ArticleTitle::fromString('Test'),
            ArticleContent::fromString(str_repeat('Content ', 10)),
            $authorId,
        );

        $this->assertTrue($article->isOwnedBy(AuthorId::fromInt(42)));
        $this->assertFalse($article->isOwnedBy(AuthorId::fromInt(99)));
    }

    // =========================================================================
    // State Machine Tests
    // =========================================================================

    public function testAllowedTransitionsFromDraft(): void
    {
        $status = ArticleStatus::Draft;

        $this->assertTrue($status->canTransitionTo(ArticleStatus::Published));
        $this->assertTrue($status->canTransitionTo(ArticleStatus::Archived));
        $this->assertFalse($status->canTransitionTo(ArticleStatus::Draft));
    }

    public function testAllowedTransitionsFromPublished(): void
    {
        $status = ArticleStatus::Published;

        $this->assertFalse($status->canTransitionTo(ArticleStatus::Draft));
        $this->assertFalse($status->canTransitionTo(ArticleStatus::Published));
        $this->assertTrue($status->canTransitionTo(ArticleStatus::Archived));
    }

    public function testAllowedTransitionsFromArchived(): void
    {
        $status = ArticleStatus::Archived;

        $this->assertFalse($status->canTransitionTo(ArticleStatus::Draft));
        $this->assertFalse($status->canTransitionTo(ArticleStatus::Published));
        $this->assertFalse($status->canTransitionTo(ArticleStatus::Archived));
    }
}
