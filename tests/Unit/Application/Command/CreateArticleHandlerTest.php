<?php

declare(strict_types=1);

namespace Vision2026\Tests\Unit\Application\Command;

use PHPUnit\Framework\TestCase;
use Vision2026\Application\Command\CreateArticle;
use Vision2026\Application\Handler\CreateArticleHandler;
use Vision2026\Domain\Repository\ArticleRepositoryInterface;
use Vision2026\Domain\ValueObject\ArticleId;
use Vision2026\Domain\ValueObject\ArticleTitle;
use Vision2026\Domain\ValueObject\ArticleContent;
use Vision2026\Domain\ValueObject\AuthorId;
use Vision2026\Domain\Entity\Article;
use Vision2026\Domain\Event\ArticleCreated;
use Vision2026\Application\EventDispatcher\EventDispatcherInterface;

/**
 * Unit tests for the CreateArticleHandler.
 *
 * Tests command handling, article creation,
 * repository interaction, and event dispatching.
 */
final class CreateArticleHandlerTest extends TestCase
{
    private ArticleRepositoryInterface $repository;
    private EventDispatcherInterface $eventDispatcher;
    private CreateArticleHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ArticleRepositoryInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->handler = new CreateArticleHandler($this->repository, $this->eventDispatcher);
    }

    // =========================================================================
    // Happy Path Tests
    // =========================================================================

    public function testHandleCreatesArticle(): void
    {
        // Arrange
        $articleId = ArticleId::generate();

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Article $article) use ($articleId) {
                return $article->id->equals($articleId)
                    && $article->getTitle()->value === 'Test Article'
                    && $article->isDraft();
            }));

        $command = new CreateArticle(
            ArticleTitle::fromString('Test Article'),
            ArticleContent::fromString(str_repeat('Content here. ', 10)),
            AuthorId::fromInt(1),
            $articleId
        );

        // Act
        $result = $this->handler->handle($command);

        // Assert
        $this->assertInstanceOf(Article::class, $result);
        $this->assertTrue($result->id->equals($articleId));
    }

    public function testHandleSavesArticleToRepository(): void
    {
        // Arrange
        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Article::class));

        $command = new CreateArticle(
            ArticleTitle::fromString('Test Article'),
            ArticleContent::fromString(str_repeat('Content here. ', 10)),
            AuthorId::fromInt(1)
        );

        // Act
        $this->handler->handle($command);
    }

    public function testHandleDispatchesDomainEvents(): void
    {
        // Arrange
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($events) {
                return is_array($events)
                    && count($events) === 1
                    && $events[0] instanceof ArticleCreated;
            }));

        $command = new CreateArticle(
            ArticleTitle::fromString('Test Article'),
            ArticleContent::fromString(str_repeat('Content here. ', 10)),
            AuthorId::fromInt(1)
        );

        // Act
        $this->handler->handle($command);
    }

    public function testHandleReturnsCreatedArticle(): void
    {
        // Arrange
        $command = new CreateArticle(
            ArticleTitle::fromString('Test Article'),
            ArticleContent::fromString(str_repeat('Content here. ', 10)),
            AuthorId::fromInt(1)
        );

        // Act
        $result = $this->handler->handle($command);

        // Assert
        $this->assertInstanceOf(Article::class, $result);
        $this->assertEquals('Test Article', $result->getTitle()->value);
        $this->assertTrue($result->isDraft());
        $this->assertFalse($result->isPublished());
    }

    // =========================================================================
    // Article Properties Tests
    // =========================================================================

    public function testCreatedArticleHasCorrectTitle(): void
    {
        // Arrange
        $command = new CreateArticle(
            ArticleTitle::fromString('My Amazing Article'),
            ArticleContent::fromString(str_repeat('Content. ', 20)),
            AuthorId::fromInt(42)
        );

        // Act
        $article = $this->handler->handle($command);

        // Assert
        $this->assertEquals('My Amazing Article', $article->getTitle()->value);
    }

    public function testCreatedArticleHasCorrectContent(): void
    {
        // Arrange
        $content = str_repeat('This is test content. ', 15);
        $command = new CreateArticle(
            ArticleTitle::fromString('Test'),
            ArticleContent::fromString($content),
            AuthorId::fromInt(1)
        );

        // Act
        $article = $this->handler->handle($command);

        // Assert
        $this->assertEquals(trim($content), $article->getContent()->value);
    }

    public function testCreatedArticleHasCorrectAuthor(): void
    {
        // Arrange
        $authorId = AuthorId::fromInt(99);
        $command = new CreateArticle(
            ArticleTitle::fromString('Test'),
            ArticleContent::fromString(str_repeat('Content. ', 10)),
            $authorId
        );

        // Act
        $article = $this->handler->handle($command);

        // Assert
        $this->assertTrue($article->isOwnedBy($authorId));
    }

    public function testCreatedArticleIsDraft(): void
    {
        // Arrange
        $command = new CreateArticle(
            ArticleTitle::fromString('Test'),
            ArticleContent::fromString(str_repeat('Content. ', 10)),
            AuthorId::fromInt(1)
        );

        // Act
        $article = $this->handler->handle($command);

        // Assert
        $this->assertTrue($article->isDraft());
        $this->assertFalse($article->isPublished());
        $this->assertFalse($article->isArchived());
    }

    // =========================================================================
    // Event Dispatching Tests
    // =========================================================================

    public function testDispatchesArticleCreatedEvent(): void
    {
        // Arrange
        $capturedEvents = null;

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($events) use (&$capturedEvents) {
                $capturedEvents = $events;
                return null;
            });

        $command = new CreateArticle(
            ArticleTitle::fromString('Test'),
            ArticleContent::fromString(str_repeat('Content. ', 10)),
            AuthorId::fromInt(1)
        );

        // Act
        $this->handler->handle($command);

        // Assert
        $this->assertIsArray($capturedEvents);
        $this->assertCount(1, $capturedEvents);
        $this->assertInstanceOf(ArticleCreated::class, $capturedEvents[0]);
    }

    public function testArticleCreatedEventContainsCorrectData(): void
    {
        // Arrange
        $articleId = ArticleId::generate();
        $capturedEvents = null;

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($events) use (&$capturedEvents) {
                $capturedEvents = $events;
                return null;
            });

        $command = new CreateArticle(
            ArticleTitle::fromString('Test'),
            ArticleContent::fromString(str_repeat('Content. ', 10)),
            AuthorId::fromInt(1),
            $articleId
        );

        // Act
        $this->handler->handle($command);

        // Assert
        /** @var ArticleCreated $event */
        $event = $capturedEvents[0];
        $this->assertTrue($event->articleId->equals($articleId));
    }

    // =========================================================================
    // Repository Interaction Tests
    // =========================================================================

    public function testCallsRepositorySaveExactlyOnce(): void
    {
        // Arrange
        $this->repository
            ->expects($this->once())
            ->method('save');

        $command = new CreateArticle(
            ArticleTitle::fromString('Test'),
            ArticleContent::fromString(str_repeat('Content. ', 10)),
            AuthorId::fromInt(1)
        );

        // Act
        $this->handler->handle($command);
    }

    public function testSavesArticleWithAllProperties(): void
    {
        // Arrange
        $savedArticle = null;

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Article $article) use (&$savedArticle) {
                $savedArticle = $article;
            });

        $command = new CreateArticle(
            ArticleTitle::fromString('Complete Article'),
            ArticleContent::fromString(str_repeat('Full content here. ', 15)),
            AuthorId::fromInt(42)
        );

        // Act
        $this->handler->handle($command);

        // Assert
        $this->assertNotNull($savedArticle);
        $this->assertEquals('Complete Article', $savedArticle->getTitle()->value);
        $this->assertTrue($savedArticle->isOwnedBy(AuthorId::fromInt(42)));
        $this->assertTrue($savedArticle->isDraft());
        $this->assertNotNull($savedArticle->getCreatedAt());
    }

    // =========================================================================
    // Multiple Execution Tests
    // =========================================================================

    public function testHandleCanBeCalledMultipleTimes(): void
    {
        // Arrange
        $this->repository
            ->expects($this->exactly(3))
            ->method('save');

        $this->eventDispatcher
            ->expects($this->exactly(3))
            ->method('dispatch');

        // Act
        for ($i = 1; $i <= 3; $i++) {
            $command = new CreateArticle(
                ArticleTitle::fromString("Article $i"),
                ArticleContent::fromString(str_repeat("Content $i. ", 10)),
                AuthorId::fromInt($i)
            );

            $article = $this->handler->handle($command);

            // Assert
            $this->assertEquals("Article $i", $article->getTitle()->value);
        }
    }

    public function testEachExecutionCreatesUniqueArticle(): void
    {
        // Arrange
        $articles = [];

        $this->repository
            ->expects($this->exactly(2))
            ->method('save')
            ->willReturnCallback(function (Article $article) use (&$articles) {
                $articles[] = $article;
            });

        // Act
        $command1 = new CreateArticle(
            ArticleTitle::fromString('Article 1'),
            ArticleContent::fromString(str_repeat('Content 1. ', 10)),
            AuthorId::fromInt(1)
        );

        $command2 = new CreateArticle(
            ArticleTitle::fromString('Article 2'),
            ArticleContent::fromString(str_repeat('Content 2. ', 10)),
            AuthorId::fromInt(2)
        );

        $this->handler->handle($command1);
        $this->handler->handle($command2);

        // Assert
        $this->assertCount(2, $articles);
        $this->assertFalse($articles[0]->id->equals($articles[1]->id));
        $this->assertNotEquals($articles[0]->getTitle()->value, $articles[1]->getTitle()->value);
    }
}
