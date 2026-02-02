# The XOOPS 2026 Vision

> *"The best code is code you're proud to show others."*

---

## Why This Module Exists

For over 20 years, XOOPS has powered websites around the world. But PHP has evolved dramatically—from PHP 4's procedural scripts to PHP 8's modern object-oriented features. XOOPS 2026 represents our commitment to evolving with the language.

The **Vision 2026 Module** is not just documentation. It's a **working prototype** that demonstrates:

1. **What's possible** with modern PHP in XOOPS
2. **How it feels** to develop with clean architecture
3. **Why it matters** for the future of XOOPS modules

---

## The Problem We're Solving

Traditional XOOPS module development has challenges:

```php
// Traditional approach - business logic mixed with data access
class ArticleHandler extends XoopsPersistableObjectHandler
{
    public function publish($id)
    {
        $article = $this->get($id);
        $article->setVar('status', 'published');
        $article->setVar('published_at', time());
        $this->insert($article);

        // Send notification... but where?
        // Validation... did we check permissions?
        // What if we need to test this?
    }
}
```

**Issues:**
- Business logic scattered across handlers, controllers, templates
- Hard to test without a database
- Difficult to understand what "publishing" actually means
- Changes ripple unpredictably

---

## The Vision 2026 Approach

```php
// Domain-driven approach - clear, testable, maintainable

final class Article extends AggregateRoot
{
    public function publish(): void
    {
        // Business rules are HERE, in the entity
        if ($this->status !== ArticleStatus::Draft) {
            throw new InvalidStatusTransition('Only drafts can be published');
        }

        $this->status = ArticleStatus::Published;
        $this->publishedAt = new \DateTimeImmutable();

        // Domain event - decoupled notification
        $this->recordEvent(new ArticlePublished($this->id));
    }
}

// The handler just orchestrates
final class PublishArticleHandler
{
    public function handle(PublishArticle $command): void
    {
        $article = $this->articles->findOrFail($command->articleId);
        $article->publish();  // All logic is in the entity
        $this->articles->save($article);

        // Events dispatched automatically
    }
}
```

**Benefits:**
- Business rules in one place
- Easy to test: `$article->publish()` - no database needed
- Clear what "publish" means
- Events decouple notifications from core logic

---

## Core Principles

### 1. Domain at the Center

The domain layer contains **pure PHP**—no XOOPS, no database, no HTTP. Just business logic.

```
┌────────────────────────────────────────────┐
│              Framework / HTTP              │
│  ┌──────────────────────────────────────┐  │
│  │           Application Layer          │  │
│  │  ┌────────────────────────────────┐  │  │
│  │  │        Domain Layer            │  │  │
│  │  │    (Pure Business Logic)       │  │  │
│  │  └────────────────────────────────┘  │  │
│  └──────────────────────────────────────┘  │
└────────────────────────────────────────────┘
```

### 2. Dependency Inversion

The domain defines **what it needs** (interfaces). Infrastructure provides **how it's done**.

```php
// Domain says "I need a way to store articles"
interface ArticleRepositoryInterface
{
    public function save(Article $article): void;
}

// Infrastructure says "Here's how, using XOOPS database"
class XoopsArticleRepository implements ArticleRepositoryInterface
{
    public function save(Article $article): void
    {
        // XOOPS-specific implementation
    }
}
```

### 3. Value Objects for Correctness

Instead of primitive strings, we use objects that **guarantee validity**:

```php
// Traditional - anything goes
$title = $_POST['title'];  // Could be empty, 10000 chars, SQL injection...

// Vision 2026 - invalid states are impossible
$title = ArticleTitle::fromString($_POST['title']);
// Throws exception if < 3 chars or > 200 chars
// Once created, you KNOW it's valid
```

### 4. Events for Extension

Instead of hardcoding what happens when an article is published:

```php
// Domain raises the event
$article->recordEvent(new ArticlePublished($this->id));

// Listeners react (separately configured)
class SendNotificationOnPublish
{
    public function handle(ArticlePublished $event): void
    {
        // Send email, Slack, push notification...
    }
}

class UpdateSearchIndexOnPublish
{
    public function handle(ArticlePublished $event): void
    {
        // Update Elasticsearch, Algolia...
    }
}
```

---

## What You'll See in This Module

### Clean Entity Design
- `Article` entity with encapsulated state transitions
- `ArticleStatus` enum with allowed transition rules
- Domain events recorded during operations

### Immutable Value Objects
- `ArticleId` - ULID-based, globally unique, time-sortable
- `ArticleTitle` - validated on creation
- `ArticleSlug` - generated from title, URL-safe
- `ArticleContent` - with format tracking

### Repository Pattern
- Interface in domain layer (no database knowledge)
- Implementation in infrastructure (XOOPS database)
- Mapper for domain ↔ persistence translation

### Command/Query Separation
- Commands: `CreateArticle`, `PublishArticle`, `UpdateArticle`
- Queries: `GetArticle`, `ListPublishedArticles`
- Clear separation of write and read operations

### Testable by Design
- Domain logic tested without database
- Handlers tested with mock repositories
- Integration tests for full flow

---

## The Path Forward

This module shows that **modern architecture and XOOPS can coexist**.

For XOOPS 2.5.x users:
- Study the patterns
- Adopt what makes sense
- Gradually modernize existing modules

For XOOPS 2026:
- These patterns become the standard
- PSR-11 container integration
- PSR-14 event dispatcher
- PSR-15 middleware pipeline

---

## Join the Journey

The Vision 2026 Module is open source. We welcome:

- **Feedback** on the architecture
- **Contributions** to the codebase
- **Questions** about implementation
- **Ports** of existing modules to this style

Together, we're building the future of XOOPS.

---

*"Good architecture makes the system easy to understand, easy to develop, easy to maintain, and easy to deploy."*
— Robert C. Martin
