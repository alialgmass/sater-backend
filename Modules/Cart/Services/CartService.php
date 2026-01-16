<?php

namespace Modules\Cart\Services;

use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\Customer;
use Modules\Cart\Models\Cart;
use Modules\Cart\Models\GuestCart;
use Illuminate\Support\Collection;

class CartService
{
    public function getOrCreateCart(Customer $customer): Cart
    {
        return Cart::firstOrCreate(['customer_id' => $customer->id]);
    }

    public function getGuestCart(string $cartKey): Collection
    {
        return GuestCart::byCartKey($cartKey)->with(['product', 'vendor'])->get();
    }

    public function mergeGuestCartToCustomer(string $cartKey, Customer $customer): void
    {
        DB::transaction(function () use ($cartKey, $customer) {
            $guestItems = GuestCart::byCartKey($cartKey)->get();
            
            if ($guestItems->isEmpty()) {
                return;
            }

            $cart = $this->getOrCreateCart($customer);

            foreach ($guestItems as $guestItem) {
                // Check if item already exists in customer cart
                $existingItem = $cart->items()
                    ->where('product_id', $guestItem->product_id)
                    ->first();

                if ($existingItem) {
                    // Sum quantities
                    $existingItem->quantity += $guestItem->quantity;
                    $existingItem->save();
                } else {
                    // Create new cart item
                    $cart->items()->create([
                        'product_id' => $guestItem->product_id,
                        'vendor_id' => $guestItem->vendor_id,
                        'quantity' => $guestItem->quantity,
                        'price_at_add_time' => $guestItem->price_at_add_time,
                        'status' => $guestItem->status,
                    ]);
                }
            }

            // Delete guest cart items
            GuestCart::byCartKey($cartKey)->delete();
        });
    }

    public function clearCart($cart): void
    {
        if ($cart instanceof Cart) {
            $cart->items()->delete();
        } elseif (is_string($cart)) {
            // Cart key for guest
            GuestCart::byCartKey($cart)->delete();
        }
    }
}
