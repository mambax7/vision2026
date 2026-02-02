# XOOPS Module Conventions & Industry Standards

> **Convention over Configuration**: Standardized file naming and structure across all XOOPS modules

## Table of Contents

1. [Directory Structure](#directory-structure)
2. [File Naming Conventions](#file-naming-conventions)
3. [CSS/JS Asset Standards](#cssjs-asset-standards)
4. [Template Naming](#template-naming)
5. [PHP Class Naming](#php-class-naming)
6. [Testing Standards](#testing-standards)
7. [Configuration Files](#configuration-files)

---

## Directory Structure

### Standard Module Structure

```
modulename/
├── admin/                  # Admin backend
│   ├── index.php          # Dashboard
│   ├── menu.php           # Admin menu definition
│   └── *.php              # Other admin controllers
├── assets/                 # Frontend assets (CSS, JS, images)
│   ├── css/
│   │   └── styles.css     # Main stylesheet (STANDARD NAME)
│   ├── js/
│   │   └── scripts.js     # Main JavaScript (STANDARD NAME)
│   └── images/
├── blocks/                 # Block definitions
├── class/                  # Legacy XOOPS classes (if needed)
├── config/                 # Configuration files
│   └── demo.php           # Demo mode config
├── docs/                   # Documentation
├── include/                # Include files (search, functions)
├── language/               # Language files
│   └── english/
│       ├── admin.php      # Admin language
│       ├── blocks.php     # Block language
│       ├── main.php       # Frontend language
│       └── modinfo.php    # Module info language
├── preloads/              # XOOPS preload hooks
├── src/                   # Clean Architecture source code
│   ├── Application/       # Application layer (commands, handlers)
│   ├── Domain/            # Domain layer (entities, value objects)
│   └── Infrastructure/    # Infrastructure layer (persistence, services)
├── sql/                   # SQL installation files
│   └── mysql.sql
├── templates/             # Smarty templates
│   ├── admin/            # Admin templates
│   └── *.tpl             # Frontend templates
├── tests/                 # PHPUnit tests
│   ├── Unit/
│   └── Integration/
├── vendor/                # Composer dependencies (gitignored)
├── composer.json          # Composer dependencies
├── phpunit.xml           # PHPUnit configuration
├── README.md             # Module documentation
├── CHANGELOG.md          # Version history
└── xoops_version.php     # Module manifest
```

---

## File Naming Conventions

### PHP Files

**Convention**: `lowercase_with_underscores.php` (snake_case)

```
✅ article.php
✅ index.php
✅ submit_comment.php
❌ Article.php
❌ submitComment.php
```

**Exception**: PSR-4 classes in `src/` directory use **PascalCase**:

```
✅ Article.php
✅ ArticleRepository.php
✅ CreateArticleHandler.php
```

### Templates

**Convention**: `modulename_templatename.tpl`

```
✅ vision2026_index.tpl
✅ vision2026_article.tpl
✅ vision2026_admin_articles_list.tpl
```

**Why?**: Templates are stored in database with `db:` prefix. Module prefix prevents conflicts.

---

## CSS/JS Asset Standards

### Primary Assets (Convention over Configuration)

These files should exist in **EVERY** module for consistency:

```
assets/
├── css/
│   └── styles.css         # REQUIRED: Main stylesheet
├── js/
│   └── scripts.js         # Main JavaScript file (if needed)
└── images/
    └── logo.png           # Module icon/logo
```

### Additional CSS Files (Optional)

If you need additional stylesheets, use descriptive names:

```
assets/css/
├── styles.css             # Main (required)
├── admin.css              # Admin-specific styles
├── blocks.css             # Block-specific styles
└── print.css              # Print styles
```

### Loading Assets in Templates

**Standard Pattern**:

```smarty
{* Always use XOOPS constants for paths *}
<link rel="stylesheet" href="<{$xoops_url}>/modules/<{$xoops_dirname}>/assets/css/styles.css">
<script src="<{$xoops_url}>/modules/<{$xoops_dirname}>/assets/js/scripts.js"></script>
```

**Why?**:
- Works regardless of XOOPS installation directory
- Supports subdomain installations
- `$xoops_dirname` ensures multi-instance compatibility

### CSS Organization

Follow **BEM methodology** (Block Element Modifier):

```css
/* Block */
.article-card { }

/* Element */
.article-card__title { }
.article-card__meta { }

/* Modifier */
.article-card--featured { }
```

Or use **semantic component naming**:

```css
/* Component-based (used in Vision 2026) */
.vision2026-index { }
.article-list { }
.article-card { }
.demo-banner { }
```

---

## Template Naming

### Frontend Templates

**Pattern**: `modulename_pagename.tpl`

```
vision2026_index.tpl         # Main listing page
vision2026_article.tpl       # Single article view
vision2026_submit.tpl        # Submission form
vision2026_category.tpl      # Category view
```

### Admin Templates

**Pattern**: `modulename_admin_section_action.tpl`

```
vision2026_admin_articles_list.tpl      # Article listing
vision2026_admin_articles_form.tpl      # Article form
vision2026_admin_categories_list.tpl    # Category listing
vision2026_admin_categories_form.tpl    # Category form
```

### Block Templates

**Pattern**: `modulename_block_blockname.tpl`

```
vision2026_block_recent.tpl          # Recent articles block
vision2026_block_popular.tpl         # Popular articles block
```

---

## PHP Class Naming

### PSR-4 Autoloading

**Namespace matches directory structure**:

```php
// File: src/Domain/Entity/Article.php
namespace ModuleName\Domain\Entity;

class Article { }
```

### Naming Patterns

| Type | Pattern | Example |
|------|---------|---------|
| Entity | PascalCase noun | `Article`, `Category`, `User` |
| Value Object | PascalCase with suffix | `ArticleId`, `ArticleTitle`, `Slug` |
| Repository Interface | PascalCase + Interface | `ArticleRepositoryInterface` |
| Repository Implementation | Technology + Class + Repository | `XoopsArticleRepository` |
| Command | PascalCase verb | `CreateArticle`, `PublishArticle` |
| Handler | Command name + Handler | `CreateArticleHandler` |
| Mapper | Class + Mapper | `ArticleMapper` |
| Enum | PascalCase noun | `ArticleStatus`, `UserRole` |

---

## Testing Standards

### Test Structure

```
tests/
├── Unit/                   # Fast, isolated tests (no database)
│   ├── Domain/
│   │   ├── Entity/
│   │   │   ├── ArticleTest.php
│   │   │   └── ArticleStatusTest.php
│   │   └── ValueObject/
│   │       ├── ArticleIdTest.php
│   │       ├── ArticleTitleTest.php
│   │       ├── ArticleContentTest.php
│   │       └── ArticleSlugTest.php
│   ├── Application/
│   │   └── Command/
│   │       ├── CreateArticleHandlerTest.php
│   │       └── PublishArticleHandlerTest.php
│   └── Infrastructure/
│       └── Persistence/
│           └── ArticleMapperTest.php
├── Integration/            # Tests with dependencies (database, etc.)
│   └── Infrastructure/
│       └── Persistence/
│           └── XoopsArticleRepositoryTest.php
└── Functional/             # End-to-end tests
    └── ArticleSubmissionTest.php
```

### Test Naming Convention

**Pattern**: `ClassNameTest.php`

```
Article.php → ArticleTest.php
ArticleId.php → ArticleIdTest.php
CreateArticleHandler.php → CreateArticleHandlerTest.php
```

### Test Method Naming

**Pattern**: `test_methodName_scenario_expectedResult()`

```php
public function test_create_withValidData_returnsArticle(): void
public function test_publish_whenDraft_transitionsToPublished(): void
public function test_fromString_withEmptyTitle_throwsException(): void
```

### What Gets Tested?

**Vision 2026 Testing Strategy**:

1. **Domain Layer (100% coverage)**:
   - All Entity methods
   - All Value Object validation
   - All business logic
   - State transitions

2. **Application Layer (80% coverage)**:
   - Command handlers
   - Event dispatchers
   - Application services

3. **Infrastructure Layer (50% coverage)**:
   - Critical repository methods
   - Mapper transformations
   - Integration points

4. **Presentation Layer (minimal)**:
   - Controllers tested via functional tests
   - Templates tested manually

### Complete Test Suite

**Vision 2026 now includes 9 comprehensive test files**:

#### Domain Layer Tests (6 files):
1. **`ArticleTest.php`** (247 lines) - Core entity business logic
   - Entity creation and factory methods
   - Status transitions (Draft → Published → Archived → Draft)
   - Domain events (ArticleCreated, ArticlePublished)
   - Business rules and validation
   - Ownership checks

2. **`ArticleStatusTest.php`** (152 lines) - Enum state machine
   - All valid state transitions
   - Editability rules per status
   - String conversion (toString/fromString)
   - Enum equality and cases

3. **`ArticleIdTest.php`** - ULID identifier validation
   - ID generation and uniqueness
   - String conversion and equality

4. **`ArticleTitleTest.php`** - Title validation
   - Length constraints (3-255 characters)
   - Whitespace handling
   - Invalid input rejection

5. **`ArticleContentTest.php`** (286 lines) - Content validation and utilities
   - Content length validation (minimum 50 chars)
   - Word count calculation
   - Reading time estimation (200 words/min)
   - Excerpt generation with word boundary breaking
   - Unicode and special character handling

6. **`ArticleSlugTest.php`** (287 lines) - URL-safe slug validation
   - Generation from titles (automatic slugification)
   - URL-safe format validation (lowercase, numbers, hyphens)
   - Edge case handling (consecutive hyphens, start/end validation)
   - Special character transliteration

#### Application Layer Tests (2 files):
7. **`CreateArticleHandlerTest.php`** (267 lines) - Article creation command
   - Command handling flow
   - Repository interaction
   - Event dispatching
   - Multiple execution scenarios

8. **`PublishArticleHandlerTest.php`** (281 lines) - Article publishing command
   - Publishing logic and state changes
   - Error handling (not found, invalid transitions)
   - Event dispatching
   - Timestamp management

#### Infrastructure Layer Tests (1 file):
9. **`ArticleMapperTest.php`** (307 lines) - Entity ↔ Row conversion
   - Entity-to-row mapping (all statuses)
   - Row-to-entity reconstruction
   - Round-trip data preservation
   - Timestamp conversion (Unix ↔ DateTimeImmutable)
   - Edge cases and special characters

**Total: 9 test files with 1,827+ lines of test code**

**Coverage Strategy**:
- ✅ **Domain Layer**: 100% coverage of critical business logic
- ✅ **Application Layer**: Command handlers fully tested
- ✅ **Infrastructure Layer**: Mapper tested, repository ready for integration tests
- ⚪ **Integration Tests**: Can be added when testing against real database
- ⚪ **Functional Tests**: Can be added for end-to-end scenarios

**Philosophy**: Comprehensive testing of the core domain first, then expand outward.

---

## Configuration Files

### Standard Config Files

```
config/
├── demo.php              # Demo mode configuration
├── defaults.php          # Default module settings
└── routes.php            # URL routing (if implemented)
```

### Environment-Specific Files

**Never commit sensitive data**:

```
.gitignore should include:
vendor/
.env
config/local.php
```

---

## Language File Conventions

### Constant Naming

**Pattern**: `_LAYER_MODULENAME_CONTEXT_ITEM`

**Layers**:
- `_MI_` = Module Info (xoops_version.php)
- `_MA_` = Module Admin (admin/*)
- `_MB_` = Module Blocks (blocks/*)
- `_MD_` = Module Data/Frontend (index.php, article.php)

**Examples**:

```php
// modinfo.php
define('_MI_VISION2026_NAME', 'Vision 2026');
define('_MI_VISION2026_DESC', 'Reference implementation...');
define('_MI_VISION2026_ARTICLES', 'Articles');
define('_MI_VISION2026_DEMO', 'Demo');

// admin.php
define('_AM_VISION2026_ARTICLE_CREATED', 'Article created successfully');
define('_AM_VISION2026_CONFIRM_DELETE', 'Are you sure you want to delete?');

// main.php
define('_MD_VISION2026_READMORE', 'Read more');
define('_MD_VISION2026_POSTED_BY', 'Posted by %s');

// blocks.php
define('_MB_VISION2026_RECENT_LIMIT', 'Number of articles to display');
```

---

## Industry Standards Reference

### PHP Standards (PSR)

- **PSR-1**: Basic Coding Standard
- **PSR-4**: Autoloading Standard
- **PSR-12**: Extended Coding Style Guide

### CSS Methodologies

- **BEM**: Block Element Modifier
- **SMACSS**: Scalable and Modular Architecture for CSS
- **OOCSS**: Object-Oriented CSS

### JavaScript Standards

- **ES6+**: Modern JavaScript syntax
- **Module pattern**: Encapsulation
- **JSDoc**: Documentation comments

### Documentation

- **Markdown**: All documentation files
- **PHPDoc**: PHP code comments
- **JSDoc**: JavaScript comments

---

## Quick Reference

### Must-Have Files (Minimum)

```
✅ xoops_version.php
✅ index.php
✅ composer.json
✅ README.md
✅ assets/css/styles.css
✅ language/english/modinfo.php
✅ language/english/main.php
✅ templates/modulename_index.tpl
```

### Recommended Files

```
✅ CHANGELOG.md
✅ CLAUDE.md (for AI context)
✅ phpunit.xml
✅ .gitignore
✅ tests/Unit/Domain/...
```

### Optional Files

```
⚪ docs/WALKTHROUGH.md
⚪ docs/COMPARISON.md
⚪ docs/API.md
⚪ preloads/...
⚪ blocks/...
```

---

## Vision 2026 Compliance Checklist

- ✅ **CSS**: `assets/css/styles.css` (standard name)
- ✅ **Templates**: Module-prefixed names
- ✅ **Tests**: Domain layer coverage
- ✅ **PSR-4**: Proper autoloading
- ✅ **Separation**: HTML in templates, CSS in stylesheets
- ✅ **Documentation**: Clear, comprehensive
- ✅ **Demo Mode**: Educational sample data
- ✅ **Clean Architecture**: Proper layer separation

---

**Remember**: Convention over Configuration means less time deciding, more time building! 🚀
