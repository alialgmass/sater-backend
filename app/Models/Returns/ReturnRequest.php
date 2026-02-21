<?php

namespace App\Models\Returns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Order\Models\OrderItem;
use App\Models\User;

class ReturnRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'customer_id',
        'vendor_id',
        'reason',
        'description',
        'status',
        'requested_quantity',
        'approved_quantity',
        'refund_amount',
        'images',
        'admin_notes',
        'vendor_notes',
    ];

    protected $casts = [
        'images' => 'array',
        'requested_quantity' => 'integer',
        'approved_quantity' => 'integer',
        'refund_amount' => 'decimal:2',
    ];

    // Relationships
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
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