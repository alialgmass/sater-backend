<?php

namespace Modules\Vendor\ValueObjects;

use InvalidArgumentException;

class PhoneNumber
{
    private string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $this->normalize($value);
    }

    private function validate(string $value): void
    {
        $normalized = $this->normalize($value);

        if (empty($normalized)) {
            throw new InvalidArgumentException('Phone number cannot be empty.');
        }

        if (strlen($normalized) < 10 || strlen($normalized) > 15) {
            throw new InvalidArgumentException('Phone number must be between 10 and 15 digits.');
        }

        if (!preg_match('/^\+?[0-9]+$/', $normalized)) {
            throw new InvalidArgumentException('Phone number contains invalid characters.');
        }
    }

    private function normalize(string $value): string
    {
        // Remove spaces, dashes, and parentheses
        return preg_replace('/[\s\-\(\)]/', '', $value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(PhoneNumber $other): bool
    {
        return $this->value === $other->value;
    }
}
