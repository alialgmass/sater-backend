<?php

namespace Modules\Vendor\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'shop_name' => $this->shop_name,
            'shop_slug' => $this->shop_slug,
            'whatsapp' => $this->whatsapp,
            'description' => $this->description,
            'logo' => $this->logo_url,
            'cover' => $this->cover_url,
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'color' => $this->status->color(),
            ],
            'is_active' => $this->isActive(),
            'products_count' => $this->whenLoaded('products', function () {
                return $this->products->count();
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];


    }
}
