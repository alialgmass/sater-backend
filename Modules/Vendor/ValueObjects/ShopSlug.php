<?php

namespace Modules\Vendor\ValueObjects;

class ShopSlug
{
    private string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $this->normalize($value);
    }

    private function validate(string $value): void
    {
        if (empty($value)) {
            throw new InvalidArgumentException('Shop slug cannot be empty.');
        }

        $normalized = $this->normalize($value);

        if (strlen($normalized) < 3) {
            throw new InvalidArgumentException('Shop slug must be at least 3 characters long.');
        }

        if (strlen($normalized) > 100) {
            throw new InvalidArgumentException('Shop slug cannot exceed 100 characters.');
        }

        if (!preg_match('/^[a-z0-9\-]+$/', $normalized)) {
            throw new InvalidArgumentException('Shop slug can only contain lowercase letters, numbers, and hyphens.');
        }

        if (preg_match('/^-|-$/', $normalized)) {
            throw new InvalidArgumentException('Shop slug cannot start or end with a hyphen.');
        }

        if (preg_match('/--+/', $normalized)) {
            throw new InvalidArgumentException('Shop slug cannot contain consecutive hyphens.');
        }
    }

    private function normalize(string $value): string
    {
        return strtolower(
            preg_replace(
                ['/[^a-z0-9\-]/', '/--+/', '/^-+|-+$/'],
                ['', '-', ''],
                strtolower(trim($value))
            )
        );
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(ShopSlug $other): bool
    {
        return $this->value === $other->value;
    }
}
