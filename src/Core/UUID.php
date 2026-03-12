<?php

namespace Tito\App\Core;

use InvalidArgumentException;
use Random\RandomException;

class UUID
{
    private const PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    private string $value;

    public function __construct(string $value)
    {
        if (!self::isValid($value)) {
            throw new InvalidArgumentException("Invalid UUID v4 format: $value");
        }

        $this->value = strtolower($value);
    }

    public static function randomUUID(): self
    {
        $bytes = random_bytes(16);

        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        $hex = bin2hex($bytes);

        $uuid = sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );

        return new self($uuid);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function isValid(string $value): bool
    {
        return (bool)preg_match(self::PATTERN, $value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(UUID $other): bool
    {
        return $this->value === $other->value;
    }
}
