<?php

namespace Modules\Cart\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    protected $totals;
    protected $cartKey;

    public function __construct($resource, $totals = null, $cartKey = null)
    {
        parent::__construct($resource);
        $this->totals = $totals;
        $this->cartKey = $cartKey;
    }

    public function toArray($request): array
    {
        $items = $this->resource instanceof \Illuminate\Support\Collection 
            ? $this->resource 
            : $this->items;

        return [
            'items' => CartItemResource::collection($items),
            'items_count' => $items->count(),
            'cart_key' => $this->cartKey,
            'subtotal' => $this->totals['subtotal'] ?? 0,
            'shipping' => $this->totals['shipping'] ?? 0,
            'tax' => $this->totals['tax'] ?? 0,
            'total' => $this->totals['grand_total'] ?? 0,
            'vendors_breakdown' => $this->totals['vendors'] ?? [],
        ];
    }
}
