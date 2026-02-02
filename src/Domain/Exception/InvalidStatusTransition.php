<?php

declare(strict_types=1);

namespace Vision2026\Domain\Exception;

use Vision2026\Domain\Entity\ArticleStatus;

/**
 * Exception thrown when an invalid status transition is attempted.
 *
 * For example: trying to publish an already archived article.
 */
final class InvalidStatusTransition extends \DomainException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    /**
     * Create exception for specific status transition.
     */
    public static function fromTo(ArticleStatus $from, ArticleStatus $to): self
    {
        return new self(
            sprintf(
                'Cannot transition article from "%s" to "%s"',
                $from->value,
                $to->value
            )
        );
    }
}
