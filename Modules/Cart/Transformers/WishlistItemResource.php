<?php

namespace Modules\Cart\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class WishlistItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product->name,
            'product_image' => $this->product->image_url ?? null,
            'product_price' => $this->product->price,
            'added_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
