<?php

declare(strict_types=1);

/**
 * Vision 2026 Module - Admin Menu Configuration
 */

// Load language file if not already loaded
if (!defined('_AM_VISION2026_DASHBOARD')) {
    $langPath = XOOPS_ROOT_PATH . '/modules/' . basename(dirname(__DIR__)) . '/language';
    if (file_exists($langPath . '/' . $GLOBALS['xoopsConfig']['language'] . '/admin.php')) {
        require_once $langPath . '/' . $GLOBALS['xoopsConfig']['language'] . '/admin.php';
    } elseif (file_exists($langPath . '/english/admin.php')) {
        require_once $langPath . '/english/admin.php';
    }
}

$adminmenu = [];
$i = 0;

// Dashboard
$adminmenu[++$i] = [
    'title' => defined('_AM_VISION2026_DASHBOARD') ? _AM_VISION2026_DASHBOARD : 'Dashboard',
    'link'  => 'admin/index.php',
    'icon'  => 'home.png',
];

// Articles
$adminmenu[++$i] = [
    'title' => defined('_AM_VISION2026_ARTICLES') ? _AM_VISION2026_ARTICLES : 'Articles',
    'link'  => 'admin/articles.php',
    'icon'  => 'content.png',
];

// Categories
$adminmenu[++$i] = [
    'title' => defined('_AM_VISION2026_CATEGORIES') ? _AM_VISION2026_CATEGORIES : 'Categories',
    'link'  => 'admin/categories.php',
    'icon'  => 'category.png',
];

// About
$adminmenu[++$i] = [
    'title' => defined('_AM_VISION2026_ABOUT') ? _AM_VISION2026_ABOUT : 'About',
    'link'  => 'admin/about.php',
    'icon'  => 'about.png',
];
