<?php

declare(strict_types=1);

namespace Vision2026\Domain\Event;

use Vision2026\Domain\ValueObject\ArticleId;

/**
 * Event raised when an article is published.
 *
 * Possible reactions:
 * - Send notification to subscribers
 * - Update search index
 * - Post to social media
 * - Clear relevant caches
 * - Update RSS feed
 */
final readonly class ArticlePublished implements DomainEvent
{
    public function __construct(
        public ArticleId $articleId,
        public \DateTimeImmutable $publishedAt,
        public \DateTimeImmutable $occurredAt,
    ) {}

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function aggregateId(): string
    {
        return $this->articleId->toString();
    }

    public function eventName(): string
    {
        return 'article.published';
    }

    public function toArray(): array
    {
        return [
            'article_id' => $this->articleId->toString(),
            'published_at' => $this->publishedAt->format('c'),
            'occurred_at' => $this->occurredAt->format('c'),
        ];
    }
}
