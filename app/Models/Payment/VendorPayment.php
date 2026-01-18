<?php

namespace App\Models\Payment;

use App\Enums\Payment\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VendorPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_order_id',
        'total_amount',
        'payment_status',
        'last_payment_attempt_id',
        'paid_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'payment_status' => PaymentStatusEnum::class,
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function vendorOrder(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Order\VendorOrder::class);
    }

    public function lastPaymentAttempt(): BelongsTo
    {
        return $this->belongsTo(PaymentAttempt::class);
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('payment_status', PaymentStatusEnum::COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', PaymentStatusEnum::PENDING);
    }

    public function scopeFailed($query)
    {
        return $query->whereIn('payment_status', [
            PaymentStatusEnum::FAILED,
            PaymentStatusEnum::CANCELLED,
            PaymentStatusEnum::EXPIRED,
        ]);
    }

    // Helper methods
    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatusEnum::COMPLETED;
    }

    public function isPending(): bool
    {
        return $this->payment_status === PaymentStatusEnum::PENDING;
    }

    public function isFailed(): bool
    {
        return $this->payment_status->isFailure();
    }
}