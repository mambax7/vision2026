# Vision 2026 Module for XOOPS CMS

## Project Overview

Vision 2026 is a reference implementation demonstrating Clean Architecture and Domain-Driven Design patterns for XOOPS CMS. It serves as the blueprint for modern XOOPS module development.

### Goals

1. Demonstrate Clean Architecture in a real XOOPS module
2. Show how to build testable code without XOOPS dependencies
3. Provide patterns that can be adopted by other modules
4. Bridge the gap between XOOPS 2.5.x and XOOPS 2026

---

## Architecture

### Layer Overview

```mermaid
flowchart TB
    subgraph PRES["🎨 PRESENTATION LAYER"]
        direction LR
        CTRL["Controllers<br/>index.php, article.php"]
        TPL["Templates<br/>Smarty .tpl files"]
        API["REST API<br/>api/*.php"]
    end

    subgraph APP["⚙️ APPLICATION LAYER"]
        direction LR
        CMD["Commands<br/>CreateArticle, PublishArticle"]
        HANDLER["Handlers<br/>CreateArticleHandler"]
        EVENTS["Event Dispatcher"]
    end

    subgraph DOM["💎 DOMAIN LAYER"]
        direction LR
        ENT["Entities<br/>Article, Comment"]
        VO["Value Objects<br/>ArticleId, Title, Slug"]
        REPO_INT["Repository<br/>Interfaces"]
        DOM_EVT["Domain Events<br/>ArticleCreated"]
    end

    subgraph INFRA["🔧 INFRASTRUCTURE LAYER"]
        direction LR
        REPO_IMPL["Repository<br/>Implementations"]
        MAPPER["Mappers<br/>Row ↔ Entity"]
        CONTAINER["Service<br/>Container"]
    end

    PRES --> APP
    APP --> DOM
    INFRA --> DOM
    INFRA -.->|implements| REPO_INT

    style PRES fill:#667EEA,color:#fff
    style APP fill:#F5576C,color:#fff
    style DOM fill:#4FACFE,color:#fff
    style INFRA fill:#43E97B,color:#fff
```

### The Dependency Rule

**Critical:** Dependencies flow inward only. The Domain layer has ZERO external dependencies.

```mermaid
flowchart LR
    subgraph OUTER["Outer Layers"]
        P["Presentation"]
        I["Infrastructure"]
    end

    subgraph INNER["Inner Layers"]
        A["Application"]
        D["Domain"]
    end

    P --> A
    A --> D
    I --> D

    P -.->|"❌ NEVER"| D
    I -.->|"❌ NEVER"| P

    style D fill:#4FACFE,color:#fff
    style OUTER fill:#f5f5f5
    style INNER fill:#e8f5e9
```

---

## Directory Structure

```
vision2026/
├── src/
│   ├── Domain/                 # Pure PHP - NO dependencies
│   │   ├── Entity/
│   │   │   ├── Article.php     # Aggregate root
│   │   │   └── ArticleStatus.php # PHP 8.1 enum
│   │   ├── ValueObject/
│   │   │   ├── ArticleId.php   # ULID identifier
│   │   │   ├── ArticleTitle.php
│   │   │   ├── ArticleSlug.php
│   │   │   └── ArticleContent.php
│   │   ├── Event/
│   │   │   ├── ArticleCreated.php
│   │   │   └── ArticlePublished.php
│   │   ├── Repository/
│   │   │   └── ArticleRepositoryInterface.php
│   │   └── Exception/
│   │       └── ArticleNotFoundException.php
│   │
│   ├── Application/
│   │   ├── Command/
│   │   │   ├── CreateArticle.php
│   │   │   └── PublishArticle.php
│   │   ├── Handler/
│   │   │   ├── CreateArticleHandler.php
│   │   │   └── PublishArticleHandler.php
│   │   └── EventDispatcher/
│   │       └── EventDispatcherInterface.php
│   │
│   └── Infrastructure/
│       ├── Persistence/
│       │   ├── XoopsArticleRepository.php
│       │   └── ArticleMapper.php
│       ├── Container/
│       │   └── ServiceContainer.php
│       └── Demo/
│           └── DemoDataProvider.php
│
├── tests/
│   └── Unit/
│       └── Domain/
│           ├── Entity/
│           │   └── ArticleTest.php
│           └── ValueObject/
│               ├── ArticleIdTest.php
│               └── ArticleTitleTest.php
│
├── admin/
│   ├── index.php
│   └── menu.php
├── templates/
├── config/
│   └── demo.php
├── index.php
├── article.php
└── xoops_version.php
```

---

## Key Patterns

### 1. Entity with Factory Methods

```mermaid
classDiagram
    class Article {
        -ArticleId id
        -ArticleTitle title
        -ArticleSlug slug
        -ArticleContent content
        -ArticleStatus status
        -array domainEvents
        +create(title, content, authorId)$ Article
        +reconstitute(...)$ Article
        +publish() void
        +isPublished() bool
        +pullDomainEvents() array
    }

    class ArticleStatus {
        <<enumeration>>
        Draft
        Published
        Archived
        +canTransitionTo(status) bool
    }

    Article --> ArticleStatus
```

**Key insight:** Use `create()` for new entities (fires events), `reconstitute()` for loading from database (no events).

### 2. Value Object Validation

```mermaid
flowchart LR
    INPUT["Raw Input<br/>'My Title'"]
    VO["Value Object<br/>ArticleTitle"]
    VALID["✅ Always Valid"]
    INVALID["❌ Exception"]

    INPUT -->|"fromString()"| CHECK{Valid?}
    CHECK -->|Yes| VO
    CHECK -->|No| INVALID
    VO --> VALID

    style VO fill:#4FACFE,color:#fff
    style VALID fill:#43E97B,color:#fff
    style INVALID fill:#F5576C,color:#fff
```

### 3. Command/Handler Pattern

```mermaid
sequenceDiagram
    participant C as Controller
    participant CMD as CreateArticle
    participant H as Handler
    participant R as Repository
    participant E as EventDispatcher

    C->>CMD: new CreateArticle(title, content, authorId)
    C->>H: handle(command)
    H->>H: Article::create(...)
    H->>R: save(article)
    H->>E: dispatch(events)
    H-->>C: Article
```

### 4. Repository Pattern

```mermaid
flowchart TB
    subgraph DOMAIN["Domain Layer"]
        INTERFACE["ArticleRepositoryInterface<br/>───────────────<br/>find(id): ?Article<br/>findBySlug(slug): ?Article<br/>save(article): void"]
    end

    subgraph INFRASTRUCTURE["Infrastructure Layer"]
        XOOPS_REPO["XoopsArticleRepository<br/>───────────────<br/>Uses $xoopsDB<br/>SQL queries"]
        DEMO_REPO["DemoArticleRepository<br/>───────────────<br/>In-memory data<br/>For testing"]
    end

    XOOPS_REPO -.->|implements| INTERFACE
    DEMO_REPO -.->|implements| INTERFACE

    style DOMAIN fill:#4FACFE,color:#fff
    style INFRASTRUCTURE fill:#43E97B,color:#fff
```

---

## Working with This Codebase

### Adding a New Entity

1. Create the Entity in `src/Domain/Entity/`
2. Create Value Objects in `src/Domain/ValueObject/`
3. Create Domain Events in `src/Domain/Event/`
4. Create Repository Interface in `src/Domain/Repository/`
5. Create Repository Implementation in `src/Infrastructure/Persistence/`
6. Add to ServiceContainer
7. Write unit tests (Domain layer only needs PHPUnit)

### Adding a New Command

```mermaid
flowchart LR
    A["1. Create Command<br/>Application/Command/"] --> B["2. Create Handler<br/>Application/Handler/"]
    B --> C["3. Register in Container"]
    C --> D["4. Use in Controller"]

    style A fill:#667EEA,color:#fff
    style B fill:#667EEA,color:#fff
    style C fill:#43E97B,color:#fff
    style D fill:#667EEA,color:#fff
```

### Testing Strategy

```mermaid
pie title Test Distribution
    "Unit Tests (Domain)" : 60
    "Unit Tests (Application)" : 20
    "Integration Tests" : 15
    "Functional Tests" : 5
```

**Unit tests for Domain layer:**
- No mocks needed
- No database
- No XOOPS
- Run in milliseconds

---

## Demo Mode

When `config/demo.php` has `'enabled' => true`:

```mermaid
flowchart TB
    REQ["Request"] --> CHECK{Demo Mode?}
    CHECK -->|Yes| DEMO["DemoDataProvider<br/>Returns sample articles"]
    CHECK -->|No| DB["XoopsArticleRepository<br/>Queries database"]
    DEMO --> RESPONSE["Response"]
    DB --> RESPONSE

    style DEMO fill:#43E97B,color:#fff
    style DB fill:#4FACFE,color:#fff
```

Demo mode provides 5 sample articles with educational content about Clean Architecture.

---

## Common Tasks

### Run Tests
```bash
composer test
```

### Check Code Style
```bash
composer cs-check
```

### Enable Demo Mode
```php
// config/demo.php
return ['enabled' => true];
```

### Create a New Article (Code)
```php
$command = new CreateArticle(
    ArticleTitle::fromString('My Title'),
    ArticleContent::fromString('Content here'),
    AuthorId::fromInt($userId)
);

$article = $handler->handle($command);
```

---

## Integration with XOOPS

### What Stays Traditional
- `xoops_version.php` — Module manifest
- `admin/menu.php` — Admin menu
- `templates/*.tpl` — Smarty templates
- XOOPS bootstrap (`mainfile.php`, `header.php`)

### What's New
- PSR-4 autoloading via Composer
- Dependency injection via ServiceContainer
- Domain logic isolated from XOOPS
- Unit tests without XOOPS bootstrap

---

## Resources

- [WALKTHROUGH.md](docs/WALKTHROUGH.md) — Step-by-step request flow
- [COMPARISON.md](docs/COMPARISON.md) — Traditional vs Vision 2026
- [Knowledge Base](../XOOPS-Knowledge-Base/10-Vision2026-Module/) — Full documentation

---
