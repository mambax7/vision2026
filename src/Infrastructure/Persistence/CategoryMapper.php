<?php

declare(strict_types=1);

namespace Vision2026\Infrastructure\Persistence;

use Vision2026\Domain\Entity\Category;
use Vision2026\Domain\ValueObject\CategoryId;
use Vision2026\Domain\ValueObject\CategoryName;
use Vision2026\Domain\ValueObject\CategorySlug;

/**
 * Maps between database rows and Category entities.
 */
final class CategoryMapper
{
    /**
     * Convert a database row to a Category entity.
     *
     * @param array<string, mixed> $row
     */
    public function toDomain(array $row): Category
    {
        return Category::reconstitute(
            id: CategoryId::fromInt((int) $row['id']),
            name: CategoryName::fromString((string) $row['name']),
            slug: CategorySlug::fromString((string) $row['slug']),
            description: $row['description'] !== null ? (string) $row['description'] : null,
            weight: (int) $row['weight'],
            parentId: $row['parent_id'] !== null ? CategoryId::fromInt((int) $row['parent_id']) : null,
            createdAt: new \DateTimeImmutable($row['created_at']),
            updatedAt: $row['updated_at'] !== null ? new \DateTimeImmutable($row['updated_at']) : null,
        );
    }

    /**
     * Convert multiple database rows to Category entities.
     *
     * @param list<array<string, mixed>> $rows
     * @return list<Category>
     */
    public function toDomainCollection(array $rows): array
    {
        return array_map(
            fn(array $row): Category => $this->toDomain($row),
            $rows
        );
    }

    /**
     * Convert a Category entity to database row for INSERT.
     *
     * @return array<string, mixed>
     */
    public function toInsertData(Category $category): array
    {
        return [
            'name' => $category->getName()->value,
            'slug' => $category->getSlug()->value,
            'description' => $category->getDescription(),
            'weight' => $category->getWeight(),
            'parent_id' => $category->getParentId()?->value,
            'article_count' => 0,
            'created_at' => $category->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $category->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Convert a Category entity to database row for UPDATE.
     *
     * @return array<string, mixed>
     */
    public function toUpdateData(Category $category): array
    {
        return [
            'name' => $category->getName()->value,
            'slug' => $category->getSlug()->value,
            'description' => $category->getDescription(),
            'weight' => $category->getWeight(),
            'updated_at' => $category->getUpdatedAt()?->format('Y-m-d H:i:s'),
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
