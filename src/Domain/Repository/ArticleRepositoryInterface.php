<?php

declare(strict_types=1);

namespace Vision2026\Domain\Repository;

use Vision2026\Domain\Entity\Article;
use Vision2026\Domain\Entity\ArticleStatus;
use Vision2026\Domain\ValueObject\ArticleId;
use Vision2026\Domain\ValueObject\ArticleSlug;
use Vision2026\Domain\ValueObject\AuthorId;
use Vision2026\Domain\ValueObject\CategoryId;

/**
 * Repository interface for Article aggregate.
 *
 * This interface lives in the DOMAIN layer - it knows nothing about
 * databases, XOOPS, or any infrastructure concerns.
 *
 * The actual implementation (XoopsArticleRepository) lives in the
 * INFRASTRUCTURE layer and uses the XOOPS database.
 *
 * This is Dependency Inversion in action:
 * - Domain defines WHAT it needs
 * - Infrastructure provides HOW it's done
 */
interface ArticleRepositoryInterface
{
    /**
     * Generate a new unique identifier.
     *
     * Uses ULID for time-sortable, globally unique IDs.
     */
    public function nextIdentity(): ArticleId;

    /**
     * Find an article by ID.
     *
     * @return Article|null The article, or null if not found
     */
    public function find(ArticleId $id): ?Article;

    /**
     * Find an article by ID, throw if not found.
     *
     * @throws \Vision2026\Domain\Exception\ArticleNotFound
     */
    public function findOrFail(ArticleId $id): Article;

    /**
     * Find an article by its URL slug.
     */
    public function findBySlug(ArticleSlug $slug): ?Article;

    /**
     * Find all articles with a given status.
     *
     * @return list<Article>
     */
    public function findByStatus(ArticleStatus $status): array;

    /**
     * Find all articles by a specific author.
     *
     * @return list<Article>
     */
    public function findByAuthor(AuthorId $authorId): array;

    /**
     * Find all articles in a category.
     *
     * @return list<Article>
     */
    public function findByCategory(CategoryId $categoryId): array;

    /**
     * Find published articles with pagination.
     *
     * @return array{items: list<Article>, total: int, page: int, perPage: int}
     */
    public function findPublishedPaginated(int $page, int $perPage): array;

    /**
     * Find recent published articles.
     *
     * @return list<Article>
     */
    public function findRecentPublished(int $limit = 10): array;

    /**
     * Persist an article (insert or update).
     *
     * The repository determines whether to INSERT or UPDATE
     * based on whether the article already exists.
     */
    public function save(Article $article): void;

    /**
     * Remove an article from persistence.
     */
    public function remove(Article $article): void;

    /**
     * Count all articles.
     */
    public function count(): int;

    /**
     * Count articles by status.
     */
    public function countByStatus(ArticleStatus $status): int;

    /**
     * Check if a slug is already in use.
     *
     * Optionally exclude a specific article (for updates).
     */
    public function slugExists(ArticleSlug $slug, ?ArticleId $excludeId = null): bool;
}
