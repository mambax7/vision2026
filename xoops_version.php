<?php

declare(strict_types=1);

/**
 * Vision 2026 Module - XOOPS 2026 Reference Implementation
 *
 * This module demonstrates modern PHP architecture patterns
 * within the XOOPS ecosystem.
 */

$modversion = [
    // Module info
    'name'           => _MI_VISION2026_NAME ?? 'Vision 2026',
    'version'        => '1.0.0',
    'description'    => _MI_VISION2026_DESC ?? 'Reference implementation showcasing XOOPS 2026 architecture',
    'author'         => 'XOOPS Team',
    'credits'        => 'XOOPS Community',
    'license'        => 'GPL-2.0+',
    'license_url'    => 'https://www.gnu.org/licenses/gpl-2.0.html',
    'official'       => 1,
    'image'          => 'assets/images/logoModule.png',
    'dirname'        => 'vision2026',

    // Requirements
    'min_php'        => '8.1',
    'min_xoops'      => '2.5.11',
    'min_admin'      => '1.2',
    'min_db'         => ['mysql' => '5.7', 'mysqli' => '5.7'],

    // Module icons
    'modicons16'     => 'assets/images/icons/16',
    'modicons32'     => 'assets/images/icons/32',

    // Database
    'sqlfile'        => ['mysql' => 'sql/mysql.sql'],
    'tables'         => [
        'vision2026_articles',
        'vision2026_categories',
    ],

    // Admin
    'hasAdmin'       => 1,
    'system_menu'    => 1,
    'adminindex'     => 'admin/index.php',
    'adminmenu'      => 'admin/menu.php',

    // Main
    'hasMain'        => 1,
    'sub'            => [
        [
            'name' => _MI_VISION2026_ARTICLES ?? 'Articles',
            'url'  => 'index.php',
        ],
        [
            'name' => _MI_VISION2026_CATEGORIES ?? 'Categories',
            'url'  => 'category.php',
        ],
        [
            'name' => _MI_VISION2026_DEMO ?? 'Demo',
            'url'  => 'index.php?demo=1',
        ],
    ],

    // Search
    'hasSearch'      => 1,
    'search'         => [
        'file' => 'include/search.inc.php',
        'func' => 'vision2026_search',
    ],

    // Comments
    'hasComments'    => 1,
    'comments'       => [
        'itemName' => 'article_id',
        'pageName' => 'article.php',
    ],

    // Notifications
    'hasNotification' => 1,
    'notification'    => [
        'lookup_file' => 'include/notification.inc.php',
        'lookup_func' => 'vision2026_notify_iteminfo',
        'category'    => [
            [
                'name'           => 'global',
                'title'          => _MI_VISION2026_NOTIFY_GLOBAL ?? 'Global',
                'description'    => _MI_VISION2026_NOTIFY_GLOBAL_DESC ?? 'Global notifications',
                'subscribe_from' => ['index.php'],
                'item_name'      => '',
                'allow_bookmark' => 0,
            ],
            [
                'name'           => 'article',
                'title'          => _MI_VISION2026_NOTIFY_ARTICLE ?? 'Article',
                'description'    => _MI_VISION2026_NOTIFY_ARTICLE_DESC ?? 'Article notifications',
                'subscribe_from' => ['article.php'],
                'item_name'      => 'article_id',
                'allow_bookmark' => 1,
            ],
        ],
        'event' => [
            [
                'name'          => 'new_article',
                'category'      => 'global',
                'title'         => _MI_VISION2026_NOTIFY_NEW ?? 'New Article',
                'caption'       => _MI_VISION2026_NOTIFY_NEW_CAP ?? 'Notify when new article published',
                'description'   => _MI_VISION2026_NOTIFY_NEW_DESC ?? '',
                'mail_template' => 'notify_new_article',
                'mail_subject'  => _MI_VISION2026_NOTIFY_NEW_SUBJECT ?? 'New article published',
            ],
        ],
    ],

    // Config options
    'config' => [
        [
            'name'        => 'articles_per_page',
            'title'       => '_MI_VISION2026_PERPAGE',
            'description' => '_MI_VISION2026_PERPAGE_DESC',
            'formtype'    => 'textbox',
            'valuetype'   => 'int',
            'default'     => 10,
        ],
        [
            'name'        => 'enable_comments',
            'title'       => '_MI_VISION2026_COMMENTS',
            'description' => '_MI_VISION2026_COMMENTS_DESC',
            'formtype'    => 'yesno',
            'valuetype'   => 'int',
            'default'     => 1,
        ],
    ],

    // Templates
    'templates' => [
        // Public templates
        ['file' => 'vision2026_index.tpl', 'description' => 'Article listing page'],
        ['file' => 'vision2026_article.tpl', 'description' => 'Single article display'],
        ['file' => 'vision2026_category.tpl', 'description' => 'Category listing'],

        // Admin templates
        ['file' => 'vision2026_admin_articles_list.tpl', 'description' => 'Admin: Article listing', 'type' => 'admin'],
        ['file' => 'vision2026_admin_articles_form.tpl', 'description' => 'Admin: Article form', 'type' => 'admin'],
        ['file' => 'vision2026_admin_categories_list.tpl', 'description' => 'Admin: Category listing', 'type' => 'admin'],
        ['file' => 'vision2026_admin_categories_form.tpl', 'description' => 'Admin: Category form', 'type' => 'admin'],
    ],

    // Blocks
    'blocks' => [
        [
            'file'        => 'blocks.php',
            'name'        => _MI_VISION2026_BLOCK_RECENT ?? 'Recent Articles',
            'description' => _MI_VISION2026_BLOCK_RECENT_DESC ?? 'Shows recent articles',
            'show_func'   => 'vision2026_block_recent',
            'edit_func'   => 'vision2026_block_recent_edit',
            'options'     => '5|0',
            'template'    => 'vision2026_block_recent.tpl',
        ],
    ],
];
