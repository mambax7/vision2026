<?php

declare(strict_types=1);

namespace Vision2026\Application\Command;

use Vision2026\Domain\Repository\ArticleRepositoryInterface;
use Vision2026\Domain\ValueObject\ArticleId;
use Vision2026\Application\Event\EventDispatcherInterface;

/**
 * Handler for PublishArticle command.
 *
 * This demonstrates how the handler is thin:
 * - Load the article
 * - Call the domain method (article->publish())
 * - Save and dispatch events
 *
 * All the business logic (can this be published? what happens when published?)
 * is in the Article entity, not here.
 */
final readonly class PublishArticleHandler
{
    public function __construct(
        private ArticleRepositoryInterface $articles,
        private ?EventDispatcherInterface $events = null,
    ) {}

    /**
     * Handle the publish article command.
     *
     * @throws \Vision2026\Domain\Exception\ArticleNotFound
     * @throws \Vision2026\Domain\Exception\InvalidStatusTransition
     */
    public function handle(PublishArticle $command): void
    {
        // 1. Load the article (throws if not found)
        $articleId = ArticleId::fromString($command->articleId);
        $article = $this->articles->findOrFail($articleId);

        // 2. Execute domain logic (throws if invalid transition)
        $article->publish();

        // 3. Persist changes
        $this->articles->save($article);

        // 4. Dispatch domain events
        if ($this->events !== null) {
            foreach ($article->pullDomainEvents() as $event) {
                $this->events->dispatch($event);
            }
        }
    }
}
