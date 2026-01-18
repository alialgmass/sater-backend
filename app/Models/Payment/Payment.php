<?php

namespace App\Models\Payment;

use App\Enums\Payment\PaymentMethodEnum;
use App\Enums\Payment\PaymentStatusEnum;
use App\Enums\Payment\GatewayEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'master_order_id',
        'vendor_order_id',
        'customer_id',
        'vendor_id',
        'gateway',
        'method',
        'status',
        'amount',
        'currency',
        'transaction_id',
        'reference_id',
        'gateway_response',
        'metadata',
        'failure_reason',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'method' => PaymentMethodEnum::class,
        'status' => PaymentStatusEnum::class,
        'gateway' => GatewayEnum::class,
    ];

    // Relationships
    public function vendorOrder(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Order\VendorOrder::class);
    }

    public function masterOrder(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Order\MasterOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Vendor::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(PaymentReceipt::class);
    }

    // Scopes
    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeByGateway($query, GatewayEnum $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    public function scopeByMethod($query, PaymentMethodEnum $method)
    {
        return $query->where('method', $method);
    }

    public function scopeByStatus($query, PaymentStatusEnum $status)
    {
        return $query->where('status', $status);
    }

    public function scopeCompleted($query)
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
    public function isCompleted(): bool
    {
        return $this->status === PaymentStatusEnum::COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status->isFailure();
    }

    public function isPending(): bool
    {
        return $this->status === PaymentStatusEnum::PENDING;
    }

    public function isOnlinePayment(): bool
    {
        return $this->method->isOnline();
    }

    public function isCashOnDelivery(): bool
    {
        return $this->method->isCashOnDelivery();
    }

    public function canBeRetried(): bool
    {
        return $this->isFailed() || $this->isPending();
    }
}