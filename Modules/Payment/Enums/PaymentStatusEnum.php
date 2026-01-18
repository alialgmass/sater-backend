<?php

namespace Modules\Payment\Enums;

enum PaymentStatusEnum: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
            self::PARTIALLY_REFUNDED => 'Partially Refunded',
            self::EXPIRED => 'Expired',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [
            self::PENDING,
            self::PROCESSING,
        ]);
    }

    public function isFinal(): bool
    {
        return in_array($this, [
            self::COMPLETED,
            self::FAILED,
            self::CANCELLED,
            self::REFUNDED,
            self::PARTIALLY_REFUNDED,
            self::EXPIRED,
        ]);
    }

    public function isSuccess(): bool
    {
        return $this === self::COMPLETED;
    }

    public function isFailure(): bool
    {
        return in_array($this, [
            self::FAILED,
            self::CANCELLED,
            self::EXPIRED,
        ]);
    }
}