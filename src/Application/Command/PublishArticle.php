<?php

declare(strict_types=1);

namespace Vision2026\Application\Command;

/**
 * Command to publish an article.
 *
 * Publishing transitions an article from Draft to Published status,
 * making it visible to the public.
 */
final readonly class PublishArticle
{
    public function __construct(
        public string $articleId,
    ) {}
}
