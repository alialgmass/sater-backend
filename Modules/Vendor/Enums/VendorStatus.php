<?php

namespace Modules\Vendor\Enums;

enum VendorStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';

    /**
     * Get all status values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all status labels.
     */
    public static function labels(): array
    {
        return [
            self::PENDING->value => 'Pending',
            self::ACTIVE->value => 'Active',
            self::SUSPENDED->value => 'Suspended',
        ];
    }

    /**
     * Get status label.
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::ACTIVE => 'Active',
            self::SUSPENDED => 'Suspended',
        };
    }

    /**
     * Get status color for UI.
     */
    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::ACTIVE => 'success',
            self::SUSPENDED => 'danger',
        };
    }

    /**
     * Check if status is pending.
     */
    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * Check if status is active.
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Check if status is suspended.
     */
    public function isSuspended(): bool
    {
        return $this === self::SUSPENDED;
    }

    /**
     * Compare with another status.
     */
    public function equals(VendorStatus $other): bool
    {
        return $this === $other;
    }

    /**
     * Create from string value.
     */
    public static function fromString(string $value): self
    {
        return self::from($value);
    }
}
