<?php

namespace Modules\Cart\Services;

use Illuminate\Support\Facades\DB;
use Modules\Cart\DTOs\AddToCartData;
use Modules\Cart\DTOs\UpdateCartItemData;
use Modules\Cart\Models\Cart;
use Modules\Cart\Models\CartItem;
use Modules\Cart\Models\GuestCart;
use Modules\Cart\Models\SavedCartItem;
use Modules\Product\Models\Product;
use Illuminate\Validation\ValidationException;

class CartItemService
{
    public function __construct(
        protected StockValidationService $stockValidation
    ) {}

    public function addItem($cart, AddToCartData $data)
    {
        $product = Product::findOrFail($data->product_id);

        // Validate stock
        if (!$this->stockValidation->validateStock($product, $data->quantity)) {
            throw ValidationException::withMessages([
                'quantity' => 'Insufficient stock available.'
            ]);
        }

        if ($cart instanceof Cart) {
            return $this->addToCustomerCart($cart, $product, $data->quantity);
        } else {
            // Guest cart
            return $this->addToGuestCart($data->cart_key, $product, $data->quantity);
        }
    }

    protected function addToCustomerCart(Cart $cart, Product $product, int $quantity): CartItem
    {
        return DB::transaction(function () use ($cart, $product, $quantity) {
            $existingItem = $cart->items()->where('product_id', $product->id)->first();

            if ($existingItem) {
                $existingItem->quantity += $quantity;
                $existingItem->save();
                return $existingItem;
            }

            return $cart->items()->create([
                'product_id' => $product->id,
                'vendor_id' => $product->vendor_id,
                'quantity' => $quantity,
                'price_at_add_time' => $product->price,
                'status' => 'available',
            ]);
        });
    }

    protected function addToGuestCart(string $cartKey, Product $product, int $quantity): GuestCart
    {
        return DB::transaction(function () use ($cartKey, $product, $quantity) {
            $existingItem = GuestCart::byCartKey($cartKey)
                ->where('product_id', $product->id)
                ->first();

            if ($existingItem) {
                $existingItem->quantity += $quantity;
                $existingItem->save();
                return $existingItem;
            }

            return GuestCart::create([
                'cart_key' => $cartKey,
                'product_id' => $product->id,
                'vendor_id' => $product->vendor_id,
                'quantity' => $quantity,
                'price_at_add_time' => $product->price,
                'status' => 'available',
            ]);
        });
    }

    public function updateQuantity(CartItem $item, int $quantity): CartItem
    {
        if ($quantity <= 0) {
            $item->delete();
            return $item;
        }

        // Validate stock
        if (!$this->stockValidation->validateStock($item->product, $quantity)) {
            throw ValidationException::withMessages([
                'quantity' => 'Insufficient stock available.'
            ]);
        }

        $item->quantity = $quantity;
        $item->save();

        return $item;
    }

    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    public function saveForLater(CartItem $item): SavedCartItem
    {
        return DB::transaction(function () use ($item) {
            $saved = SavedCartItem::create([
                'customer_id' => $item->cart->customer_id,
                'product_id' => $item->product_id,
                'vendor_id' => $item->vendor_id,
                'quantity' => $item->quantity,
                'price_at_add_time' => $item->price_at_add_time,
            ]);

            $item->delete();

            return $saved;
        });
    }
}
