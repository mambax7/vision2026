<?php

declare(strict_types=1);

namespace Vision2026\Domain\Entity;

use Vision2026\Domain\ValueObject\ArticleId;
use Vision2026\Domain\ValueObject\ArticleTitle;
use Vision2026\Domain\ValueObject\ArticleSlug;
use Vision2026\Domain\ValueObject\ArticleContent;
use Vision2026\Domain\ValueObject\AuthorId;
use Vision2026\Domain\ValueObject\CategoryId;
use Vision2026\Domain\Event\DomainEvent;
use Vision2026\Domain\Event\ArticleCreated;
use Vision2026\Domain\Event\ArticlePublished;
use Vision2026\Domain\Event\ArticleUpdated;
use Vision2026\Domain\Exception\InvalidStatusTransition;

/**
 * Article aggregate root.
 *
 * This is the heart of the domain model. Key characteristics:
 *
 * 1. ENCAPSULATION: Business rules are inside the entity, not scattered in handlers
 * 2. FACTORY METHODS: Constructors are private; use create() or reconstitute()
 * 3. DOMAIN EVENTS: State changes record events for side effects
 * 4. IMMUTABLE ID: The identity never changes after creation
 * 5. INVARIANTS: Invalid states are impossible to create
 */
final class Article
{
    /** @var list<DomainEvent> */
    private array $domainEvents = [];

    /**
     * Private constructor enforces use of factory methods.
     */
    private function __construct(
        public readonly ArticleId $id,
        private ArticleTitle $title,
        private ArticleSlug $slug,
        private ArticleContent $content,
        private ArticleStatus $status,
        private readonly AuthorId $authorId,
        private ?CategoryId $categoryId,
        private readonly \DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $updatedAt = null,
        private ?\DateTimeImmutable $publishedAt = null,
    ) {}

    /**
     * Create a NEW article.
     *
     * Use this when the user is creating new content.
     * Records ArticleCreated domain event.
     */
    public static function create(
        ArticleId $id,
        ArticleTitle $title,
        ArticleContent $content,
        AuthorId $authorId,
        ?CategoryId $categoryId = null,
    ): self {
        $article = new self(
            id: $id,
            title: $title,
            slug: ArticleSlug::fromTitle($title),
            content: $content,
            status: ArticleStatus::Draft,
            authorId: $authorId,
            categoryId: $categoryId,
            createdAt: new \DateTimeImmutable(),
        );

        $article->recordEvent(new ArticleCreated(
            articleId: $id,
            title: $title,
            authorId: $authorId,
            occurredAt: new \DateTimeImmutable(),
        ));

        return $article;
    }

    /**
     * Reconstitute an article from persistence.
     *
     * Use this when loading from database.
     * Does NOT record domain events (this is a reconstruction, not a new action).
     */
    public static function reconstitute(
        ArticleId $id,
        ArticleTitle $title,
        ArticleSlug $slug,
        ArticleContent $content,
        ArticleStatus $status,
        AuthorId $authorId,
        ?CategoryId $categoryId,
        \DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $updatedAt,
        ?\DateTimeImmutable $publishedAt,
    ): self {
        return new self(
            id: $id,
            title: $title,
            slug: $slug,
            content: $content,
            status: $status,
            authorId: $authorId,
            categoryId: $categoryId,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            publishedAt: $publishedAt,
        );
    }

    /**
     * Update article content.
     *
     * Only allowed for draft articles.
     */
    public function update(
        ArticleTitle $title,
        ArticleContent $content,
        ?CategoryId $categoryId = null,
    ): void {
        if (!$this->status->isEditable()) {
            throw new InvalidStatusTransition(
                'Cannot edit article in status: ' . $this->status->value
            );
        }

        $titleChanged = !$this->title->equals($title);

        $this->title = $title;
        $this->content = $content;
        $this->categoryId = $categoryId;
        $this->updatedAt = new \DateTimeImmutable();

        // Only regenerate slug if title changed
        if ($titleChanged) {
            $this->slug = ArticleSlug::fromTitle($title);
        }

        $this->recordEvent(new ArticleUpdated(
            articleId: $this->id,
            title: $title,
            occurredAt: new \DateTimeImmutable(),
        ));
    }

    /**
     * Publish the article.
     *
     * Transitions from Draft to Published.
     */
    public function publish(): void
    {
        $this->guardTransition(ArticleStatus::Published);

        $this->status = ArticleStatus::Published;
        $this->publishedAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new ArticlePublished(
            articleId: $this->id,
            publishedAt: $this->publishedAt,
            occurredAt: new \DateTimeImmutable(),
        ));
    }

    /**
     * Archive the article.
     *
     * Transitions from Draft or Published to Archived.
     */
    /**
     * Unpublish the article.
     *
     * Transitions from Published to Draft.
     * Useful for retracting content or making further edits.
     */
    public function unpublish(): void
    {
        $this->guardTransition(ArticleStatus::Draft);

        $this->status = ArticleStatus::Draft;
        $this->publishedAt = null;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function archive(): void
    {
        $this->guardTransition(ArticleStatus::Archived);

        $this->status = ArticleStatus::Archived;
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Guard against invalid state transitions.
     *
     * @throws InvalidStatusTransition
     */
    private function guardTransition(ArticleStatus $target): void
    {
        if (!$this->status->canTransitionTo($target)) {
            throw new InvalidStatusTransition(
                sprintf(
                    'Cannot transition from %s to %s',
                    $this->status->value,
                    $target->value
                )
            );
        }
    }

    /**
     * Record a domain event.
     */
    private function recordEvent(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * Pull and clear recorded domain events.
     *
     * Called by application layer after saving.
     *
     * @return list<DomainEvent>
     */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }

    // =========================================================================
    // Getters - Expose state in a controlled way
    // =========================================================================

    public function getTitle(): ArticleTitle
    {
        return $this->title;
    }

    public function getSlug(): ArticleSlug
    {
        return $this->slug;
    }

    public function getContent(): ArticleContent
    {
        return $this->content;
    }

    public function getStatus(): ArticleStatus
    {
        return $this->status;
    }

    public function getAuthorId(): AuthorId
    {
        return $this->authorId;
    }

    public function getCategoryId(): ?CategoryId
    {
        return $this->categoryId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    // =========================================================================
    // Query methods - Express business concepts
    // =========================================================================

    public function isDraft(): bool
    {
        return $this->status === ArticleStatus::Draft;
    }

    public function isPublished(): bool
    {
        return $this->status === ArticleStatus::Published;
    }

    public function isArchived(): bool
    {
        return $this->status === ArticleStatus::Archived;
    }

    public function isEditable(): bool
    {
        return $this->status->isEditable();
    }

    public function isPublic(): bool
    {
        return $this->status->isPublic();
    }

    public function canPublish(): bool
    {
        return $this->status->canTransitionTo(ArticleStatus::Published);
    }

    public function canArchive(): bool
    {
        return $this->status->canTransitionTo(ArticleStatus::Archived);
    }

    public function isOwnedBy(AuthorId $authorId): bool
    {
        return $this->authorId->equals($authorId);
    }
}
