<?php

namespace Modules\Product\Http\Resources;

use App\Support\Api\Resources\WithPagination;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for search results
 *
 * Optimized for search API responses with essential product info
 */
class ProductSearchResource extends JsonResource
{
    use WithPagination;

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => (float) $this->price,
            'discounted_price' => $this->discounted_price ? (float) $this->discounted_price : null,
            'discount_percentage' => $this->getDiscountPercentage(),
            'stock' => $this->stock,
            'in_stock' => $this->stock > 0,
            'sku' => $this->sku,
            'rating' => [
                'average' => (float) $this->avg_rating,
                'count' => $this->rating_count,
            ],
            'popularity' => $this->sales_count,
            'vendor' => [
                'id' => $this->vendor_id,
                'name' => $this->vendor?->name,
                'shop_name' => $this->vendor?->shop_name,
                'shop_slug' => $this->vendor?->shop_slug,
            ],
            'category' => [
                'id' => $this->category_id,
                'name' => $this->category?->name,
            ],
            'image' => $this->getMainImage(),
        ];
    }

    /**
     * Calculate discount percentage
     */
    protected function getDiscountPercentage(): ?float
    {
        if (!$this->discounted_price || $this->discounted_price >= $this->price) {
            return null;
        }

        return round((1 - ($this->discounted_price / $this->price)) * 100, 2);
    }

    /**
     * Get main product image
     */
    protected function getMainImage(): ?string
    {
        return $this->getFirstMediaUrl('main_image') ?: null;
    }
}
