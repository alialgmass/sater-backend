<?php

namespace Modules\Order\Services;

use Modules\Order\Models\Order;
use Modules\Cart\Services\CartService; // Assuming this exists

class ReorderService
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function reorder(Order $order): array
    {
        $warnings = [];
        $addedItems = 0;

        foreach ($order->items as $item) {
            $product = $item->product;

            if (!$product || !$product->is_active) {
                $warnings[] = "Item '{$item->product_name}' is no longer available.";
                continue;
            }

            if ($product->stock < $item->quantity) {
                $warnings[] = "Insufficient stock for item '{$item->product_name}'.";
                continue;
            }

            if ($product->price !== $item->price) {
                $warnings[] = "Price for item '{$item->product_name}' has changed.";
            }

            $this->cartService->add($product->id, $item->quantity, $item->options);
            $addedItems++;
        }

        return [
            'message' => $addedItems > 0 ? 'Items added to your cart.' : 'No items were added to your cart.',
            'warnings' => $warnings,
        ];
    }
}
