<?php

declare(strict_types=1);

namespace Vision2026\Domain\Entity;

use Vision2026\Domain\ValueObject\CategoryId;
use Vision2026\Domain\ValueObject\CategoryName;
use Vision2026\Domain\ValueObject\CategorySlug;

/**
 * Category entity.
 *
 * Simpler than Article - categories are primarily organizational structures.
 */
final class Category
{
    private function __construct(
        public readonly CategoryId $id,
        private CategoryName $name,
        private CategorySlug $slug,
        private ?string $description,
        private int $weight,
        private ?CategoryId $parentId,
        private readonly \DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $updatedAt = null,
    ) {}

    /**
     * Create a new category.
     */
    public static function create(
        CategoryId $id,
        CategoryName $name,
        ?string $description = null,
        int $weight = 0,
        ?CategoryId $parentId = null,
    ): self {
        return new self(
            id: $id,
            name: $name,
            slug: CategorySlug::fromName($name),
            description: $description,
            weight: $weight,
            parentId: $parentId,
            createdAt: new \DateTimeImmutable(),
        );
    }

    /**
     * Reconstitute from persistence.
     */
    public static function reconstitute(
        CategoryId $id,
        CategoryName $name,
        CategorySlug $slug,
        ?string $description,
        int $weight,
        ?CategoryId $parentId,
        \DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $id,
            name: $name,
            slug: $slug,
            description: $description,
            weight: $weight,
            parentId: $parentId,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    /**
     * Update category details.
     */
    public function update(
        CategoryName $name,
        ?string $description,
        int $weight,
    ): void {
        $nameChanged = !$this->name->equals($name);

        $this->name = $name;
        $this->description = $description;
        $this->weight = $weight;
        $this->updatedAt = new \DateTimeImmutable();

        // Only regenerate slug if name changed
        if ($nameChanged) {
            $this->slug = CategorySlug::fromName($name);
        }
    }

    // Getters
    public function getName(): CategoryName
    {
        return $this->name;
    }

    public function getSlug(): CategorySlug
    {
        return $this->slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function getParentId(): ?CategoryId
    {
        return $this->parentId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function hasParent(): bool
    {
        return $this->parentId !== null;
    }
}
