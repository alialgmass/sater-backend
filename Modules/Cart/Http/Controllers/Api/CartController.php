<?php

namespace Modules\Cart\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Cart\DTOs\AddToCartData;
use Modules\Cart\DTOs\UpdateCartItemData;
use Modules\Cart\Http\Requests\AddToCartRequest;
use Modules\Cart\Http\Requests\UpdateCartItemRequest;
use Modules\Cart\Models\CartItem;
use Modules\Cart\Services\CartService;
use Modules\Cart\Services\CartItemService;
use Modules\Cart\Services\CartPricingService;
use Modules\Cart\Services\StockValidationService;
use Modules\Cart\Transformers\CartResource;
use Modules\Cart\Transformers\CartItemResource;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected CartItemService $cartItemService,
        protected CartPricingService $pricingService,
        protected StockValidationService $stockValidation
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user('api_customers');
        
        if ($user) {
            // Authenticated customer
            $cart = $this->cartService->getOrCreateCart($user);
            $items = $cart->items()->with(['product', 'vendor'])->get();
        } else {
            // Guest user
            $cartKey = $request->header('X-Cart-Key');
            
            if (!$cartKey) {
                return response()->json([
                    'items' => [],
                    'items_count' => 0,
                    'totals' => null,
                ]);
            }
            
            $items = $this->cartService->getGuestCart($cartKey);
        }

        // Validate stock for all items
        $items = $this->stockValidation->validateCartItems($items);

        // Calculate totals
        $totals = $this->pricingService->calculateCartTotals($items);

        return response()->json(new CartResource($items, $totals));
    }

    public function add(AddToCartRequest $request): JsonResponse
    {
        $data = AddToCartData::fromRequest($request);
        $user = $request->user('api_customers');

        if ($user) {
            $cart = $this->cartService->getOrCreateCart($user);
        } else {
            // Generate cart key for guest if not provided
            if (!$data->cart_key) {
                $cartKey = (string) Str::uuid();
            } else {
                $cartKey = $data->cart_key;
            }
            $cart = $cartKey;
        }

        $item = $this->cartItemService->addItem($cart, $data);

        return response()->json([
            'message' => 'Item added to cart successfully.',
            'data' => new CartItemResource($item),
            'cart_key' => $user ? null : ($data->cart_key ?? $cartKey),
        ], 201);
    }

    public function updateItem(UpdateCartItemRequest $request, CartItem $item): JsonResponse
    {
        $this->authorize('update', $item);

        $data = UpdateCartItemData::fromRequest($request);
        $updatedItem = $this->cartItemService->updateQuantity($item, $data->quantity);

        if ($data->quantity <= 0) {
            return response()->json([
                'message' => 'Item removed from cart.',
            ]);
        }

        return response()->json([
            'message' => 'Cart item updated successfully.',
            'data' => new CartItemResource($updatedItem),
        ]);
    }

    public function removeItem(Request $request, CartItem $item): JsonResponse
    {
        $this->authorize('delete', $item);

        $this->cartItemService->removeItem($item);

        return response()->json([
            'message' => 'Item removed from cart successfully.',
        ]);
    }

    public function saveForLater(Request $request, CartItem $item): JsonResponse
    {
        $this->authorize('update', $item);

        $savedItem = $this->cartItemService->saveForLater($item);

        return response()->json([
            'message' => 'Item saved for later.',
            'data' => $savedItem,
        ]);
    }
}
