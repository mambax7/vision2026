<?php

declare(strict_types=1);

/**
 * Vision 2026 Module - Database Cleanup Utility
 */

use Vision2026\Infrastructure\Container\ServiceContainer;
use Vision2026\Infrastructure\Persistence\XoopsConnection;

// XOOPS admin header
require_once __DIR__ . '/admin_header.php';
xoops_cp_header();

// Module autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

$container = ServiceContainer::getInstance();
$connection = $container->get(XoopsConnection::class);

$op = $_REQUEST['op'] ?? 'list';

if ($op === 'list') {
    // List all articles
    $sql = "SELECT id, title, slug, status, created_at FROM " . $connection->table('articles') . " ORDER BY created_at DESC";
    $articles = $connection->fetchAll($sql);
    
    echo '<div style="padding: 20px;">';
    echo '<h2>Database Articles</h2>';
    echo '<table class="outer" style="width: 100%;">';
    echo '<tr><th>ID</th><th>Title</th><th>Slug</th><th>Status</th><th>Created</th><th>Actions</th></tr>';
    
    foreach ($articles as $article) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($article['id']) . '</td>';
        echo '<td>' . htmlspecialchars($article['title']) . '</td>';
        echo '<td>' . htmlspecialchars($article['slug']) . '</td>';
        echo '<td>' . htmlspecialchars($article['status']) . '</td>';
        echo '<td>' . htmlspecialchars($article['created_at']) . '</td>';
        echo '<td><a href="?op=delete&id=' . urlencode($article['id']) . '">Delete</a></td>';
        echo '</tr>';
    }
    
    echo '</table>';
    echo '<p><a href="articles.php" class="button">Back to Articles</a></p>';
    echo '</div>';
    
} elseif ($op === 'delete' && !empty($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM " . $connection->table('articles') . " WHERE id = " . $connection->quote($id);
    $connection->execute($sql);
    redirect_header('database_cleanup.php', 2, 'Article deleted');
}

require_once __DIR__ . '/admin_footer.php';
