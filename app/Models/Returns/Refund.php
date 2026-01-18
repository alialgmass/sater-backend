<?php

namespace App\Models\Returns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Returns\ReturnRequest;
use App\Models\User;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_request_id',
        'customer_id',
        'vendor_id',
        'order_id',
        'amount',
        'currency',
        'status',
        'gateway',
        'transaction_id',
        'reference_id',
        'gateway_response',
        'processed_by',
        'processed_at',
        'failure_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'processed_at' => 'datetime',
    ];

    // Relationships
    public function returnRequest(): BelongsTo
    {
        return $this->belongsTo(ReturnRequest::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeForVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }
}