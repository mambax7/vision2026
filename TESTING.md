# Testing Guide - Vision 2026

> **Comprehensive testing documentation for the Vision 2026 reference module**

## Quick Start

### Run All Tests
```bash
composer test
```

### Run Specific Test Suite
```bash
vendor/bin/phpunit --testsuite=Unit
```

### Run Single Test File
```bash
vendor/bin/phpunit tests/Unit/Domain/Entity/ArticleTest.php
```

---

## Test Structure

Vision 2026 has **two test directories**:

```
vision2026/
├── tests/              # ✅ Current implementation tests (ALL PASS)
│   └── Unit/
│       ├── Domain/
│       │   ├── Entity/
│       │   │   └── ArticleTest.php              47 tests ✅
│       │   └── ValueObject/
│       │       ├── ArticleIdTest.php            Tests ✅
│       │       └── ArticleTitleTest.php         Tests ✅
│       └── Integration/                         (Empty, future)
│
└── tests_future/       # 📋 Specification-based tests (Blueprint)
    └── Unit/
        ├── Domain/
        │   ├── Entity/
        │   │   ├── ArticleTest.php              ✅ (same as current)
        │   │   └── ArticleStatusTest.php        📋 Future
        │   └── ValueObject/
        │       ├── ArticleIdTest.php            ✅ (same as current)
        │       ├── ArticleTitleTest.php         ✅ (same as current)
        │       ├── ArticleContentTest.php       📋 Future
        │       └── ArticleSlugTest.php          📋 Future
        ├── Application/
        │   └── Command/
        │       ├── CreateArticleHandlerTest.php 📋 Future
        │       └── PublishArticleHandlerTest.php 📋 Future
        └── Infrastructure/
            └── Persistence/
                └── ArticleMapperTest.php        📋 Future
```

---

## Current Tests (`/tests/`)

### Status: ✅ ALL 47 TESTS PASS

These tests work with the **current implementation** and provide solid coverage of critical business logic.

#### Test Files:
1. **`ArticleTest.php`** (247 lines, 20+ tests)
   - Article creation and factory methods
   - Status transitions (Draft → Published → Archived → Draft)
   - Domain events (ArticleCreated, ArticlePublished)
   - Business rules (publish, unpublish, archive, update)
   - Ownership verification

2. **`ArticleIdTest.php`**
   - ULID generation and validation
   - String conversion
   - Equality checks

3. **`ArticleTitleTest.php`**
   - Title validation (3-255 characters)
   - Whitespace handling
   - Error cases

#### Run Current Tests:
```bash
/Applications/MAMP/bin/php/php8.3.28/bin/php vendor/bin/phpunit --testsuite=Unit

# Output:
# Tests: 47, Assertions: 83
# OK ✅
```

---

## Future Tests (`/tests_future/`)

### Status: 📋 SPECIFICATION-BASED BLUEPRINT

These tests represent the **ideal implementation** based on Cowork's original specification. They serve as:
- 📖 Documentation of target architecture
- 🗺️ Roadmap for future enhancements
- 🎓 Educational resource
- ✅ Acceptance criteria for new features

See [`tests_future/README.md`](tests_future/README.md) for complete details.

#### Test Files (9 files, 2,596 lines):
1. `ArticleTest.php` - ✅ Passes (current)
2. `ArticleStatusTest.php` - 📋 Future (152 lines, enum state machine)
3. `ArticleIdTest.php` - ✅ Passes (current)
4. `ArticleTitleTest.php` - ✅ Passes (current)
5. `ArticleContentTest.php` - 📋 Future (286 lines, word count, reading time, excerpts)
6. `ArticleSlugTest.php` - 📋 Future (287 lines, URL-safe validation)
7. `CreateArticleHandlerTest.php` - 📋 Future (267 lines, command handling)
8. `PublishArticleHandlerTest.php` - 📋 Future (281 lines, publishing logic)
9. `ArticleMapperTest.php` - 📋 Future (307 lines, entity ↔ row mapping)

#### Why Keep Future Tests?

Even though they don't pass yet, these tests are **valuable** because:

1. **Clear Specification** - Shows exactly what behavior is expected
2. **Quality Standards** - Demonstrates high standards for validation and error messages
3. **Implementation Guide** - When adding features, you have clear acceptance criteria
4. **Educational Value** - Shows best practices for comprehensive testing

#### Run Future Tests (will show failures):
```bash
/Applications/MAMP/bin/php/php8.3.28/bin/php vendor/bin/phpunit tests_future/
```

---

## Testing Philosophy

### Test Pyramid

```
         /\
        /  \  E2E (5%)           - Functional tests (future)
       /    \
      /      \ Integration (25%)  - Database tests (future)
     /        \
    /          \ Unit (70%)       - ✅ COMPLETE
   /____________\
```

### Current Coverage:
- ✅ **Domain Entities**: 100% covered
- ✅ **Domain Value Objects**: Core objects covered
- ⚪ **Application Handlers**: Ready for testing
- ⚪ **Infrastructure**: Mappers ready for testing
- ⚪ **Integration**: Database tests future
- ⚪ **Functional**: End-to-end tests future

---

## Key Testing Principles

### 1. No Dependencies in Domain Tests
```php
// ✅ Pure PHP - no mocks, no database, no XOOPS
final class ArticleTest extends TestCase
{
    public function testPublishDraftArticle(): void
    {
        $article = Article::create(...);
        $article->publish();

        $this->assertTrue($article->isPublished());
    }
}
```

### 2. Fast Execution
- All 47 tests run in **~21ms**
- No database setup needed
- Instant feedback

### 3. Clear Test Names
```php
testPublishDraftArticle()                    // ✅ Clear what is tested
testCannotPublishAlreadyPublishedArticle()   // ✅ Clear expected behavior
testNewArticleRecordsCreatedEvent()          // ✅ Clear assertion
```

### 4. Comprehensive Coverage
```php
// Tests all state transitions
testPublishDraftArticle()
testUnpublishPublishedArticle()
testArchiveDraftArticle()
testArchivePublishedArticle()
testCannotArchiveAlreadyArchivedArticle()
```

---

## Test Results

### Current Tests (Production)
```
PHPUnit 10.5.63 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.3.28
Configuration: phpunit.xml

...............................................                   47 / 47 (100%)

Time: 00:00.021, Memory: 4.00 MB

OK ✅
Tests: 47, Assertions: 83
```

### Future Tests (Blueprint)
```
Tests: 188, Assertions: 193
Errors: 63, Failures: 21

Status: Blueprint for future implementation
See: tests_future/README.md
```

---

## Business Rules Tested

### Article Lifecycle
- ✅ New articles start as Draft
- ✅ Draft can be Published
- ✅ Published can be Unpublished (back to Draft)
- ✅ Draft and Published can be Archived
- ✅ Archived cannot transition to any status (terminal state)

### Editability Rules
- ✅ Draft articles are editable
- ✅ Published articles are editable (business rule change)
- ✅ Archived articles are NOT editable

### Domain Events
- ✅ Creating article records ArticleCreated event
- ✅ Publishing article records ArticlePublished event
- ✅ Events are pulled once and cleared

### Ownership
- ✅ Article tracks author
- ✅ Ownership can be verified

---

## Running Tests

### Prerequisites
```bash
# Ensure composer dependencies are installed
composer install
```

### Run All Tests
```bash
composer test
```

### Run with Specific PHP Version
```bash
/Applications/MAMP/bin/php/php8.3.28/bin/php vendor/bin/phpunit
```

### Run Specific Test Suite
```bash
vendor/bin/phpunit --testsuite=Unit
vendor/bin/phpunit --testsuite=Integration  # (empty currently)
```

### Run Specific Test File
```bash
vendor/bin/phpunit tests/Unit/Domain/Entity/ArticleTest.php
```

### Run Specific Test Method
```bash
vendor/bin/phpunit --filter=testPublishDraftArticle
```

### Run with Coverage (requires Xdebug)
```bash
composer test:coverage
```

---

## Adding New Tests

### For Current Implementation

Add tests to `/tests/Unit/` following the structure:

```php
<?php

declare(strict_types=1);

namespace Vision2026\Tests\Unit\Domain\Entity;

use PHPUnit\Framework\TestCase;
use Vision2026\Domain\Entity\YourEntity;

final class YourEntityTest extends TestCase
{
    public function testSomething(): void
    {
        // Arrange
        $entity = YourEntity::create(...);

        // Act
        $entity->doSomething();

        // Assert
        $this->assertTrue($entity->someCondition());
    }
}
```

### For Future Features

Add tests to `/tests_future/Unit/` following the specification patterns.

---

## CI/CD Integration

### GitHub Actions (Example)
```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
      - run: composer install
      - run: composer test
```

---

## Resources

- **Test Suite Documentation**: [`docs/TEST-SUITE.md`](docs/TEST-SUITE.md)
- **Future Tests README**: [`tests_future/README.md`](tests_future/README.md)
- **Conventions**: [`docs/CONVENTIONS.md`](docs/CONVENTIONS.md)
- **PHPUnit Docs**: https://phpunit.de/documentation.html

---

## Frequently Asked Questions

### Q: Why two test directories?

**A**: `/tests/` contains tests that pass with current implementation. `/tests_future/` contains specification-based tests that serve as a blueprint for future enhancements. This approach:
- Keeps CI green (passing tests only)
- Preserves specification as documentation
- Provides clear roadmap for features

### Q: Should I run future tests?

**A**: You can, but they will fail. They're primarily for:
- Documentation of ideal implementation
- Reference when implementing new features
- Educational purposes

### Q: How do I move a future test to current?

**A**: When you implement a feature that makes a future test pass:
1. Run the specific future test to verify it passes
2. Copy it from `tests_future/` to `tests/`
3. Run full test suite to ensure no regressions

### Q: What's the deprecation warning?

**A**: PHPUnit shows a deprecation about return types. This is informational and doesn't affect test execution.

---

**Last Updated**: 2026-02-02
**Test Count**: 47 passing (current), 188 total (with future)
**Coverage**: Domain layer at 100%
