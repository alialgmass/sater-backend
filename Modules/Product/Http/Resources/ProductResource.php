<?php

namespace Modules\Product\Http\Resources;
use App\Support\Api\Resources\WithPagination;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Product\Models\Product;

/**
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    use WithPagination;
    public function toArray($request)
    {
        $price = $this->discounted_price ?: $this->price;
        $oldPrice = $this->discounted_price ? $this->price : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sku' => $this->sku,
            'price' => (float) $price,
            'old_price' => $oldPrice ? (float) $oldPrice : null,
            'stock' => $this->stock,
            'status' => $this->status,
            'is_new' => $this->created_at->gt(now()->subDays(7)),
            'rating' => (float) ($this->avg_rating ?? 0),
            'reviews_count' => (int) ($this->rating_count ?? 0),
            'colors' => $this->colors->map(fn($color) => $color->only(['id', 'name', 'hex_code'])),
            'sizes' => $this->sizes->map(fn($size) => $size->only(['id', 'name', 'abbreviation'])),
            'tags' => $this->tags->map(fn($tag) => $tag->only(['id', 'name', 'slug'])),
            'attributes' => $this->attributes,
            'vendor' => [
                'id' => $this->vendor_id,
                'name' => $this->vendor?->name,
                'shop_name' => $this->vendor?->shop_name,
            ],
            'category' => [
                'id' => $this->category_id,
                'name' => $this->category?->name,
                'slug' => $this->category?->slug,
            ],
            'main_image' => url($this->getFirstMediaUrl('main_image')),
            'images' => $this->getMedia('images')->map(fn($media) => [
                'id' => $media->id,
                'url' => url($media->getUrl()),
                'properties' => $media->custom_properties,
                'is_main' => $media->id === $this->main_image?->id,
            ]),
        ];
    }
}
