<?php

namespace Modules\Order\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Order\Enums\VendorOrderStatusEnum;

class VendorOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'master_order_id',
        'vendor_id',
        'vendor_order_number',
        'status',
        'total_amount',
        'currency',
        'shipping_address',
        'shipping_method',
        'notes',
        'is_cod',
        'cod_amount',
        'confirmed_at',
        'processing_started_at',
        'packed_at',
        'shipped_at',
        'delivered_at',
        'cod_confirmed',
    ];

    protected $casts = [
        'shipping_address' => 'json',
        'status' => VendorOrderStatusEnum::class,
        'is_cod' => 'boolean',
        'cod_confirmed' => 'boolean',
        'confirmed_at' => 'datetime',
        'processing_started_at' => 'datetime',
        'packed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    protected $appends = [
        'fulfillment_duration',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->vendor_order_number)) {
                $model->vendor_order_number = self::generateVendorOrderNumber();
            }
        });
    }

    public function masterOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'master_order_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }

    public function getFulfillmentDurationAttribute(): ?int
    {
        if (!$this->confirmed_at || !$this->delivered_at) {
            return null;
        }

        return $this->confirmed_at->diffInMinutes($this->delivered_at);
    }

    private static function generateVendorOrderNumber(): string
    {
        $prefix = 'VO';
        $timestamp = now()->format('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));

        return $prefix . $timestamp . $random;
    }
}
