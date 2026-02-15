<?php

namespace Modules\Vendor\Models;

use Filament\Panel;
use App\Support\Traits\HasRoles;
use Spatie\MediaLibrary\HasMedia;
use Modules\Product\Models\Product;
use Illuminate\Support\Facades\Hash;
use Modules\Vendor\Enums\VendorStatus;
use App\Support\Media\InteractsWithMedia;
use Modules\Vendor\ValueObjects\ShopSlug;
use Filament\Models\Contracts\FilamentUser;
use Modules\Vendor\ValueObjects\PhoneNumber;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Vendor extends Authenticatable implements HasMedia, FilamentUser
{
    use HasFactory, SoftDeletes, InteractsWithMedia, HasRoles;

    protected static function newFactory()
    {
        return \Modules\Vendor\Database\Factories\VendorFactory::new();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'vendor' && $this->isActive();
    }
    protected $fillable = [
        'name',
        'phone',
        'password',
        'shop_name',
        'shop_slug',
        'whatsapp',
        'description',
        'logo',
        'cover',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'status' => VendorStatus::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vendor) {
            if (empty($vendor->status)) {
                $vendor->status = VendorStatus::PENDING;
            }
        });
    }

    /**
     * Set the password attribute with hashing.
     */
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Set the phone attribute with PhoneNumber value object.
     */
    public function setPhoneAttribute($value): void
    {
        $phoneNumber = new PhoneNumber($value);
        $this->attributes['phone'] = (string) $phoneNumber;
    }

    /**
     * Set the shop_slug attribute with ShopSlug value object.
     */
    public function setShopSlugAttribute($value): void
    {
        $shopSlug = new ShopSlug($value);
        $this->attributes['shop_slug'] = (string) $shopSlug;
    }

    /**
     * Get the logo URL.
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    /**
     * Get the cover URL.
     */
    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover ? asset('storage/' . $this->cover) : null;
    }

    /**
     * Relationship: Vendor has many products.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Scope: Get only active vendors.
     */
    public function scopeActive($query)
    {
        return $query->where('status', VendorStatus::ACTIVE);
    }

    /**
     * Scope: Get pending vendors.
     */
    public function scopePending($query)
    {
        return $query->where('status', VendorStatus::PENDING);
    }

    /**
     * Scope: Get suspended vendors.
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', VendorStatus::SUSPENDED);
    }

    /**
     * Check if vendor is active.
     */
    public function isActive(): bool
    {
        return $this->status->equals(VendorStatus::ACTIVE);
    }

    /**
     * Check if vendor is pending.
     */
    public function isPending(): bool
    {
        return $this->status->equals(VendorStatus::PENDING);
    }

    /**
     * Check if vendor is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status->equals(VendorStatus::SUSPENDED);
    }

    /**
     * Activate the vendor.
     */
    public function activate(): void
    {
        $this->update(['status' => VendorStatus::ACTIVE]);
    }

    /**
     * Suspend the vendor.
     */
    public function suspend(): void
    {
        $this->update(['status' => VendorStatus::SUSPENDED]);
    }
}
