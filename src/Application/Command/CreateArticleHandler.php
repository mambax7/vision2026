<?php

declare(strict_types=1);

namespace Vision2026\Application\Command;

use Vision2026\Domain\Entity\Article;
use Vision2026\Domain\Repository\ArticleRepositoryInterface;
use Vision2026\Domain\ValueObject\ArticleId;
use Vision2026\Domain\ValueObject\ArticleTitle;
use Vision2026\Domain\ValueObject\ArticleContent;
use Vision2026\Domain\ValueObject\AuthorId;
use Vision2026\Domain\ValueObject\CategoryId;
use Vision2026\Application\Event\EventDispatcherInterface;

/**
 * Handler for CreateArticle command.
 *
 * Handlers orchestrate the use case:
 * 1. Receive command with primitive data
 * 2. Create domain objects (validation happens here)
 * 3. Execute domain logic
 * 4. Persist changes via repository
 * 5. Dispatch domain events
 *
 * Notice: The handler is thin - business logic lives in the Article entity.
 */
final readonly class CreateArticleHandler
{
    public function __construct(
        private ArticleRepositoryInterface $articles,
        private ?EventDispatcherInterface $events = null,
    ) {}

    /**
     * Handle the create article command.
     *
     * @throws \InvalidArgumentException if validation fails
     * @return ArticleId The ID of the created article
     */
    public function handle(CreateArticle $command): ArticleId
    {
        // 1. Transform primitives to domain objects (validation happens here)
        $id = $this->articles->nextIdentity();
        $title = ArticleTitle::fromString($command->title);
        $content = ArticleContent::fromString($command->content, $command->format);
        $authorId = AuthorId::fromInt($command->authorId);
        $categoryId = $command->categoryId !== null
            ? CategoryId::fromInt($command->categoryId)
            : null;

        // 2. Create the article (domain logic in entity)
        $article = Article::create(
            id: $id,
            title: $title,
            content: $content,
            authorId: $authorId,
            categoryId: $categoryId,
        );

        // 3. Persist via repository
        $this->articles->save($article);

        // 4. Dispatch domain events (side effects happen elsewhere)
        if ($this->events !== null) {
            foreach ($article->pullDomainEvents() as $event) {
                $this->events->dispatch($event);
            }
        }

        // 5. Return the new ID
        return $article->id;
    }
}
