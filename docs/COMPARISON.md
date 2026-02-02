# Traditional XOOPS vs Vision 2026: Side-by-Side Comparison

> A detailed comparison showing how the same functionality is implemented in traditional XOOPS modules versus the Vision 2026 architecture.

---

## 📊 Quick Comparison Table

| Aspect | Traditional XOOPS | Vision 2026 |
|--------|-------------------|-------------|
| **Validation** | Scattered throughout code | Centralized in Value Objects |
| **Database Access** | Anywhere (`$xoopsDB->query`) | Only in Infrastructure layer |
| **Testing** | Requires XOOPS + database | Domain tests run standalone |
| **Business Rules** | Mixed with UI/DB code | Isolated in Domain Entities |
| **Dependencies** | Everything depends on XOOPS | Core code has zero dependencies |
| **Reusability** | Locked to XOOPS | Domain logic can be reused anywhere |
| **Code Organization** | By feature files | By architectural layer |

---

## 1️⃣ Creating an Article

### Traditional Approach

```php
// submit.php - Everything in one file

// Get form data
$title = $_POST['title'];
$content = $_POST['content'];

// Validation scattered here
if (empty($title)) {
    $error = 'Title is required';
    redirect_header('add.php', 2, $error);
    exit;
}

if (strlen($title) > 255) {
    $error = 'Title too long';
    redirect_header('add.php', 2, $error);
    exit;
}

// Slug generation mixed with validation
$slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($title));
$slug = trim($slug, '-');

// Database code right here
global $xoopsDB, $xoopsUser;

$sql = sprintf(
    "INSERT INTO %s (title, slug, content, author_id, created_at)
     VALUES (%s, %s, %s, %d, NOW())",
    $xoopsDB->prefix('mymodule_articles'),
    $xoopsDB->quoteString($title),
    $xoopsDB->quoteString($slug),
    $xoopsDB->quoteString($content),
    $xoopsUser->getVar('uid')
);

if ($xoopsDB->queryF($sql)) {
    $article_id = $xoopsDB->getInsertId();
    // More code to notify, update counts, etc.
    redirect_header('view.php?id=' . $article_id, 2, 'Article created!');
} else {
    redirect_header('add.php', 2, 'Database error');
}
```

**Problems:**
- Validation mixed with database code
- Slug generation logic duplicated elsewhere
- Hard to test without database
- Easy to forget validation in other places
- No events for side effects (notifications, indexing)

---

### Vision 2026 Approach

**Step 1: Controller (Presentation Layer)**
```php
// admin/submit.php
use Vision2026\Application\Command\CreateArticle;
use Vision2026\Application\Handler\CreateArticleHandler;
use Vision2026\Domain\ValueObject\{ArticleTitle, ArticleContent, AuthorId};

// Get the handler from container
$handler = $container->get(CreateArticleHandler::class);

try {
    // Value Objects validate at creation!
    $command = new CreateArticle(
        title: ArticleTitle::fromString($_POST['title']),
        content: ArticleContent::fromString($_POST['content']),
        authorId: AuthorId::fromInt($xoopsUser->getVar('uid'))
    );

    // Handler does the work
    $article = $handler->handle($command);

    redirect_header('view.php?id=' . $article->id, 2, 'Article created!');

} catch (InvalidArgumentException $e) {
    // All validation errors caught here
    redirect_header('add.php', 2, $e->getMessage());
}
```

**Step 2: Command (Application Layer)**
```php
// src/Application/Command/CreateArticle.php
final readonly class CreateArticle
{
    public function __construct(
        public ArticleTitle $title,     // Already validated!
        public ArticleContent $content, // Already validated!
        public AuthorId $authorId       // Already validated!
    ) {}
}
```

**Step 3: Handler (Application Layer)**
```php
// src/Application/Handler/CreateArticleHandler.php
final class CreateArticleHandler
{
    public function __construct(
        private ArticleRepositoryInterface $repository,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    public function handle(CreateArticle $command): Article
    {
        // Create article using factory method
        $article = Article::create(
            $command->title,
            $command->content,
            $command->authorId
        );

        // Slug is auto-generated in the Entity
        // ID is auto-generated (ULID)

        // Save through repository
        $this->repository->save($article);

        // Dispatch events for side effects
        foreach ($article->pullDomainEvents() as $event) {
            $this->eventDispatcher->dispatch($event);
        }

        return $article;
    }
}
```

**Step 4: Entity (Domain Layer)**
```php
// src/Domain/Entity/Article.php
final class Article
{
    public static function create(
        ArticleTitle $title,
        ArticleContent $content,
        AuthorId $authorId
    ): self {
        $id = ArticleId::generate();
        $slug = ArticleSlug::fromTitle($title);  // Auto-generate slug!

        $article = new self(
            id: $id,
            title: $title,
            slug: $slug,
            content: $content,
            authorId: $authorId,
            status: ArticleStatus::Draft,
            createdAt: new DateTimeImmutable()
        );

        // Record that this happened
        $article->recordEvent(new ArticleCreated($id, $title, $authorId));

        return $article;
    }
}
```

**Benefits:**
- Validation happens once in Value Objects
- Slug generation is in ArticleSlug::fromTitle()
- Handler is easily testable with mock repository
- Events enable loose coupling for notifications
- Clear separation of concerns

---

## 2️⃣ Validating a Title

### Traditional Approach

```php
// validation.php (or scattered across multiple files)

function validateTitle($title) {
    if (empty($title)) {
        return 'Title is required';
    }
    if (strlen($title) > 255) {
        return 'Title must be less than 255 characters';
    }
    if (strlen($title) < 3) {
        return 'Title must be at least 3 characters';
    }
    return null; // Valid
}

// But then in submit.php someone writes:
if (strlen($title) > 255) { ... }  // Duplicated!

// And in edit.php:
if (!$title) { ... }  // Different check!

// And in api.php:
// Forgot validation entirely! 🐛
```

**Problems:**
- Validation logic duplicated
- Easy to forget in some places
- Inconsistent error messages
- No type safety (passing wrong value)

---

### Vision 2026 Approach

```php
// src/Domain/ValueObject/ArticleTitle.php

final readonly class ArticleTitle
{
    public const MIN_LENGTH = 3;
    public const MAX_LENGTH = 255;

    private function __construct(
        public string $value
    ) {}

    public static function fromString(string $value): self
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidArgumentException('Title cannot be empty');
        }

        if (strlen($value) < self::MIN_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Title must be at least %d characters', self::MIN_LENGTH)
            );
        }

        if (strlen($value) > self::MAX_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Title must be less than %d characters', self::MAX_LENGTH)
            );
        }

        return new self($value);
    }

    // Bonus: Rich behavior
    public function toSlug(): string
    {
        $slug = strtolower($this->value);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim($slug, '-');
    }
}
```

**Usage everywhere:**
```php
// In controller:
$title = ArticleTitle::fromString($_POST['title']);

// In handler:
$command->title  // Already an ArticleTitle

// In repository:
$row['title']    // Need to wrap: ArticleTitle::fromString($row['title'])

// IMPOSSIBLE to pass a plain string where ArticleTitle is expected!
public function setTitle(ArticleTitle $title): void  // Type safety!
```

**Benefits:**
- Validated exactly once at creation
- Impossible to have an invalid ArticleTitle
- Type system prevents passing wrong values
- Business logic (toSlug) lives with the data
- Constants define the rules clearly

---

## 3️⃣ Querying Articles

### Traditional Approach

```php
// list.php

global $xoopsDB;

$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$limit = 10;

// Build query string (SQL injection risk if not careful)
$sql = "SELECT a.*, u.uname as author_name
        FROM " . $xoopsDB->prefix('mymodule_articles') . " a
        LEFT JOIN " . $xoopsDB->prefix('users') . " u
            ON a.author_id = u.uid
        WHERE a.status = 'published'
        AND a.published_at <= NOW()
        ORDER BY a.published_at DESC
        LIMIT $start, $limit";

$result = $xoopsDB->query($sql);

$articles = [];
while ($row = $xoopsDB->fetchArray($result)) {
    // Accessing array keys - no autocomplete, easy to typo
    $articles[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        // What if we add a field? Must update everywhere!
    ];
}
```

**Problems:**
- SQL scattered throughout codebase
- No type hints, easy to typo column names
- Status check logic duplicated everywhere
- Hard to test (needs database)

---

### Vision 2026 Approach

**Controller (Presentation Layer):**
```php
// index.php
$repository = $container->get(ArticleRepositoryInterface::class);

$result = $repository->findPublishedPaginated(
    page: (int) ($_GET['page'] ?? 1),
    perPage: 10
);

// $result['items'] contains Article entities
// $result['total'] for pagination
```

**Interface (Domain Layer):**
```php
// src/Domain/Repository/ArticleRepositoryInterface.php

interface ArticleRepositoryInterface
{
    public function find(ArticleId $id): ?Article;

    public function findBySlug(ArticleSlug $slug): ?Article;

    public function findPublishedPaginated(int $page, int $perPage): array;

    public function save(Article $article): void;

    public function delete(Article $article): void;
}
```

**Implementation (Infrastructure Layer):**
```php
// src/Infrastructure/Persistence/XoopsArticleRepository.php

final class XoopsArticleRepository implements ArticleRepositoryInterface
{
    public function findPublishedPaginated(int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;

        // Count query
        $countSql = "SELECT COUNT(*) FROM {$this->tableName}
                     WHERE status = :status
                     AND published_at <= :now
                     AND deleted_at IS NULL";

        $total = $this->connection->fetchColumn($countSql, [
            'status' => ArticleStatus::Published->value,
            'now' => date('Y-m-d H:i:s')
        ]);

        // Data query
        $sql = "SELECT * FROM {$this->tableName}
                WHERE status = :status
                AND published_at <= :now
                AND deleted_at IS NULL
                ORDER BY published_at DESC
                LIMIT :limit OFFSET :offset";

        $rows = $this->connection->fetchAll($sql, [
            'status' => ArticleStatus::Published->value,
            'now' => date('Y-m-d H:i:s'),
            'limit' => $perPage,
            'offset' => $offset
        ]);

        return [
            'items' => array_map(
                fn($row) => $this->mapper->toDomain($row),
                $rows
            ),
            'total' => (int) $total
        ];
    }
}
```

**Benefits:**
- SQL is only in Infrastructure layer
- Domain uses rich objects (Article, not array)
- "Published" logic is encapsulated
- Easy to swap database (implement new repository)
- Testable with in-memory implementation

---

## 4️⃣ Testing

### Traditional Approach

```php
// Testing is... difficult

// Need to:
// 1. Boot XOOPS
// 2. Have a test database
// 3. Seed data
// 4. Hope nothing else interferes

// Often just manual testing in browser
```

**Problems:**
- Tests are slow (database, framework boot)
- Tests are flaky (shared state)
- Often no tests at all

---

### Vision 2026 Approach

```php
// tests/Unit/Domain/Entity/ArticleTest.php

final class ArticleTest extends TestCase
{
    public function test_create_article(): void
    {
        // No database! No XOOPS! Pure PHP!
        $article = Article::create(
            ArticleTitle::fromString('My Title'),
            ArticleContent::fromString('Content here'),
            AuthorId::fromInt(1)
        );

        $this->assertInstanceOf(Article::class, $article);
        $this->assertSame('My Title', $article->getTitle()->value);
        $this->assertTrue($article->isDraft());
    }

    public function test_publish_article(): void
    {
        $article = $this->createArticle();

        $article->publish();

        $this->assertTrue($article->isPublished());
        $this->assertNotNull($article->getPublishedAt());
    }

    public function test_domain_events_recorded(): void
    {
        $article = Article::create(
            ArticleTitle::fromString('Test'),
            ArticleContent::fromString('Content'),
            AuthorId::fromInt(1)
        );

        $events = $article->pullDomainEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(ArticleCreated::class, $events[0]);
    }
}
```

```php
// tests/Unit/Domain/ValueObject/ArticleTitleTest.php

final class ArticleTitleTest extends TestCase
{
    public function test_valid_title(): void
    {
        $title = ArticleTitle::fromString('Valid Title');
        $this->assertSame('Valid Title', $title->value);
    }

    public function test_empty_title_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ArticleTitle::fromString('');
    }

    public function test_too_long_title_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ArticleTitle::fromString(str_repeat('a', 300));
    }

    public function test_slug_generation(): void
    {
        $title = ArticleTitle::fromString('Hello World!');
        $this->assertSame('hello-world', $title->toSlug());
    }
}
```

**Run with:**
```bash
composer test

# Output:
# PHPUnit 10.5.2
#
# ..........                                                     10 / 10 (100%)
#
# Time: 00:00.023, Memory: 8.00 MB
#
# OK (10 tests, 28 assertions)
```

**Benefits:**
- Tests run in milliseconds
- No external dependencies
- CI-friendly
- Test business rules in isolation
- 100% deterministic

---

## 📁 File Structure Comparison

### Traditional XOOPS Module

```
mymodule/
├── admin/
│   ├── index.php      # Mixed: queries, logic, HTML
│   ├── article.php    # Same problems
│   └── menu.php
├── class/
│   └── article.php    # Maybe a class, often just functions
├── include/
│   └── functions.php  # Global helper functions
├── templates/
│   └── index.tpl
├── index.php          # Mixed concerns
├── submit.php         # Validation + DB + redirect
└── xoops_version.php
```

### Vision 2026 Module

```
vision2026/
├── src/
│   ├── Domain/                      # Pure PHP, no dependencies
│   │   ├── Entity/
│   │   │   ├── Article.php          # Business rules
│   │   │   └── ArticleStatus.php    # State machine
│   │   ├── ValueObject/
│   │   │   ├── ArticleId.php        # Self-validating
│   │   │   ├── ArticleTitle.php
│   │   │   ├── ArticleSlug.php
│   │   │   └── ArticleContent.php
│   │   ├── Event/
│   │   │   ├── ArticleCreated.php
│   │   │   └── ArticlePublished.php
│   │   ├── Repository/
│   │   │   └── ArticleRepositoryInterface.php  # Contract only
│   │   └── Exception/
│   │       └── ArticleNotFoundException.php
│   │
│   ├── Application/                 # Use cases
│   │   ├── Command/
│   │   │   └── CreateArticle.php    # Data Transfer Object
│   │   └── Handler/
│   │       └── CreateArticleHandler.php
│   │
│   └── Infrastructure/              # External concerns
│       ├── Persistence/
│       │   ├── XoopsArticleRepository.php
│       │   └── ArticleMapper.php
│       └── Container/
│           └── ServiceContainer.php
│
├── tests/
│   └── Unit/
│       └── Domain/                  # Testable without XOOPS!
│           ├── Entity/
│           └── ValueObject/
│
├── admin/
├── templates/
├── config/
├── index.php
└── composer.json
```

---

## 🎯 Summary: When to Use Each Approach

### Use Traditional XOOPS When:
- Building a quick, small module
- Prototyping an idea
- Single developer, short lifespan
- Simple CRUD with minimal logic

### Use Vision 2026 When:
- Building a module meant to last
- Multiple developers will work on it
- Complex business rules
- Need comprehensive testing
- Want to share code between projects
- Care about maintainability

---

*This comparison is part of the Vision 2026 module for XOOPS CMS.*
