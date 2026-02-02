<?php

declare(strict_types=1);

namespace Vision2026\Domain\ValueObject;

use InvalidArgumentException;

/**
 * ULID-based article identifier.
 *
 * ULIDs (Universally Unique Lexicographically Sortable Identifiers) provide:
 * - Time-sortable: lexicographic order = chronological order
 * - Globally unique: no coordination needed
 * - URL-safe: uses Crockford's Base32
 * - Compact: 26 characters vs UUID's 36
 *
 * Format: 01ARZ3NDEKTSV4RRFFQ69G5FAV
 * - First 10 chars: timestamp (48-bit, millisecond precision)
 * - Last 16 chars: randomness (80-bit)
 *
 * @see https://github.com/ulid/spec
 */
final readonly class ArticleId
{
    private const ENCODING_CHARS = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
    private const ENCODING_LENGTH = 26;

    private function __construct(
        public string $value
    ) {}

    /**
     * Generate a new ULID.
     */
    public static function generate(): self
    {
        $time = (int) (microtime(true) * 1000);
        $timeChars = self::encodeTime($time);
        $randomChars = self::encodeRandom();

        return new self($timeChars . $randomChars);
    }

    /**
     * Create from existing string.
     *
     * @throws InvalidArgumentException if format is invalid
     */
    public static function fromString(string $id): self
    {
        $id = strtoupper(trim($id));

        if (!self::isValid($id)) {
            throw new InvalidArgumentException(
                sprintf('Invalid ULID format: "%s"', $id)
            );
        }

        return new self($id);
    }

    /**
     * Get string representation.
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Compare equality with another ArticleId.
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Extract timestamp from ULID.
     */
    public function getTimestamp(): \DateTimeImmutable
    {
        $time = self::decodeTime(substr($this->value, 0, 10));
        return \DateTimeImmutable::createFromFormat('U.u', sprintf('%.3f', $time / 1000));
    }

    /**
     * Validate ULID format.
     */
    private static function isValid(string $id): bool
    {
        if (strlen($id) !== self::ENCODING_LENGTH) {
            return false;
        }

        // Check all characters are valid Crockford Base32
        for ($i = 0; $i < self::ENCODING_LENGTH; $i++) {
            if (strpos(self::ENCODING_CHARS, $id[$i]) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Encode timestamp to 10-character string.
     */
    private static function encodeTime(int $time): string
    {
        $chars = self::ENCODING_CHARS;
        $result = '';

        for ($i = 9; $i >= 0; $i--) {
            $result = $chars[$time % 32] . $result;
            $time = intdiv($time, 32);
        }

        return $result;
    }

    /**
     * Decode timestamp from 10-character string.
     */
    private static function decodeTime(string $encoded): int
    {
        $chars = self::ENCODING_CHARS;
        $time = 0;

        for ($i = 0; $i < 10; $i++) {
            $time = $time * 32 + strpos($chars, $encoded[$i]);
        }

        return $time;
    }

    /**
     * Generate 16 random characters.
     */
    private static function encodeRandom(): string
    {
        $chars = self::ENCODING_CHARS;
        $result = '';

        for ($i = 0; $i < 16; $i++) {
            $result .= $chars[random_int(0, 31)];
        }

        return $result;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
