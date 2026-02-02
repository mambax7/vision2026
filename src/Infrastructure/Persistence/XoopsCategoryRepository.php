<?php

declare(strict_types=1);

namespace Vision2026\Infrastructure\Persistence;

use Vision2026\Domain\Entity\Category;
use Vision2026\Domain\Repository\CategoryRepositoryInterface;
use Vision2026\Domain\ValueObject\CategoryId;
use Vision2026\Domain\ValueObject\CategorySlug;
use Vision2026\Domain\Exception\CategoryNotFound;

/**
 * XOOPS implementation of CategoryRepository.
 */
final class XoopsCategoryRepository implements CategoryRepositoryInterface
{
    private const TABLE = 'vision2026_categories';

    private XoopsConnection $connection;
    private CategoryMapper $mapper;

    public function __construct(
        XoopsConnection $connection,
        CategoryMapper $mapper
    ) {
        $this->connection = $connection;
        $this->mapper = $mapper;
    }

    public function nextIdentity(): CategoryId
    {
        // For auto-increment IDs, we don't generate ahead of time
        return CategoryId::fromInt(0); // Will be set by database
    }

    public function find(CategoryId $id): ?Category
    {
        $table = $this->connection->table(self::TABLE);
        $idInt = $id->value;

        $sql = "SELECT * FROM {$table} WHERE id = {$idInt} LIMIT 1";

        $row = $this->connection->fetchOne($sql);

        if ($row === null) {
            return null;
        }

        return $this->mapper->toDomain($row);
    }

    public function findOrFail(CategoryId $id): Category
    {
        $category = $this->find($id);

        if ($category === null) {
            throw new CategoryNotFound("Category not found: {$id->value}");
        }

        return $category;
    }

    public function findBySlug(CategorySlug $slug): ?Category
    {
        $table = $this->connection->table(self::TABLE);
        $quotedSlug = $this->connection->quote($slug->value);

        $sql = "SELECT * FROM {$table} WHERE slug = {$quotedSlug} LIMIT 1";

        $row = $this->connection->fetchOne($sql);

        if ($row === null) {
            return null;
        }

        return $this->mapper->toDomain($row);
    }

    public function findAll(): array
    {
        $table = $this->connection->table(self::TABLE);

        $sql = "SELECT * FROM {$table} ORDER BY weight ASC, name ASC";

        $rows = $this->connection->fetchAll($sql);

        return $this->mapper->toDomainCollection($rows);
    }

    public function findRootCategories(): array
    {
        $table = $this->connection->table(self::TABLE);

        $sql = "SELECT * FROM {$table} WHERE parent_id IS NULL ORDER BY weight ASC, name ASC";

        $rows = $this->connection->fetchAll($sql);

        return $this->mapper->toDomainCollection($rows);
    }

    public function findByParent(CategoryId $parentId): array
    {
        $table = $this->connection->table(self::TABLE);
        $parentIdInt = $parentId->value;

        $sql = "SELECT * FROM {$table} WHERE parent_id = {$parentIdInt} ORDER BY weight ASC, name ASC";

        $rows = $this->connection->fetchAll($sql);

        return $this->mapper->toDomainCollection($rows);
    }

    public function save(Category $category): void
    {
        $exists = $this->find($category->id) !== null;

        if ($exists) {
            $this->update($category);
        } else {
            $this->insert($category);
        }
    }

    public function remove(Category $category): void
    {
        $table = $this->connection->table(self::TABLE);
        $idInt = $category->id->value;

        $sql = "DELETE FROM {$table} WHERE id = {$idInt}";

        $this->connection->execute($sql);
    }

    public function count(): int
    {
        $table = $this->connection->table(self::TABLE);

        $sql = "SELECT COUNT(*) as total FROM {$table}";

        $row = $this->connection->fetchOne($sql);

        return (int) ($row['total'] ?? 0);
    }

    public function slugExists(CategorySlug $slug, ?CategoryId $excludeId = null): bool
    {
        $table = $this->connection->table(self::TABLE);
        $quotedSlug = $this->connection->quote($slug->value);

        $sql = "SELECT COUNT(*) as total FROM {$table} WHERE slug = {$quotedSlug}";

        if ($excludeId !== null) {
            $excludeIdInt = $excludeId->value;
            $sql .= " AND id != {$excludeIdInt}";
        }

        $row = $this->connection->fetchOne($sql);

        return (int) ($row['total'] ?? 0) > 0;
    }

    private function insert(Category $category): void
    {
        $table = $this->connection->table(self::TABLE);
        $data = $this->mapper->toInsertData($category);

        $columns = array_keys($data);
        $quotedColumns = array_map(
            fn($col) => $this->connection->quoteIdentifier($col),
            $columns
        );
        $columnList = implode(', ', $quotedColumns);

        $values = $this->mapper->buildInsertValues($data, $this->connection);

        $sql = "INSERT INTO {$table} ({$columnList}) VALUES {$values}";

        $this->connection->execute($sql);
    }

    private function update(Category $category): void
    {
        $table = $this->connection->table(self::TABLE);
        $data = $this->mapper->toUpdateData($category);

        $setClause = $this->mapper->buildSetClause($data, $this->connection);

        $idInt = $category->id->value;

        $sql = "UPDATE {$table} SET {$setClause} WHERE id = {$idInt}";

        $this->connection->execute($sql);
    }
}
