<?php

declare(strict_types=1);

namespace Vision2026\Domain\Repository;

use Vision2026\Domain\Entity\Category;
use Vision2026\Domain\ValueObject\CategoryId;
use Vision2026\Domain\ValueObject\CategorySlug;

/**
 * Repository interface for Category entity.
 */
interface CategoryRepositoryInterface
{
    /**
     * Generate a new unique identifier.
     */
    public function nextIdentity(): CategoryId;

    /**
     * Find a category by ID.
     */
    public function find(CategoryId $id): ?Category;

    /**
     * Find a category by ID, throw if not found.
     */
    public function findOrFail(CategoryId $id): Category;

    /**
     * Find a category by slug.
     */
    public function findBySlug(CategorySlug $slug): ?Category;

    /**
     * Find all categories ordered by weight.
     *
     * @return list<Category>
     */
    public function findAll(): array;

    /**
     * Find root categories (no parent).
     *
     * @return list<Category>
     */
    public function findRootCategories(): array;

    /**
     * Find child categories of a parent.
     *
     * @return list<Category>
     */
    public function findByParent(CategoryId $parentId): array;

    /**
     * Persist a category (insert or update).
     */
    public function save(Category $category): void;

    /**
     * Remove a category.
     */
    public function remove(Category $category): void;

    /**
     * Count all categories.
     */
    public function count(): int;

    /**
     * Check if a slug is already in use.
     */
    public function slugExists(CategorySlug $slug, ?CategoryId $excludeId = null): bool;
}
