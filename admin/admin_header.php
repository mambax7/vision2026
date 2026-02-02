<?php

declare(strict_types=1);

/**
 * Vision 2026 Module - Admin Header
 *
 * Included at the top of all admin pages.
 * Loads language files and common dependencies.
 */

use Xmf\Module\Admin;

require_once dirname(__DIR__, 3) . '/include/cp_header.php';

// Load language files
$moduleDirName = basename(dirname(__DIR__));
xoops_loadLanguage('admin', $moduleDirName);
xoops_loadLanguage('modinfo', $moduleDirName);
xoops_loadLanguage('main', $moduleDirName);

// Initialize XMF Admin object (required for xoops_cp_footer)
$adminObject = Admin::getInstance();
