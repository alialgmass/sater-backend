<?php

namespace Modules\Cart\Services;

use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\Customer;
use Modules\Cart\Models\Wishlist;
use Modules\Cart\Models\WishlistItem;
use Modules\Product\Models\Product;
use Illuminate\Validation\ValidationException;

class WishlistService
{
    public function getOrCreateWishlist(Customer $customer): Wishlist
    {
        return Wishlist::firstOrCreate(['customer_id' => $customer->id]);
    }

    public function addItem(Wishlist $wishlist, Product $product): WishlistItem
    {
        // Check if already exists
        $existing = $wishlist->items()->where('product_id', $product->id)->first();
        
        if ($existing) {
            throw ValidationException::withMessages([
                'product_id' => 'Product already in wishlist.'
            ]);
        }

        return $wishlist->items()->create([
            'product_id' => $product->id,
        ]);
    }

    public function removeItem(WishlistItem $item): void
    {
        $item->delete();
    }

    public function moveToCart(WishlistItem $item, Customer $customer, CartItemService $cartItemService)
    {
        return DB::transaction(function () use ($item, $customer, $cartItemService) {
            $product = $item->product;

            // Add to cart
            $cart = app(CartService::class)->getOrCreateCart($customer);
            $cartItem = $cartItemService->addItem($cart, new \Modules\Cart\DTOs\AddToCartData(
                product_id: $product->id,
                quantity: 1
            ));

            // Remove from wishlist
            $item->delete();

            return $cartItem;
        });
    }

    public function generateShareableLink(Wishlist $wishlist): string
    {
        if (!$wishlist->share_token) {
            $wishlist->generateShareToken();
        }

        return url("/api/wishlist/shared/{$wishlist->share_token}");
    }
}
