<?php

namespace Modules\Product\Http\Resources;
use App\Support\Api\Resources\WithPagination;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    use WithPagination;
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sku' => $this->sku,
            'price' => $this->price,
            'discounted_price' => $this->discounted_price,
            'stock' => $this->stock,
            'status' => $this->status,
            'attributes' => $this->attributes,
            'vendor' => [
                'id' => $this->vendor_id,
            ],
            'category' => [
                'id' => $this->category_id,
            ],
            'main_image' => $this->main_image?->getUrl(),
            'images' => $this->getMedia('images')->map(fn($media) => [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'properties' => $media->custom_properties,
                'is_main' => $media->id === $this->main_image?->id,
            ]),
        ];
    }
}
