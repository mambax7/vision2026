# Vision 2026 Module Database Schema
# XOOPS 2026 Reference Implementation
#
# Uses ULID identifiers for articles (time-sortable, globally unique)
# Standard auto-increment for supporting tables

# =====================================================
# Articles - Core content entity
# =====================================================
CREATE TABLE vision2026_articles (
    `id` CHAR(26) NOT NULL COMMENT 'ULID identifier',
    `title` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(220) NOT NULL,
    `summary` TEXT NULL COMMENT 'Optional excerpt/summary',
    `content` MEDIUMTEXT NOT NULL,
    `content_format` VARCHAR(20) NOT NULL DEFAULT 'html' COMMENT 'html, markdown, etc.',
    `status` ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
    `author_id` INT UNSIGNED NOT NULL COMMENT 'XOOPS user ID',
    `category_id` INT UNSIGNED NULL,
    `featured_image` VARCHAR(255) NULL,
    `meta_title` VARCHAR(100) NULL,
    `meta_description` VARCHAR(200) NULL,
    `views` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NULL,
    `published_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_slug` (`slug`),
    KEY `idx_status` (`status`),
    KEY `idx_author` (`author_id`),
    KEY `idx_category` (`category_id`),
    KEY `idx_published_at` (`published_at`),
    KEY `idx_status_published` (`status`, `published_at`)
) ENGINE=InnoDB ;

# =====================================================
# Categories - Article organization
# =====================================================
CREATE TABLE vision2026_categories (
    `id` INT UNSIGNED AUTO_INCREMENT,
    `parent_id` INT UNSIGNED NULL COMMENT 'For nested categories',
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(110) NOT NULL,
    `description` TEXT NULL,
    `image` VARCHAR(255) NULL,
    `weight` INT NOT NULL DEFAULT 0 COMMENT 'Sort order',
    `article_count` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Denormalized count',
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_slug` (`slug`),
    KEY `idx_parent` (`parent_id`),
    KEY `idx_weight` (`weight`)
) ENGINE=InnoDB ;

# =====================================================
# Tags - Flexible article tagging
# =====================================================
CREATE TABLE vision2026_tags (
    `id` INT UNSIGNED AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(60) NOT NULL,
    `article_count` INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_slug` (`slug`)
) ENGINE=InnoDB ;

# =====================================================
# Article-Tag relationship (many-to-many)
# =====================================================
CREATE TABLE vision2026_article_tags (
    `article_id` CHAR(26) NOT NULL,
    `tag_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`article_id`, `tag_id`),
    KEY `idx_tag` (`tag_id`),
    KEY `idx_article` (`article_id`)
) ENGINE=InnoDB ;

# =====================================================
# Domain Events Log (for event sourcing / audit)
# =====================================================
CREATE TABLE vision2026_domain_events (
    `id` BIGINT UNSIGNED AUTO_INCREMENT,
    `aggregate_id` CHAR(26) NOT NULL COMMENT 'Article ULID',
    `aggregate_type` VARCHAR(50) NOT NULL DEFAULT 'article',
    `event_type` VARCHAR(100) NOT NULL COMMENT 'e.g., ArticlePublished',
    `event_data` JSON NOT NULL,
    `occurred_at` DATETIME(6) NOT NULL COMMENT 'Microsecond precision',
    `user_id` INT UNSIGNED NULL COMMENT 'Who triggered the event',
    PRIMARY KEY (`id`),
    KEY `idx_aggregate` (`aggregate_id`, `aggregate_type`),
    KEY `idx_event_type` (`event_type`),
    KEY `idx_occurred` (`occurred_at`)
) ENGINE=InnoDB ;

# =====================================================
# Initial data
# =====================================================
# INSERT INTO `vision2026_categories` (`name`, `slug`, `description`, `weight`, `created_at`) VALUES
# ('General', 'general', 'General articles and announcements', 0, NOW()),
# ('Tutorials', 'tutorials', 'How-to guides and tutorials', 1, NOW()),
# ('News', 'news', 'Latest news and updates', 2, NOW());
