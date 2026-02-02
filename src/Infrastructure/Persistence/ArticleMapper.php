<?php

declare(strict_types=1);

namespace Vision2026\Infrastructure\Persistence;

use Vision2026\Domain\Entity\Article;
use Vision2026\Domain\Entity\ArticleStatus;
use Vision2026\Domain\ValueObject\ArticleId;
use Vision2026\Domain\ValueObject\ArticleTitle;
use Vision2026\Domain\ValueObject\ArticleSlug;
use Vision2026\Domain\ValueObject\ArticleContent;
use Vision2026\Domain\ValueObject\AuthorId;
use Vision2026\Domain\ValueObject\CategoryId;

/**
 * Maps between database rows and Article entities.
 *
 * This is the DATA MAPPER pattern in action:
 * - Database row (array) ↔ Domain entity (Article)
 * - Handles all the ugly conversion logic
 * - Keeps the domain layer clean from persistence concerns
 */
final class ArticleMapper
{
    /**
     * Convert a database row to an Article entity.
     *
     * Uses Article::reconstitute() to avoid firing domain events
     * for data that's already persisted.
     *
     * @param array<string, mixed> $row
     */
    public function toDomain(array $row): Article
    {
        return Article::reconstitute(
            id: ArticleId::fromString((string) $row['id']),
            title: ArticleTitle::fromString((string) $row['title']),
            slug: ArticleSlug::fromString((string) $row['slug']),
            content: ArticleContent::fromString((string) $row['content']),
            status: ArticleStatus::from((string) $row['status']),
            authorId: AuthorId::fromInt((int) $row['author_id']),
            categoryId: $row['category_id'] !== null
                ? CategoryId::fromInt((int) $row['category_id'])
                : null,
            createdAt: new \DateTimeImmutable($row['created_at']),
            updatedAt: $row['updated_at'] !== null
                ? new \DateTimeImmutable($row['updated_at'])
                : null,
            publishedAt: $row['published_at'] !== null
                ? new \DateTimeImmutable($row['published_at'])
                : null,
        );
    }

    /**
     * Convert multiple database rows to Article entities.
     *
     * @param list<array<string, mixed>> $rows
     * @return list<Article>
     */
    public function toDomainCollection(array $rows): array
    {
        return array_map(
            fn(array $row): Article => $this->toDomain($row),
            $rows
        );
    }

    /**
     * Convert an Article entity to a database row for INSERT.
     *
     * @return array<string, mixed>
     */
    public function toInsertData(Article $article): array
    {
        return [
            'id' => $article->id->toString(),
            'title' => $article->getTitle()->value,
            'slug' => $article->getSlug()->value,
            'content' => $article->getContent()->value,
            'content_format' => 'html', // Default format
            'status' => $article->getStatus()->value,
            'author_id' => $article->getAuthorId()->value,
            'category_id' => $article->getCategoryId()?->value,
            'created_at' => $article->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $article->getUpdatedAt()?->format('Y-m-d H:i:s'),
            'published_at' => $article->getPublishedAt()?->format('Y-m-d H:i:s'),
            'views' => 0, // New articles start with 0 views
        ];
    }

    /**
     * Convert an Article entity to a database row for UPDATE.
     *
     * Excludes immutable fields like id, created_at, author_id.
     *
     * @return array<string, mixed>
     */
    public function toUpdateData(Article $article): array
    {
        return [
            'title' => $article->getTitle()->value,
            'slug' => $article->getSlug()->value,
            'content' => $article->getContent()->value,
            'status' => $article->getStatus()->value,
            'category_id' => $article->getCategoryId()?->value,
            'updated_at' => $article->getUpdatedAt()?->format('Y-m-d H:i:s'),
            'published_at' => $article->getPublishedAt()?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Build SQL SET clause for UPDATE queries.
     *
     * @param array<string, mixed> $data
     */
    public function buildSetClause(array $data, XoopsConnection $connection): string
    {
        $parts = [];

        foreach ($data as $column => $value) {
            $quotedColumn = $connection->quoteIdentifier($column);

            if ($value === null) {
                $parts[] = "$quotedColumn = NULL";
            } elseif (is_int($value)) {
                $parts[] = "$quotedColumn = $value";
            } else {
                $quotedValue = $connection->quote((string) $value);
                $parts[] = "$quotedColumn = $quotedValue";
            }
        }

        return implode(', ', $parts);
    }

    /**
     * Build SQL INSERT VALUES clause.
     *
     * @param array<string, mixed> $data
     */
    public function buildInsertValues(array $data, XoopsConnection $connection): string
    {
        $values = [];

        foreach ($data as $value) {
            if ($value === null) {
                $values[] = 'NULL';
            } elseif (is_int($value)) {
                $values[] = (string) $value;
            } else {
                $values[] = $connection->quote((string) $value);
            }
        }

        return '(' . implode(', ', $values) . ')';
    }
}
