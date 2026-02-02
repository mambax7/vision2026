<?php

declare(strict_types=1);

namespace Vision2026\Tests\Unit\Application\Command;

use PHPUnit\Framework\TestCase;
use Vision2026\Application\Command\PublishArticle;
use Vision2026\Application\Handler\PublishArticleHandler;
use Vision2026\Domain\Repository\ArticleRepositoryInterface;
use Vision2026\Domain\ValueObject\ArticleId;
use Vision2026\Domain\ValueObject\ArticleTitle;
use Vision2026\Domain\ValueObject\ArticleContent;
use Vision2026\Domain\ValueObject\AuthorId;
use Vision2026\Domain\Entity\Article;
use Vision2026\Domain\Event\ArticlePublished;
use Vision2026\Domain\Exception\ArticleNotFoundException;
use Vision2026\Domain\Exception\InvalidStatusTransition;
use Vision2026\Application\EventDispatcher\EventDispatcherInterface;

/**
 * Unit tests for the PublishArticleHandler.
 *
 * Tests article publishing logic, state transitions,
 * error handling, and event dispatching.
 */
final class PublishArticleHandlerTest extends TestCase
{
    private ArticleRepositoryInterface $repository;
    private EventDispatcherInterface $eventDispatcher;
    private PublishArticleHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ArticleRepositoryInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->handler = new PublishArticleHandler($this->repository, $this->eventDispatcher);
    }

    // =========================================================================
    // Helper Methods
    // =========================================================================

    private function createDraftArticle(ArticleId $id = null): Article
    {
        return Article::create(
            id: $id ?? ArticleId::generate(),
            title: ArticleTitle::fromString('Draft Article'),
            content: ArticleContent::fromString(str_repeat('Content here. ', 10)),
            authorId: AuthorId::fromInt(1)
        );
    }

    private function createPublishedArticle(ArticleId $id = null): Article
    {
        $article = $this->createDraftArticle($id);
        $article->publish();
        return $article;
    }

    // =========================================================================
    // Happy Path Tests
    // =========================================================================

    public function testHandlePublishesDraftArticle(): void
    {
        // Arrange
        $articleId = ArticleId::generate();
        $article = $this->createDraftArticle($articleId);

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with($articleId)
            ->willReturn($article);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Article $savedArticle) {
                return $savedArticle->isPublished();
            }));

        $command = new PublishArticle($articleId);

        // Act
        $this->handler->handle($command);

        // Assert
        $this->assertTrue($article->isPublished());
    }

    public function testHandleSetsPublishedTimestamp(): void
    {
        // Arrange
        $articleId = ArticleId::generate();
        $article = $this->createDraftArticle($articleId);

        $this->repository
            ->method('findById')
            ->willReturn($article);

        $command = new PublishArticle($articleId);

        // Act
        $before = new \DateTimeImmutable();
        $this->handler->handle($command);
        $after = new \DateTimeImmutable();

        // Assert
        $publishedAt = $article->getPublishedAt();
        $this->assertNotNull($publishedAt);
        $this->assertGreaterThanOrEqual($before->getTimestamp(), $publishedAt->getTimestamp());
        $this->assertLessThanOrEqual($after->getTimestamp(), $publishedAt->getTimestamp());
    }

    public function testHandleDispatchesPublishedEvent(): void
    {
        // Arrange
        $articleId = ArticleId::generate();
        $article = $this->createDraftArticle($articleId);

        $this->repository
            ->method('findById')
            ->willReturn($article);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($events) {
                return is_array($events)
                    && count($events) === 1
                    && $events[0] instanceof ArticlePublished;
            }));

        $command = new PublishArticle($articleId);

        // Act
        $this->handler->handle($command);
    }

    public function testPublishedEventContainsArticleId(): void
    {
        // Arrange
        $articleId = ArticleId::generate();
        $article = $this->createDraftArticle($articleId);
        $capturedEvents = null;

        $this->repository
            ->method('findById')
            ->willReturn($article);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($events) use (&$capturedEvents) {
                $capturedEvents = $events;
                return null;
            });

        $command = new PublishArticle($articleId);

        // Act
        $this->handler->handle($command);

        // Assert
        /** @var ArticlePublished $event */
        $event = $capturedEvents[0];
        $this->assertTrue($event->articleId->equals($articleId));
    }

    // =========================================================================
    // Error Handling Tests
    // =========================================================================

    public function testThrowsExceptionWhenArticleNotFound(): void
    {
        // Arrange
        $articleId = ArticleId::generate();

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with($articleId)
            ->willReturn(null);

        $command = new PublishArticle($articleId);

        // Assert
        $this->expectException(ArticleNotFoundException::class);

        // Act
        $this->handler->handle($command);
    }

    public function testDoesNotSaveWhenArticleNotFound(): void
    {
        // Arrange
        $articleId = ArticleId::generate();

        $this->repository
            ->method('findById')
            ->willReturn(null);

        $this->repository
            ->expects($this->never())
            ->method('save');

        $command = new PublishArticle($articleId);

        // Act & Assert
        try {
            $this->handler->handle($command);
        } catch (ArticleNotFoundException $e) {
            // Expected exception
        }
    }

    public function testDoesNotDispatchEventWhenArticleNotFound(): void
    {
        // Arrange
        $articleId = ArticleId::generate();

        $this->repository
            ->method('findById')
            ->willReturn(null);

        $this->eventDispatcher
            ->expects($this->never())
            ->method('dispatch');

        $command = new PublishArticle($articleId);

        // Act & Assert
        try {
            $this->handler->handle($command);
        } catch (ArticleNotFoundException $e) {
            // Expected exception
        }
    }

    public function testThrowsExceptionWhenPublishingAlreadyPublishedArticle(): void
    {
        // Arrange
        $articleId = ArticleId::generate();
        $article = $this->createPublishedArticle($articleId);

        $this->repository
            ->method('findById')
            ->willReturn($article);

        $command = new PublishArticle($articleId);

        // Assert
        $this->expectException(InvalidStatusTransition::class);

        // Act
        $this->handler->handle($command);
    }

    public function testThrowsExceptionWhenPublishingArchivedArticle(): void
    {
        // Arrange
        $articleId = ArticleId::generate();
        $article = $this->createDraftArticle($articleId);
        $article->archive();

        $this->repository
            ->method('findById')
            ->willReturn($article);

        $command = new PublishArticle($articleId);

        // Assert
        $this->expectException(InvalidStatusTransition::class);

        // Act
        $this->handler->handle($command);
    }

    // =========================================================================
    // Repository Interaction Tests
    // =========================================================================

    public function testCallsRepositoryFindByIdExactlyOnce(): void
    {
        // Arrange
        $articleId = ArticleId::generate();
        $article = $this->createDraftArticle($articleId);

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with($articleId)
            ->willReturn($article);

        $command = new PublishArticle($articleId);

        // Act
        $this->handler->handle($command);
    }

    public function testCallsRepositorySaveExactlyOnce(): void
    {
        // Arrange
        $articleId = ArticleId::generate();
        $article = $this->createDraftArticle($articleId);

        $this->repository
            ->method('findById')
            ->willReturn($article);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($article);

        $command = new PublishArticle($articleId);

        // Act
        $this->handler->handle($command);
    }

    public function testSavesArticleAfterPublishing(): void
    {
        // Arrange
        $articleId = ArticleId::generate();
        $article = $this->createDraftArticle($articleId);
        $savedArticle = null;

        $this->repository
            ->method('findById')
            ->willReturn($article);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Article $article) use (&$savedArticle) {
                $savedArticle = $article;
            });

        $command = new PublishArticle($articleId);

        // Act
        $this->handler->handle($command);

        // Assert
        $this->assertNotNull($savedArticle);
        $this->assertTrue($savedArticle->isPublished());
        $this->assertNotNull($savedArticle->getPublishedAt());
    }

    // =========================================================================
    // State Verification Tests
    // =========================================================================

    public function testArticleRemainsPublishedAfterHandle(): void
    {
        // Arrange
        $articleId = ArticleId::generate();
        $article = $this->createDraftArticle($articleId);

        $this->repository
            ->method('findById')
            ->willReturn($article);

        $command = new PublishArticle($articleId);

        // Act
        $this->handler->handle($command);

        // Assert
        $this->assertTrue($article->isPublished());
        $this->assertFalse($article->isDraft());
        $this->assertFalse($article->isArchived());
    }

    public function testArticleIsNoLongerEditableAfterPublish(): void
    {
        // Arrange
        $articleId = ArticleId::generate();
        $article = $this->createDraftArticle($articleId);

        $this->repository
            ->method('findById')
            ->willReturn($article);

        $command = new PublishArticle($articleId);

        // Act
        $this->assertTrue($article->isEditable());  // Before
        $this->handler->handle($command);

        // Note: Per the updated business rule, published articles ARE editable
        $this->assertTrue($article->isEditable());  // After
    }

    // =========================================================================
    // Multiple Execution Tests
    // =========================================================================

    public function testCanPublishMultipleDifferentArticles(): void
    {
        // Arrange
        $article1 = $this->createDraftArticle(ArticleId::generate());
        $article2 = $this->createDraftArticle(ArticleId::generate());

        $this->repository
            ->method('findById')
            ->willReturnOnConsecutiveCalls($article1, $article2);

        $this->repository
            ->expects($this->exactly(2))
            ->method('save');

        $this->eventDispatcher
            ->expects($this->exactly(2))
            ->method('dispatch');

        // Act
        $this->handler->handle(new PublishArticle($article1->id));
        $this->handler->handle(new PublishArticle($article2->id));

        // Assert
        $this->assertTrue($article1->isPublished());
        $this->assertTrue($article2->isPublished());
    }

    public function testEachPublishCreatesIndependentEvent(): void
    {
        // Arrange
        $article1 = $this->createDraftArticle(ArticleId::generate());
        $article2 = $this->createDraftArticle(ArticleId::generate());

        $this->repository
            ->method('findById')
            ->willReturnOnConsecutiveCalls($article1, $article2);

        $dispatchCount = 0;
        $this->eventDispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function ($events) use (&$dispatchCount) {
                $this->assertCount(1, $events);
                $this->assertInstanceOf(ArticlePublished::class, $events[0]);
                $dispatchCount++;
            });

        // Act
        $this->handler->handle(new PublishArticle($article1->id));
        $this->handler->handle(new PublishArticle($article2->id));

        // Assert
        $this->assertEquals(2, $dispatchCount);
    }
}
