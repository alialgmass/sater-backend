<?php

namespace Modules\Cart\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Cart\Models\Wishlist;
use Modules\Cart\Models\WishlistItem;
use Modules\Cart\Services\WishlistService;
use Modules\Cart\Services\CartItemService;
use Modules\Cart\Transformers\WishlistResource;
use Modules\Product\Models\Product;

class WishlistController extends Controller
{
    public function __construct(
        protected WishlistService $wishlistService,
        protected CartItemService $cartItemService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $customer = $request->user();
        $wishlist = $this->wishlistService->getOrCreateWishlist($customer);
        $wishlist->load('items.product');

        return response()->json(new WishlistResource($wishlist));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $customer = $request->user();
        $product = Product::findOrFail($request->product_id);
        $wishlist = $this->wishlistService->getOrCreateWishlist($customer);

        $item = $this->wishlistService->addItem($wishlist, $product);

        return response()->json([
            'message' => 'Product added to wishlist.',
            'data' => $item,
        ], 201);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $customer = $request->user();
        $wishlist = $this->wishlistService->getOrCreateWishlist($customer);
        
        $item = $wishlist->items()->where('product_id', $product->id)->firstOrFail();
        $this->authorize('delete', $item);

        $this->wishlistService->removeItem($item);

        return response()->json([
            'message' => 'Product removed from wishlist.',
        ]);
    }

    public function share(Request $request): JsonResponse
    {
        $customer = $request->user();
        $wishlist = $this->wishlistService->getOrCreateWishlist($customer);

        $shareUrl = $this->wishlistService->generateShareableLink($wishlist);

        return response()->json([
            'share_url' => $shareUrl,
        ]);
    }

    public function viewShared(string $token): JsonResponse
    {
        $wishlist = Wishlist::where('share_token', $token)->firstOrFail();
        $wishlist->load('items.product');

        return response()->json(new WishlistResource($wishlist));
    }

    public function moveToCart(Request $request, Product $product): JsonResponse
    {
        $customer = $request->user();
        $wishlist = $this->wishlistService->getOrCreateWishlist($customer);
        
        $item = $wishlist->items()->where('product_id', $product->id)->firstOrFail();
        $this->authorize('delete', $item);

        $cartItem = $this->wishlistService->moveToCart($item, $customer, $this->cartItemService);

        return response()->json([
            'message' => 'Product moved to cart.',
            'data' => $cartItem,
        ]);
    }
}
