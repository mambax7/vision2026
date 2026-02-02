<?php

declare(strict_types=1);

namespace Vision2026\Infrastructure\Persistence;

/**
 * Wrapper around XOOPS database connection.
 *
 * Provides a cleaner interface to the global $xoopsDB object.
 * Isolates infrastructure code from direct XOOPS globals.
 *
 * This class lives in Infrastructure because it deals with
 * XOOPS-specific database access.
 */
final class XoopsConnection
{
    private \XoopsDatabase $db;
    private string $prefix;

    public function __construct()
    {
        global $xoopsDB;

        if (!isset($xoopsDB)) {
            throw new \RuntimeException('XOOPS database not initialized');
        }

        $this->db = $xoopsDB;
        $this->prefix = $xoopsDB->prefix();
    }

    /**
     * Get table name with XOOPS prefix.
     */
    public function table(string $name): string
    {
        return $this->prefix($name);
    }

    /**
     * Add XOOPS prefix to table name.
     */
    public function prefix(string $tableName): string
    {
        return $this->prefix . $tableName;
    }

    /**
     * Execute a SELECT query and return all rows.
     *
     * @return list<array<string, mixed>>
     */
    public function fetchAll(string $sql): array
    {
        $result = $this->db->query($sql);

        if (!$result) {
            return [];
        }

        $rows = [];
        while ($row = $this->db->fetchArray($result)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Execute a SELECT query and return a single row.
     *
     * @return array<string, mixed>|null
     */
    public function fetchOne(string $sql): ?array
    {
        $result = $this->db->query($sql);

        if (!$result) {
            return null;
        }

        $row = $this->db->fetchArray($result);

        return $row ?: null;
    }

    /**
     * Execute an INSERT, UPDATE, or DELETE query.
     */
    public function execute(string $sql): bool
    {
        return (bool) $this->db->exec($sql);
    }

    /**
     * Get the last inserted auto-increment ID.
     */
    public function lastInsertId(): int
    {
        return (int) $this->db->getInsertId();
    }

    /**
     * Get number of affected rows from last query.
     */
    public function affectedRows(): int
    {
        return $this->db->getAffectedRows();
    }

    /**
     * Escape a string for use in SQL queries.
     */
    public function escape(string $value): string
    {
        return $this->db->escape($value);
    }

    /**
     * Quote a string value for SQL.
     */
    public function quote(string $value): string
    {
        return "'" . $this->escape($value) . "'";
    }

    /**
     * Quote an identifier (table/column name).
     */
    public function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    /**
     * Start a database transaction.
     */
    public function beginTransaction(): void
    {
        $this->db->query('START TRANSACTION');
    }

    /**
     * Commit the current transaction.
     */
    public function commit(): void
    {
        $this->db->query('COMMIT');
    }

    /**
     * Rollback the current transaction.
     */
    public function rollback(): void
    {
        $this->db->query('ROLLBACK');
    }

    /**
     * Get the underlying XOOPS database object.
     *
     * Use sparingly - prefer methods on this class.
     */
    public function getXoopsDb(): \XoopsDatabase
    {
        return $this->db;
    }
}
