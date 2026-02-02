<?php

declare(strict_types=1);

namespace Vision2026\Infrastructure\Event;

use Vision2026\Application\Event\EventDispatcherInterface;
use Vision2026\Domain\Event\DomainEvent;

/**
 * Simple event dispatcher implementation.
 *
 * This is a minimal implementation for XOOPS 2.5.x compatibility.
 * For XOOPS 2026, this would be replaced with a PSR-14 implementation.
 *
 * Listeners are callables that receive the event object.
 */
final class SimpleEventDispatcher implements EventDispatcherInterface
{
    /** @var array<string, list<callable>> */
    private array $listeners = [];

    /**
     * Register a listener for an event type.
     *
     * @param string   $eventName Event class name or event name
     * @param callable $listener  Callable that receives the event
     */
    public function addListener(string $eventName, callable $listener): void
    {
        $this->listeners[$eventName][] = $listener;
    }

    /**
     * Dispatch an event to all registered listeners.
     */
    public function dispatch(DomainEvent $event): void
    {
        $eventName = $event->eventName();
        $eventClass = get_class($event);

        // Dispatch to listeners registered by event name
        $this->dispatchToListeners($eventName, $event);

        // Dispatch to listeners registered by class name
        if ($eventClass !== $eventName) {
            $this->dispatchToListeners($eventClass, $event);
        }
    }

    private function dispatchToListeners(string $key, DomainEvent $event): void
    {
        if (!isset($this->listeners[$key])) {
            return;
        }

        foreach ($this->listeners[$key] as $listener) {
            $listener($event);
        }
    }

    /**
     * Remove all listeners for an event.
     */
    public function clearListeners(string $eventName): void
    {
        unset($this->listeners[$eventName]);
    }

    /**
     * Check if there are listeners for an event.
     */
    public function hasListeners(string $eventName): bool
    {
        return !empty($this->listeners[$eventName]);
    }
}
