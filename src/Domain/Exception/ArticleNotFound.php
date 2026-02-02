<?php

declare(strict_types=1);

namespace Vision2026\Domain\Exception;

use Vision2026\Domain\ValueObject\ArticleId;
use Vision2026\Domain\ValueObject\ArticleSlug;

/**
 * Exception thrown when an article cannot be found.
 *
 * This is a domain exception - it represents a business rule violation
 * (trying to operate on a non-existent article).
 */
final class ArticleNotFound extends \DomainException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    /**
     * Create exception for missing article by ID.
     */
    public static function withId(ArticleId $id): self
    {
        return new self(
            sprintf('Article not found with ID: %s', $id->toString())
        );
    }

    /**
     * Create exception for missing article by slug.
     */
    public static function withSlug(ArticleSlug $slug): self
    {
        return new self(
            sprintf('Article not found with slug: %s', $slug->value)
        );
    }
}
