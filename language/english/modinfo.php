<?php

declare(strict_types=1);

/**
 * Vision 2026 Module - Module Info Language File
 *
 * These constants are used in xoops_version.php
 */

// Module info
define('_MI_VISION2026_NAME', 'Vision 2026');
define('_MI_VISION2026_DESC', 'Reference implementation showcasing XOOPS 2026 architecture and PHP best practices');

// Menu items
define('_MI_VISION2026_ARTICLES', 'Articles');
define('_MI_VISION2026_CATEGORIES', 'Categories');
define('_MI_VISION2026_DEMO', 'Demo');


// Admin menu
define('_MI_VISION2026_ADMIN_INDEX', 'Dashboard');
define('_MI_VISION2026_ADMIN_ARTICLES', 'Manage Articles');
define('_MI_VISION2026_ADMIN_CATEGORIES', 'Manage Categories');
define('_MI_VISION2026_ADMIN_ABOUT', 'About');

// Config options
define('_MI_VISION2026_PERPAGE', 'Articles per page');
define('_MI_VISION2026_PERPAGE_DESC', 'Number of articles to display per page on the frontend');
define('_MI_VISION2026_COMMENTS', 'Enable comments');
define('_MI_VISION2026_COMMENTS_DESC', 'Allow users to comment on articles');

// Blocks
define('_MI_VISION2026_BLOCK_RECENT', 'Recent Articles');
define('_MI_VISION2026_BLOCK_RECENT_DESC', 'Displays the most recently published articles');

// Notifications
define('_MI_VISION2026_NOTIFY_GLOBAL', 'Global');
define('_MI_VISION2026_NOTIFY_GLOBAL_DESC', 'Notifications for all articles');
define('_MI_VISION2026_NOTIFY_ARTICLE', 'Article');
define('_MI_VISION2026_NOTIFY_ARTICLE_DESC', 'Notifications for specific article');
define('_MI_VISION2026_NOTIFY_NEW', 'New Article');
define('_MI_VISION2026_NOTIFY_NEW_CAP', 'Notify me when a new article is published');
define('_MI_VISION2026_NOTIFY_NEW_DESC', 'Receive notification when any new article is published');
define('_MI_VISION2026_NOTIFY_NEW_SUBJECT', '[{X_SITENAME}] New article: {ARTICLE_TITLE}');
