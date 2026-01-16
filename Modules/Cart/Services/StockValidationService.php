<?php

namespace Modules\Cart\Services;

use Modules\Product\Models\Product;

class StockValidationService
{
    public function validateStock(Product $product, int $quantity): bool
    {
        // Check if product has stock tracking enabled
        if (!isset($product->stock)) {
            return true; // No stock tracking
        }

        return $product->stock >= $quantity;
    }

    public function validateCartItems($items)
    {
        return $items->map(function ($item) {
            $product = $item->product;
            
            if (!$this->validateStock($product, $item->quantity)) {
                $item->status = \Modules\Cart\Enums\CartItemStatusEnum::OUT_OF_STOCK;
                $item->save();
            } else {
                $item->status = \Modules\Cart\Enums\CartItemStatusEnum::AVAILABLE;
                $item->save();
            }
            
            return $item;
        });
    }
}
