# Changelog

All notable changes to the Vision 2026 module will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned
- Database migrations for production use
- Full admin CRUD interface
- Search functionality
- Category support
- Comment system integration

---

## [1.0.0] - 2026-02-01

### Added

#### Core Architecture
- Clean Architecture implementation with four layers (Domain, Application, Infrastructure, Presentation)
- Domain-Driven Design patterns throughout
- PSR-4 autoloading via Composer

#### Domain Layer
- `Article` entity as aggregate root with factory methods (`create()`, `reconstitute()`)
- `ArticleStatus` PHP 8.1 enum with state machine rules
- Value Objects:
  - `ArticleId` - ULID-based unique identifiers
  - `ArticleTitle` - Self-validating title (3-255 characters)
  - `ArticleSlug` - URL-safe slug with auto-generation from title
  - `ArticleContent` - Content with word count and reading time calculation
  - `AuthorId` - Author identifier
- Domain Events:
  - `ArticleCreated`
  - `ArticlePublished`
- Repository interface (`ArticleRepositoryInterface`)
- Custom exceptions (`ArticleNotFoundException`)

#### Application Layer
- Command/Handler pattern (CQRS-lite):
  - `CreateArticle` command
  - `CreateArticleHandler`
  - `PublishArticle` command
  - `PublishArticleHandler`
- Event dispatcher interface

#### Infrastructure Layer
- `ServiceContainer` for dependency injection
- `SimpleEventDispatcher` for domain events
- `DemoDataProvider` with 5 sample articles
- Demo mode configuration (`config/demo.php`)

#### Presentation Layer
- Frontend pages:
  - `index.php` - Article listing with demo mode support
  - `article.php` - Single article display
- Admin dashboard with article counts
- XOOPS integration (`xoops_version.php`, admin menu)

#### Testing
- PHPUnit test suite
- Unit tests for Domain layer:
  - `ArticleTest` - Entity creation and behavior
  - `ArticleIdTest` - ULID generation and validation
  - `ArticleTitleTest` - Title validation rules
- Tests run without XOOPS or database dependencies

#### Documentation
- `README.md` - Getting started guide with Mermaid architecture diagram
- `CLAUDE.md` - AI assistant implementation guide with Mermaid diagrams
- `docs/WALKTHROUGH.md` - Step-by-step code flow explanation
- `docs/COMPARISON.md` - Traditional XOOPS vs Vision 2026 comparison
- `docs/comparison-chart.html` - Interactive HTML comparison
- `docs/index.html` - Documentation landing page
- `docs/Vision2026-Presentation.pptx` - Conference presentation (9 slides)

### Technical Details

#### PHP Requirements
- PHP 8.1+ required
- Uses readonly classes, enums, named arguments
- Strict typing throughout

#### Dependencies
- Composer for autoloading
- PHPUnit for testing
- No external runtime dependencies in Domain layer

#### XOOPS Integration
- Compatible with XOOPS 2.5.x and 2026
- Uses traditional XOOPS bootstrap (mainfile.php, header.php)
- Smarty templates for frontend
- Standard admin menu integration

---

## Version History

| Version | Date | Description |
|---------|------|-------------|
| 1.0.0 | 2026-02-01 | Initial release with Clean Architecture implementation |

---

## Migration Notes

### From Traditional XOOPS Modules

If you're migrating from a traditional XOOPS module:

1. **Start with the Domain layer** - Define your entities and value objects first
2. **Add validation to Value Objects** - Move scattered validation into `fromString()` methods
3. **Create repository interfaces** - Define what your domain needs, not how to get it
4. **Implement infrastructure last** - Only then write the database code

See [docs/COMPARISON.md](docs/COMPARISON.md) for detailed migration examples.

---

## Contributing

When contributing, please:

1. Update this CHANGELOG with your changes under `[Unreleased]`
2. Follow the existing code patterns
3. Add unit tests for Domain layer changes
4. Ensure all tests pass (`composer test`)

---

*Vision 2026 — Building the future of XOOPS, one clean module at a time.*
