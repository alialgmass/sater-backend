<?php

namespace Modules\Payment\Models;

use Modules\Payment\Enums\PaymentStatusEnum;
use Modules\Payment\Enums\GatewayEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'attempt_number',
        'gateway',
        'status',
        'request_data',
        'response_data',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'processed_at' => 'datetime',
        'status' => PaymentStatusEnum::class,
        'gateway' => GatewayEnum::class,
    ];

    // Relationships
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    // Scopes
    public function scopeSuccessful($query)
    {
        return $query->where('status', PaymentStatusEnum::COMPLETED);
    }

    public function scopeFailed($query)
    {
        return $query->whereIn('status', [
            PaymentStatusEnum::FAILED,
            PaymentStatusEnum::CANCELLED,
            PaymentStatusEnum::EXPIRED,
        ]);
    }

    // Helper methods
    public function isSuccessful(): bool
    {
        return $this->status === PaymentStatusEnum::COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status->isFailure();
    }
}