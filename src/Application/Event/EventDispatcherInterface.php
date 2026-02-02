<?php

declare(strict_types=1);

namespace Vision2026\Application\Event;

use Vision2026\Domain\Event\DomainEvent;

/**
 * Interface for dispatching domain events.
 *
 * This abstraction allows us to:
 * - Use a simple implementation for XOOPS 2.5.x
 * - Upgrade to PSR-14 EventDispatcher for XOOPS 2026
 * - Easily mock in tests
 */
interface EventDispatcherInterface
{
    /**
     * Dispatch a domain event to all registered listeners.
     */
    public function dispatch(DomainEvent $event): void;
}
