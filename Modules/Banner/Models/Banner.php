<?php

namespace Modules\Banner\Models;

use App\Support\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Banner\Enums\BannerStatusEnum;
use Modules\Product\Models\Product;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Banner extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, HasTranslations;

    protected $fillable = [
        'title',
        'description',
        'status',
        'sort_order',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'title' => 'json',
        'description' => 'json',
        'status' => BannerStatusEnum::class,
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public array $translatable = ['title', 'description'];

    /**
     * Relationship with Products
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'banner_product');
    }

    /**
     * Scope for active banners
     */
    public function scopeActive($query)
    {
        return $query->where('status', BannerStatusEnum::ACTIVE);
    }

    /**
     * Scope for currently running banners based on date range
     */
    public function scopeCurrentlyRunning($query)
    {
        $now = now();
        return $query->where(function ($q) use ($now) {
            $q->whereNull('starts_at')
                ->orWhere('starts_at', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('ends_at')
                ->orWhere('ends_at', '>=', $now);
        });
    }

    /**
     * Accessor for full image URL from Media Library
     */
    public function getFullImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('banners');
    }

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('banners')
            ->singleFile();
    }
}
