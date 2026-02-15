<?php

namespace Modules\Cart\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Cart\Models\Wishlist;
use Modules\Cart\Models\WishlistItem;
use Modules\Cart\Services\WishlistService;
use Modules\Cart\Services\CartItemService;
use Modules\Cart\Transformers\WishlistResource;
use Modules\Product\Models\Product;

class WishlistController extends ApiController
{
    public function __construct(
        protected WishlistService $wishlistService,
        protected CartItemService $cartItemService
    ) {
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $customer = $request->user();
        $wishlist = $this->wishlistService->getOrCreateWishlist($customer);
        $wishlist->load('items.product');

        return $this->apiBody([
            'wishlist' => new WishlistResource($wishlist)
        ])->apiResponse();
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

        return $this->apiMessage('Product added to wishlist.')
            ->apiBody(['wishlist_item' => $item])
            ->apiCode(201)
            ->apiResponse();
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $customer = $request->user();
        $wishlist = $this->wishlistService->getOrCreateWishlist($customer);
        
        $item = $wishlist->items()->where('product_id', $product->id)->firstOrFail();
        $this->authorize('delete', $item);

        $this->wishlistService->removeItem($item);

        return $this->apiMessage('Product removed from wishlist.')
            ->apiResponse();
    }

    public function share(Request $request): JsonResponse
    {
        $customer = $request->user();
        $wishlist = $this->wishlistService->getOrCreateWishlist($customer);

        $shareUrl = $this->wishlistService->generateShareableLink($wishlist);

        return $this->apiBody(['share_url' => $shareUrl])
            ->apiResponse();
    }

    public function viewShared(string $token): JsonResponse
    {
        $wishlist = Wishlist::where('share_token', $token)->firstOrFail();
        $wishlist->load('items.product');

        return $this->apiBody([
            'wishlist' => new WishlistResource($wishlist)
        ])->apiResponse();
    }

    public function moveToCart(Request $request, Product $product): JsonResponse
    {
        $customer = $request->user();
        $wishlist = $this->wishlistService->getOrCreateWishlist($customer);
        
        $item = $wishlist->items()->where('product_id', $product->id)->firstOrFail();
        $this->authorize('delete', $item);

        $cartItem = $this->wishlistService->moveToCart($item, $customer, $this->cartItemService);

        return $this->apiMessage('Product moved to cart.')
            ->apiBody(['cart_item' => $cartItem])
            ->apiResponse();
    }
}
