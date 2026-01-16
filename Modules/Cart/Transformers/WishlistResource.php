<?php

namespace Modules\Cart\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class WishlistResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'items' => WishlistItemResource::collection($this->items),
            'items_count' => $this->items->count(),
            'share_url' => $this->share_token ? url("/api/wishlist/shared/{$this->share_token}") : null,
        ];
    }
}
