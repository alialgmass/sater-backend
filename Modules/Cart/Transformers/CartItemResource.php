<?php

namespace Modules\Cart\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product->name,
            'product_image' => $this->product->image_url ?? null,
            'quantity' => $this->quantity,
            'price' => $this->product->price, // Current price
            'price_at_add_time' => $this->price_at_add_time,
            'subtotal' => $this->product->price * $this->quantity,
            'status' => $this->status->value,
            'vendor_id' => $this->vendor_id,
            'vendor_name' => $this->vendor->name ?? 'Unknown',
        ];
    }
}
