<?php

declare(strict_types=1);

namespace Vision2026\Infrastructure\Persistence;

use Vision2026\Domain\Entity\Article;
use Vision2026\Domain\Entity\ArticleStatus;
use Vision2026\Domain\Repository\ArticleRepositoryInterface;
use Vision2026\Domain\ValueObject\ArticleId;
use Vision2026\Domain\ValueObject\ArticleSlug;
use Vision2026\Domain\ValueObject\AuthorId;
use Vision2026\Domain\ValueObject\CategoryId;
use Vision2026\Domain\Exception\ArticleNotFound;

/**
 * XOOPS implementation of ArticleRepository.
 *
 * This is where the rubber meets the road:
 * - Uses XOOPS database connection
 * - Translates domain concepts to SQL
 * - Handles INSERT vs UPDATE logic
 * - Maps database rows to domain entities
 *
 * The Domain layer defines the interface.
 * This Infrastructure layer implements it using XOOPS.
 */
final class XoopsArticleRepository implements ArticleRepositoryInterface
{
    private const TABLE = 'vision2026_articles';

    private XoopsConnection $connection;
    private ArticleMapper $mapper;

    public function __construct(
        XoopsConnection $connection,
        ArticleMapper $mapper
    ) {
        $this->connection = $connection;
        $this->mapper = $mapper;
    }

    /**
     * {@inheritdoc}
     */
    public function nextIdentity(): ArticleId
    {
        return ArticleId::generate();
    }

    /**
     * {@inheritdoc}
     */
    public function find(ArticleId $id): ?Article
    {
        $table = $this->connection->table(self::TABLE);
        $quotedId = $this->connection->quote($id->toString());

        $sql = "SELECT * FROM {$table} WHERE id = {$quotedId} LIMIT 1";

        $row = $this->connection->fetchOne($sql);

        if ($row === null) {
            return null;
        }

        return $this->mapper->toDomain($row);
    }

    /**
     * {@inheritdoc}
     */
    public function findOrFail(ArticleId $id): Article
    {
        $article = $this->find($id);

        if ($article === null) {
            throw new ArticleNotFound("Article not found: {$id->toString()}");
        }

        return $article;
    }

    /**
     * {@inheritdoc}
     */
    public function findBySlug(ArticleSlug $slug): ?Article
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

    /**
     * {@inheritdoc}
     */
    public function findByStatus(ArticleStatus $status): array
    {
        $table = $this->connection->table(self::TABLE);
        $quotedStatus = $this->connection->quote($status->value);

        $sql = "SELECT * FROM {$table}
                WHERE status = {$quotedStatus}
                ORDER BY created_at DESC";

        $rows = $this->connection->fetchAll($sql);

        return $this->mapper->toDomainCollection($rows);
    }

    /**
     * {@inheritdoc}
     */
    public function findByAuthor(AuthorId $authorId): array
    {
        $table = $this->connection->table(self::TABLE);
        $authorIdInt = $authorId->toInt();

        $sql = "SELECT * FROM {$table}
                WHERE author_id = {$authorIdInt}
                ORDER BY created_at DESC";

        $rows = $this->connection->fetchAll($sql);

        return $this->mapper->toDomainCollection($rows);
    }

    /**
     * {@inheritdoc}
     */
    public function findByCategory(CategoryId $categoryId): array
    {
        $table = $this->connection->table(self::TABLE);
        $categoryIdInt = $categoryId->toInt();

        $sql = "SELECT * FROM {$table}
                WHERE category_id = {$categoryIdInt}
                ORDER BY created_at DESC";

        $rows = $this->connection->fetchAll($sql);

        return $this->mapper->toDomainCollection($rows);
    }

    /**
     * {@inheritdoc}
     */
    public function findPublishedPaginated(int $page, int $perPage): array
    {
        $table = $this->connection->table(self::TABLE);
        $offset = ($page - 1) * $perPage;

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$table} WHERE status = 'published'";
        $countRow = $this->connection->fetchOne($countSql);
        $total = (int) ($countRow['total'] ?? 0);

        // Get paginated items
        $sql = "SELECT * FROM {$table}
                WHERE status = 'published'
                ORDER BY published_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $rows = $this->connection->fetchAll($sql);
        $items = $this->mapper->toDomainCollection($rows);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function findRecentPublished(int $limit = 10): array
    {
        $table = $this->connection->table(self::TABLE);

        $sql = "SELECT * FROM {$table}
                WHERE status = 'published'
                ORDER BY published_at DESC
                LIMIT {$limit}";

        $rows = $this->connection->fetchAll($sql);

        return $this->mapper->toDomainCollection($rows);
    }

    /**
     * {@inheritdoc}
     */
    public function save(Article $article): void
    {
        $exists = $this->find($article->id) !== null;

        if ($exists) {
            $this->update($article);
        } else {
            $this->insert($article);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Article $article): void
    {
        $table = $this->connection->table(self::TABLE);
        $quotedId = $this->connection->quote($article->id->toString());

        $sql = "DELETE FROM {$table} WHERE id = {$quotedId}";

        $this->connection->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        $table = $this->connection->table(self::TABLE);

        $sql = "SELECT COUNT(*) as total FROM {$table}";

        $row = $this->connection->fetchOne($sql);

        return (int) ($row['total'] ?? 0);
    }

    /**
     * {@inheritdoc}
     */
    public function countByStatus(ArticleStatus $status): int
    {
        $table = $this->connection->table(self::TABLE);
        $quotedStatus = $this->connection->quote($status->value);

        $sql = "SELECT COUNT(*) as total FROM {$table} WHERE status = {$quotedStatus}";

        $row = $this->connection->fetchOne($sql);

        return (int) ($row['total'] ?? 0);
    }

    /**
     * {@inheritdoc}
     */
    public function slugExists(ArticleSlug $slug, ?ArticleId $excludeId = null): bool
    {
        $table = $this->connection->table(self::TABLE);
        $quotedSlug = $this->connection->quote($slug->value);

        $sql = "SELECT COUNT(*) as total FROM {$table} WHERE slug = {$quotedSlug}";

        if ($excludeId !== null) {
            $quotedId = $this->connection->quote($excludeId->toString());
            $sql .= " AND id != {$quotedId}";
        }

        $row = $this->connection->fetchOne($sql);

        return (int) ($row['total'] ?? 0) > 0;
    }

    /**
     * Insert a new article into the database.
     */
    private function insert(Article $article): void
    {
        $table = $this->connection->table(self::TABLE);
        $data = $this->mapper->toInsertData($article);

        // Build column list
        $columns = array_keys($data);
        $quotedColumns = array_map(
            fn($col) => $this->connection->quoteIdentifier($col),
            $columns
        );
        $columnList = implode(', ', $quotedColumns);

        // Build values
        $values = $this->mapper->buildInsertValues($data, $this->connection);

        $sql = "INSERT INTO {$table} ({$columnList}) VALUES {$values}";

        $this->connection->execute($sql);
    }

    /**
     * Update an existing article in the database.
     */
    private function update(Article $article): void
    {
        $table = $this->connection->table(self::TABLE);
        $data = $this->mapper->toUpdateData($article);

        // Build SET clause
        $setClause = $this->mapper->buildSetClause($data, $this->connection);

        // WHERE clause
        $quotedId = $this->connection->quote($article->id->toString());

        $sql = "UPDATE {$table} SET {$setClause} WHERE id = {$quotedId}";

        $this->connection->execute($sql);
    }
}
