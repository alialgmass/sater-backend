<?php

namespace Modules\Cart\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    protected $totals;

    public function __construct($resource, $totals = null)
    {
        parent::__construct($resource);
        $this->totals = $totals;
    }

    public function toArray($request): array
    {
        $items = $this->resource instanceof \Illuminate\Support\Collection 
            ? $this->resource 
            : $this->items;

        return [
            'items' => CartItemResource::collection($items),
            'items_count' => $items->count(),
            'totals' => $this->totals,
        ];
    }
}
