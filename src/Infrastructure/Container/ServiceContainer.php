<?php

declare(strict_types=1);

namespace Vision2026\Infrastructure\Container;

use Vision2026\Domain\Repository\ArticleRepositoryInterface;
use Vision2026\Infrastructure\Persistence\XoopsArticleRepository;
use Vision2026\Infrastructure\Persistence\ArticleMapper;
use Vision2026\Infrastructure\Persistence\XoopsConnection;
use Vision2026\Infrastructure\Event\SimpleEventDispatcher;
use Vision2026\Application\Event\EventDispatcherInterface;
use Vision2026\Application\Command\CreateArticleHandler;
use Vision2026\Application\Command\PublishArticleHandler;

/**
 * Simple service container for Vision 2026.
 *
 * For XOOPS 2.5.x: Uses this simple container with manual wiring.
 * For XOOPS 2026: Would be replaced with PSR-11 container with autowiring.
 *
 * This container provides:
 * - Singleton management (services created once)
 * - Dependency injection (services receive their dependencies)
 * - Abstraction (code depends on interfaces, not implementations)
 */
final class ServiceContainer
{
    private static ?self $instance = null;

    /** @var array<string, object> */
    private array $services = [];

    /** @var array<string, callable> */
    private array $factories = [];

    private function __construct()
    {
        $this->registerDefaults();
    }

    /**
     * Get the container instance (singleton).
     */
    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * Reset the container (useful for testing).
     */
    public static function reset(): void
    {
        self::$instance = null;
    }

    /**
     * Get a service by its identifier.
     *
     * @template T of object
     * @param class-string<T> $id
     * @return T
     * @throws ServiceNotFound
     */
    public function get(string $id): object
    {
        // Return cached instance if exists
        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        // Create via factory if registered
        if (isset($this->factories[$id])) {
            $this->services[$id] = ($this->factories[$id])($this);
            return $this->services[$id];
        }

        throw new ServiceNotFound("Service not found: $id");
    }

    /**
     * Check if a service is registered.
     */
    public function has(string $id): bool
    {
        return isset($this->services[$id]) || isset($this->factories[$id]);
    }

    /**
     * Register a service factory.
     *
     * @param callable(self): object $factory
     */
    public function register(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
        unset($this->services[$id]); // Clear cached instance
    }

    /**
     * Register a pre-built instance.
     */
    public function set(string $id, object $instance): void
    {
        $this->services[$id] = $instance;
    }

    /**
     * Register default services.
     */
    private function registerDefaults(): void
    {
        // Database connection
        $this->register(
            XoopsConnection::class,
            fn() => new XoopsConnection()
        );

        // Event dispatcher
        $this->register(
            EventDispatcherInterface::class,
            fn() => new SimpleEventDispatcher()
        );

        // Article mapper
        $this->register(
            ArticleMapper::class,
            fn() => new ArticleMapper()
        );

        // Article repository
        $this->register(
            ArticleRepositoryInterface::class,
            fn(self $c) => new XoopsArticleRepository(
                $c->get(XoopsConnection::class),
                $c->get(ArticleMapper::class),
            )
        );

        // Category mapper
        $this->register(
            \Vision2026\Infrastructure\Persistence\CategoryMapper::class,
            fn() => new \Vision2026\Infrastructure\Persistence\CategoryMapper()
        );

        // Category repository
        $this->register(
            \Vision2026\Domain\Repository\CategoryRepositoryInterface::class,
            fn(self $c) => new \Vision2026\Infrastructure\Persistence\XoopsCategoryRepository(
                $c->get(XoopsConnection::class),
                $c->get(\Vision2026\Infrastructure\Persistence\CategoryMapper::class),
            )
        );

        // Command handlers
        $this->register(
            CreateArticleHandler::class,
            fn(self $c) => new CreateArticleHandler(
                $c->get(ArticleRepositoryInterface::class),
                $c->get(EventDispatcherInterface::class),
            )
        );

        $this->register(
            PublishArticleHandler::class,
            fn(self $c) => new PublishArticleHandler(
                $c->get(ArticleRepositoryInterface::class),
                $c->get(EventDispatcherInterface::class),
            )
        );
    }
}
