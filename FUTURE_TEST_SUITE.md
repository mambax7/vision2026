# Future Test Suite - Vision 2026

> **Blueprint tests representing the ideal implementation based on Cowork's specification**

## Purpose

This directory contains comprehensive tests based on the original Vision 2026 specification document (`Testing.md` from Cowork). These tests represent the **target architecture** and serve as:

1. **Implementation Blueprint** - Shows how features should work when fully implemented
2. **Documentation** - Demonstrates best practices and expected behavior
3. **Roadmap** - Guides future development toward the specification
4. **Educational Resource** - Teaches Clean Architecture testing patterns

## Status: 📋 Specification-Based (Not Yet Passing)

These tests are written against the **ideal future state** described in the original specification. They currently fail because:

- Some interfaces/methods don't exist yet (`ArticleStatus::fromString()`)
- Error messages differ from specification
- Method signatures are simplified in current implementation
- Some features are planned but not yet implemented

## Test Files (9 files, 2,596 lines)

### Domain Layer Tests
```
tests_future/Unit/Domain/
├── Entity/
│   ├── ArticleTest.php               ✅ PASSES (Current implementation)
│   └── ArticleStatusTest.php         📋 Future (missing fromString method)
└── ValueObject/
    ├── ArticleIdTest.php             ✅ PASSES (Current implementation)
    ├── ArticleTitleTest.php          ✅ PASSES (Current implementation)
    ├── ArticleContentTest.php        📋 Future (different error messages)
    └── ArticleSlugTest.php           📋 Future (simplified validation)
```

### Application Layer Tests
```
tests_future/Unit/Application/
└── Command/
    ├── CreateArticleHandlerTest.php  📋 Future (different signatures)
    └── PublishArticleHandlerTest.php 📋 Future (different return types)
```

### Infrastructure Layer Tests
```
tests_future/Unit/Infrastructure/
└── Persistence/
    └── ArticleMapperTest.php         📋 Future (needs implementation updates)
```

## Current Working Tests

The **3 original tests** that work with current implementation are in `/tests/Unit/`:

✅ `ArticleTest.php` - Core entity business logic
✅ `ArticleIdTest.php` - ULID identifier validation
✅ `ArticleTitleTest.php` - Title validation rules

These tests pass **all assertions** and provide solid coverage of critical domain logic.

## Implementation Differences

### 1. Event Dispatcher Path
**Specification**: `Vision2026\Application\EventDispatcher\EventDispatcherInterface`
**Current**: `Vision2026\Application\Event\EventDispatcherInterface`

### 2. Event Dispatching
**Specification**: Dispatch array of events
**Current**: Dispatch events one at a time in a loop

### 3. Handler Return Types
**Specification**: Returns `Article` entity
**Current**: Returns `ArticleId` value object

### 4. Error Messages
**Specification**: Detailed messages like "Article content cannot be empty"
**Current**: Generic messages like "Content must be at least 50 characters, got 0"

### 5. Validation Methods
**Specification**: `ArticleStatus::fromString()` for string conversion
**Current**: Not yet implemented

### 6. Slug Validation
**Specification**: Strict validation with detailed error messages
**Current**: Simple regex validation with generic error message

## Migration Path

To make these tests pass, you would need to:

### Phase 1: Align Error Messages
- Update `ArticleContent` validation messages
- Update `ArticleSlug` validation messages
- Add specific error messages for each validation rule

### Phase 2: Add Missing Methods
- Implement `ArticleStatus::fromString()`
- Implement `ArticleStatus::toString()`
- Add excerpt generation to `ArticleContent`
- Add reading time calculation to `ArticleContent`
- Add word count to `ArticleContent`

### Phase 3: Enhance Validation
- Add stricter slug validation (consecutive hyphens, start/end checks)
- Add length validation to all value objects
- Add specific error types for different validation failures

### Phase 4: Refine Handlers
- Consider changing return types if beneficial
- Align event dispatching patterns
- Add comprehensive error handling

## Usage

### Run Future Tests (will fail)
```bash
/Applications/MAMP/bin/php/php8.3.28/bin/php vendor/bin/phpunit tests_future/
```

### Use as Reference
When implementing new features, refer to these tests to understand:
- Expected method signatures
- Validation rules and error messages
- Business logic behavior
- Testing best practices

## Value of These Tests

Even though they don't pass yet, these tests are **extremely valuable** because:

1. **Clear Specification** - Shows exactly what behavior is expected
2. **Quality Bar** - Demonstrates high standards for error messages and validation
3. **Educational** - Teaches proper testing techniques (data providers, edge cases, etc.)
4. **Future-Proof** - When implementing features, you have clear acceptance criteria

## Test Statistics

| Metric | Count |
|--------|-------|
| Total Test Files | 9 |
| Total Lines | 2,596 |
| Test Methods | 150+ |
| Data Providers | 8 |
| Edge Cases Covered | 80+ |

## Recommendation

**Keep these tests** as part of the repository. They serve as:
- ✅ Documentation of ideal implementation
- ✅ Roadmap for future enhancements
- ✅ Educational resource for developers
- ✅ Quality standards reference

When you implement new features or refactor existing ones, gradually move passing tests from `tests_future/` to `tests/`.

---

**Status**: Blueprint / Specification
**Created**: 2026-02-02
**Based On**: Cowork's Vision 2026 Testing.md specification
**Current Passing Tests**: 3/9 files (in /tests/ directory)
