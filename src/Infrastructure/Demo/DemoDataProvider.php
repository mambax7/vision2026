<?php

declare(strict_types=1);

namespace Vision2026\Infrastructure\Demo;

use Vision2026\Domain\Entity\Article;
use Vision2026\Domain\Entity\ArticleStatus;
use Vision2026\Domain\ValueObject\ArticleId;
use Vision2026\Domain\ValueObject\ArticleTitle;
use Vision2026\Domain\ValueObject\ArticleSlug;
use Vision2026\Domain\ValueObject\ArticleContent;
use Vision2026\Domain\ValueObject\AuthorId;
use Vision2026\Domain\ValueObject\CategoryId;

/**
 * Provides demo/sample data for the Vision 2026 module.
 *
 * This allows the module to display realistic content before
 * the database infrastructure is fully implemented. Perfect for:
 * - Demonstrations
 * - Development/testing
 * - Showcasing the architecture
 *
 * Enable demo mode by setting VISION2026_DEMO_MODE constant or
 * adding 'demo=1' to the URL.
 */
final class DemoDataProvider
{
    /**
     * Check if demo mode is enabled.
     */
    public static function isEnabled(): bool
    {
        // URL parameter takes precedence - allows overriding config
        if (isset($_GET['demo'])) {
            return $_GET['demo'] === '1';
        }

        // Check constant
        if (defined('VISION2026_DEMO_MODE') && VISION2026_DEMO_MODE === true) {
            return true;
        }

        // Check config file
        $configFile = dirname(__DIR__, 3) . '/config/demo.php';
        if (file_exists($configFile)) {
            $config = require $configFile;
            return $config['enabled'] ?? false;
        }

        return false;
    }

    /**
     * Get sample published articles.
     *
     * @return list<Article>
     */
    public static function getPublishedArticles(): array
    {
        $articles = [];

        foreach (self::getSampleData() as $data) {
            if ($data['status'] === 'published') {
                $articles[] = self::createArticleFromData($data);
            }
        }

        return $articles;
    }

    /**
     * Get all sample articles.
     *
     * @return list<Article>
     */
    public static function getAllArticles(): array
    {
        return array_map(
            fn(array $data) => self::createArticleFromData($data),
            self::getSampleData()
        );
    }

    /**
     * Find a sample article by slug.
     */
    public static function findBySlug(string $slug): ?Article
    {
        foreach (self::getSampleData() as $data) {
            if ($data['slug'] === $slug) {
                return self::createArticleFromData($data);
            }
        }
        return null;
    }

    /**
     * Find a sample article by ID.
     */
    public static function findById(string $id): ?Article
    {
        foreach (self::getSampleData() as $data) {
            if ($data['id'] === $id) {
                return self::createArticleFromData($data);
            }
        }
        return null;
    }

    /**
     * Get article count by status.
     */
    public static function countByStatus(ArticleStatus $status): int
    {
        return count(array_filter(
            self::getSampleData(),
            fn(array $data) => $data['status'] === $status->value
        ));
    }

    /**
     * Get total article count.
     */
    public static function count(): int
    {
        return count(self::getSampleData());
    }

    /**
     * Create an Article entity from sample data.
     */
    private static function createArticleFromData(array $data): Article
    {
        return Article::reconstitute(
            id: ArticleId::fromString($data['id']),
            title: ArticleTitle::fromString($data['title']),
            slug: ArticleSlug::fromString($data['slug']),
            content: ArticleContent::fromString($data['content'], 'html'),
            status: ArticleStatus::from($data['status']),
            authorId: AuthorId::fromInt($data['author_id']),
            categoryId: isset($data['category_id']) ? CategoryId::fromInt($data['category_id']) : null,
            createdAt: new \DateTimeImmutable($data['created_at']),
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
            publishedAt: isset($data['published_at']) ? new \DateTimeImmutable($data['published_at']) : null,
        );
    }

    /**
     * Sample article data.
     *
     * @return list<array<string, mixed>>
     */
    private static function getSampleData(): array
    {
        return [
            [
                'id' => '01KGECX9PHNQ9PK0TT17TY8QAF',
                'title' => 'Welcome to Vision 2026: The Future of XOOPS',
                'slug' => 'welcome-to-vision-2026',
                'content' => self::getWelcomeContent(),
                'status' => 'published',
                'author_id' => 1,
                'category_id' => 1,
                'created_at' => '2026-01-15 10:00:00',
                'published_at' => '2026-01-15 12:00:00',
            ],
            [
                'id' => '01KGECX9PK1X507EFY3J2388D0',
                'title' => 'Understanding Clean Architecture in XOOPS Modules',
                'slug' => 'understanding-clean-architecture',
                'content' => self::getCleanArchitectureContent(),
                'status' => 'published',
                'author_id' => 1,
                'category_id' => 2,
                'created_at' => '2026-01-16 09:00:00',
                'published_at' => '2026-01-16 14:30:00',
            ],
            [
                'id' => '01KGECX9PMCK3P2Q0TQEW86RP7',
                'title' => 'Value Objects: Making Invalid States Impossible',
                'slug' => 'value-objects-invalid-states-impossible',
                'content' => self::getValueObjectsContent(),
                'status' => 'published',
                'author_id' => 1,
                'category_id' => 2,
                'created_at' => '2026-01-17 11:00:00',
                'published_at' => '2026-01-17 16:00:00',
            ],
            [
                'id' => '01KGECX9PNWG6VKYRMXGJ2KC1V',
                'title' => 'Domain Events: Decoupling Your Module',
                'slug' => 'domain-events-decoupling',
                'content' => self::getDomainEventsContent(),
                'status' => 'published',
                'author_id' => 1,
                'category_id' => 2,
                'created_at' => '2026-01-18 08:30:00',
                'published_at' => '2026-01-18 10:00:00',
            ],
            [
                'id' => '01KGECX9PQ3095QT2QR3E76N4Y',
                'title' => 'The Repository Pattern Explained',
                'slug' => 'repository-pattern-explained',
                'content' => self::getRepositoryPatternContent(),
                'status' => 'published',
                'author_id' => 1,
                'category_id' => 2,
                'created_at' => '2026-01-19 13:00:00',
                'published_at' => '2026-01-19 15:30:00',
            ],
            [
                'id' => '01KGECX9PRWJWAKCBFTYM8S2G3',
                'title' => 'Draft: Upcoming Features in Vision 2026',
                'slug' => 'upcoming-features-draft',
                'content' => 'This article is still being written. Stay tuned for exciting updates about new features coming to Vision 2026!',
                'status' => 'draft',
                'author_id' => 1,
                'category_id' => 1,
                'created_at' => '2026-01-20 09:00:00',
            ],
        ];
    }

    private static function getWelcomeContent(): string
    {
        return <<<'HTML'
<p class="lead">Welcome to <strong>Vision 2026</strong>, the reference implementation that demonstrates the future of XOOPS module development.</p>

<h2>What is Vision 2026?</h2>

<p>Vision 2026 is more than just an Article Management module. It's a <em>living example</em> of how modern PHP practices can be applied within XOOPS while maintaining backward compatibility.</p>

<h2>Key Features</h2>

<p>This module showcases several important architectural patterns:</p>

<ul>
    <li><strong>Clean Architecture</strong> - Separation of concerns with Domain, Application, and Infrastructure layers</li>
    <li><strong>Domain-Driven Design</strong> - Rich domain model with entities and value objects</li>
    <li><strong>CQRS-lite</strong> - Command/Query separation for clarity</li>
    <li><strong>Event-Driven</strong> - Domain events for decoupled side effects</li>
    <li><strong>Modern PHP 8.2+</strong> - Enums, readonly properties, constructor promotion</li>
</ul>

<h2>Why Does This Matter?</h2>

<p>For over 20 years, XOOPS has powered websites around the world. As PHP evolved from version 4 to 8.2+, we've gained powerful new features. Vision 2026 shows how to leverage these features while respecting XOOPS's heritage.</p>

<blockquote>
    <p>"The best code is code you're proud to show others."</p>
</blockquote>

<p>This module is designed to be studied, learned from, and extended. Every pattern, every decision, is documented and explained.</p>

<h2>Get Started</h2>

<p>Explore the other articles in this series to learn about each architectural pattern in detail. Or dive into the source code—it's thoroughly commented and designed for learning.</p>

<p><em>Welcome to the future of XOOPS. Welcome to Vision 2026.</em></p>
HTML;
    }

    private static function getCleanArchitectureContent(): string
    {
        return <<<'HTML'
<p class="lead">Clean Architecture separates your code into layers, each with a specific responsibility. The key rule: <strong>dependencies point inward</strong>.</p>

<h2>The Four Layers</h2>

<h3>1. Domain Layer (The Core)</h3>

<p>This is where your business logic lives. It contains:</p>

<ul>
    <li><strong>Entities</strong> - Objects with identity (like <code>Article</code>)</li>
    <li><strong>Value Objects</strong> - Immutable objects defined by their values (like <code>ArticleTitle</code>)</li>
    <li><strong>Domain Events</strong> - Things that happened in your domain</li>
    <li><strong>Repository Interfaces</strong> - Contracts for data access</li>
</ul>

<p><em>The domain layer has zero dependencies on frameworks, databases, or external services.</em></p>

<h3>2. Application Layer</h3>

<p>This layer orchestrates use cases. It contains:</p>

<ul>
    <li><strong>Commands</strong> - Requests to change state (<code>CreateArticle</code>)</li>
    <li><strong>Command Handlers</strong> - Execute the commands</li>
    <li><strong>Queries</strong> - Requests for data</li>
    <li><strong>Application Services</strong> - Complex orchestration</li>
</ul>

<h3>3. Infrastructure Layer</h3>

<p>Concrete implementations of domain interfaces:</p>

<ul>
    <li><strong>Repository Implementations</strong> - Actual database access</li>
    <li><strong>Event Dispatchers</strong> - Send events to listeners</li>
    <li><strong>External Services</strong> - Email, APIs, etc.</li>
</ul>

<h3>4. Presentation Layer</h3>

<p>The user interface:</p>

<ul>
    <li><strong>Controllers</strong> - Handle HTTP requests</li>
    <li><strong>Templates</strong> - Render HTML</li>
    <li><strong>API Endpoints</strong> - REST/GraphQL interfaces</li>
</ul>

<h2>The Dependency Rule</h2>

<p>Each layer can only depend on layers <em>inside</em> it:</p>

<pre><code>Presentation → Application → Domain ← Infrastructure</code></pre>

<p>Notice how Infrastructure depends on Domain (it implements the interfaces), not the other way around. This is <strong>Dependency Inversion</strong>.</p>

<h2>Benefits</h2>

<ul>
    <li><strong>Testability</strong> - Domain can be tested without database</li>
    <li><strong>Flexibility</strong> - Swap infrastructure without changing domain</li>
    <li><strong>Clarity</strong> - Each layer has clear responsibility</li>
    <li><strong>Longevity</strong> - Business logic survives framework changes</li>
</ul>
HTML;
    }

    private static function getValueObjectsContent(): string
    {
        return <<<'HTML'
<p class="lead">Value Objects are immutable objects that are defined by their attributes, not their identity. They're one of the most powerful patterns for writing correct code.</p>

<h2>The Problem</h2>

<p>Consider this traditional approach:</p>

<pre><code>$title = $_POST['title'];
// $title could be: empty, 10000 chars, contain SQL injection...</code></pre>

<p>The variable <code>$title</code> is just a string. It could be <em>anything</em>. We have to validate it everywhere we use it.</p>

<h2>The Solution: Value Objects</h2>

<pre><code>$title = ArticleTitle::fromString($_POST['title']);
// If we reach here, $title is GUARANTEED to be valid</code></pre>

<p>The <code>ArticleTitle</code> class validates on creation. If the string is invalid, an exception is thrown. If we have an <code>ArticleTitle</code> instance, we <em>know</em> it's valid.</p>

<h2>Characteristics of Value Objects</h2>

<h3>1. Immutability</h3>

<pre><code>final readonly class ArticleTitle
{
    private function __construct(public string $value) {}
}</code></pre>

<p>Once created, a value object cannot change. This eliminates a whole class of bugs.</p>

<h3>2. Self-Validating</h3>

<pre><code>public static function fromString(string $title): self
{
    if (mb_strlen($title) < 3) {
        throw new InvalidArgumentException('Title too short');
    }
    return new self($title);
}</code></pre>

<h3>3. Equality by Value</h3>

<pre><code>public function equals(self $other): bool
{
    return $this->value === $other->value;
}</code></pre>

<p>Two value objects with the same values are considered equal.</p>

<h2>Examples in Vision 2026</h2>

<ul>
    <li><code>ArticleId</code> - ULID that's guaranteed to be 26 valid characters</li>
    <li><code>ArticleTitle</code> - String between 3-200 characters</li>
    <li><code>ArticleSlug</code> - URL-safe string</li>
    <li><code>ArticleContent</code> - Content with format tracking</li>
</ul>

<h2>The Power of Type Hints</h2>

<pre><code>public function publish(ArticleId $id): void
{
    // We KNOW $id is valid - no need to check
}</code></pre>

<p>When you see <code>ArticleId</code> in a function signature, you know exactly what you're getting. The type system enforces correctness.</p>
HTML;
    }

    private static function getDomainEventsContent(): string
    {
        return <<<'HTML'
<p class="lead">Domain Events represent something significant that happened in your domain. They enable loose coupling between different parts of your application.</p>

<h2>What Are Domain Events?</h2>

<p>When an article is published, several things might need to happen:</p>

<ul>
    <li>Send email notifications to subscribers</li>
    <li>Update the search index</li>
    <li>Post to social media</li>
    <li>Clear the cache</li>
    <li>Log the activity</li>
</ul>

<p>Without events, you'd have to put all this logic in the <code>publish()</code> method. That's messy and tightly coupled.</p>

<h2>The Event-Driven Approach</h2>

<pre><code>// In the Article entity
public function publish(): void
{
    $this->status = ArticleStatus::Published;
    $this->publishedAt = new DateTimeImmutable();

    // Just record what happened
    $this->recordEvent(new ArticlePublished($this->id));
}</code></pre>

<p>The entity just records that something happened. It doesn't know or care what will happen as a result.</p>

<h2>Handling Events</h2>

<pre><code>// Somewhere in infrastructure
$dispatcher->addListener(ArticlePublished::class, function ($event) {
    // Send notifications
});

$dispatcher->addListener(ArticlePublished::class, function ($event) {
    // Update search index
});</code></pre>

<p>Each listener handles one specific concern. They're independent and can be added/removed without changing the domain.</p>

<h2>Benefits</h2>

<ul>
    <li><strong>Decoupling</strong> - Domain doesn't know about notifications</li>
    <li><strong>Extensibility</strong> - Add new reactions without changing core code</li>
    <li><strong>Testability</strong> - Test domain without side effects</li>
    <li><strong>Audit Trail</strong> - Events can be stored for history</li>
</ul>

<h2>Events in Vision 2026</h2>

<ul>
    <li><code>ArticleCreated</code> - Raised when article is first created</li>
    <li><code>ArticlePublished</code> - Raised when article goes live</li>
    <li><code>ArticleUpdated</code> - Raised when content changes</li>
</ul>
HTML;
    }

    private static function getRepositoryPatternContent(): string
    {
        return <<<'HTML'
<p class="lead">The Repository Pattern provides a clean abstraction over data storage. Your domain code doesn't know or care whether data comes from MySQL, PostgreSQL, or an API.</p>

<h2>The Interface</h2>

<p>In the <em>Domain</em> layer, we define what we need:</p>

<pre><code>interface ArticleRepositoryInterface
{
    public function find(ArticleId $id): ?Article;
    public function save(Article $article): void;
    public function remove(Article $article): void;
}</code></pre>

<p>Notice: no SQL, no database concepts. Just domain language.</p>

<h2>The Implementation</h2>

<p>In the <em>Infrastructure</em> layer, we provide the how:</p>

<pre><code>class XoopsArticleRepository implements ArticleRepositoryInterface
{
    public function find(ArticleId $id): ?Article
    {
        $sql = "SELECT * FROM ... WHERE id = ?";
        $row = $this->db->fetchOne($sql, [$id->toString()]);
        return $row ? $this->mapper->toDomain($row) : null;
    }
}</code></pre>

<h2>Why This Matters</h2>

<h3>1. Testability</h3>

<pre><code>// In tests, use a fake repository
$repository = new InMemoryArticleRepository();
$repository->save($article);
// No database needed!</code></pre>

<h3>2. Flexibility</h3>

<p>Need to switch databases? Just write a new implementation. The domain code doesn't change.</p>

<h3>3. Encapsulation</h3>

<p>All data access logic is in one place. No SQL scattered throughout your codebase.</p>

<h2>The Mapper</h2>

<p>The repository uses a <strong>Mapper</strong> to convert between domain objects and database rows:</p>

<pre><code>class ArticleMapper
{
    public function toDomain(array $row): Article
    {
        return Article::reconstitute(
            id: ArticleId::fromString($row['id']),
            title: ArticleTitle::fromString($row['title']),
            // ...
        );
    }

    public function toPersistence(Article $article): array
    {
        return [
            'id' => $article->id->toString(),
            'title' => $article->getTitle()->value,
            // ...
        ];
    }
}</code></pre>

<h2>Key Insight</h2>

<p>The domain defines <em>what</em> it needs (the interface). Infrastructure provides <em>how</em> it's done (the implementation). This is <strong>Dependency Inversion</strong> at work.</p>
HTML;
    }
}
