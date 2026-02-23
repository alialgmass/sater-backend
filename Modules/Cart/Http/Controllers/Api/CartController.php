<?php

namespace Modules\Cart\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
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

class CartController extends ApiController
{
    public function __construct(
        protected CartService $cartService,
        protected CartItemService $cartItemService,
        protected CartPricingService $pricingService,
        protected StockValidationService $stockValidation
    ) {
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user('api_customers');
        $cartKey = $request->header('X-Cart-Key') ?? $request->input('cart_key');
        
        if ($user) {
            // Authenticated customer
            if ($cartKey) {
                $this->cartService->mergeGuestCartToCustomer($cartKey, $user);
            }
            $cart = $this->cartService->getOrCreateCart($user);
            $items = $cart->items()->with(['product', 'vendor'])->get();
        } else {
            // Guest user
            if (!$cartKey) {
                return $this->apiBody([
                    'items' => [],
                    'items_count' => 0,
                    'totals' => null,
                ])->apiResponse();
            }
            
            $items = $this->cartService->getGuestCart($cartKey);
        }

        // Validate stock for all items
        $items = $this->stockValidation->validateCartItems($items);

        // Calculate totals
        $totals = $this->pricingService->calculateCartTotals($items);

        $cartData = new CartResource($items, $totals, $user ? null : $cartKey);

        return $this->apiBody([
            'cart' => $cartData
        ])->apiResponse();
    }

    public function add(AddToCartRequest $request): JsonResponse
    {
        $data = AddToCartData::fromRequest($request);
        $user = $request->user('api_customers');

        if ($user) {
            if ($data->cart_key) {
                $this->cartService->mergeGuestCartToCustomer($data->cart_key, $user);
            }
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

        return $this->apiMessage('Item added to cart successfully.')
            ->apiBody([
                'cart_item' => new CartItemResource($item),
                'cart_key' => $user ? null : ($data->cart_key ?? $cartKey),
            ])
            ->apiCode(201)
            ->apiResponse();
    }

    public function updateItem(UpdateCartItemRequest $request, $id): JsonResponse
    {
        $data = UpdateCartItemData::fromRequest($request);
        $user = $request->user('api_customers');
        $cartKey = $request->header('X-Cart-Key') ?? $request->input('cart_key');

        $item = $this->resolveCartItem($id, $user, $cartKey);

        if (!$item) {
            return $this->apiMessage('Item not found in your cart.')->apiCode(404)->apiResponse();
        }

        $updatedItem = $this->cartItemService->updateQuantity($item, $data->quantity);

        if ($data->quantity <= 0) {
            return $this->apiMessage('Item removed from cart.')
                ->apiResponse();
        }

        return $this->apiMessage('Cart item updated successfully.')
            ->apiBody([
                'cart_item' => new CartItemResource($updatedItem)
            ])
            ->apiResponse();
    }

    public function removeItem(Request $request, $id): JsonResponse
    {
        $user = $request->user('api_customers');
        $cartKey = $request->header('X-Cart-Key') ?? $request->input('cart_key');

        $item = $this->resolveCartItem($id, $user, $cartKey);

        if (!$item) {
            return $this->apiMessage('Item not found in your cart.')->apiCode(404)->apiResponse();
        }

        $this->cartItemService->removeItem($item);

        return $this->apiMessage('Item removed from cart successfully.')
            ->apiResponse();
    }

    public function clear(Request $request): JsonResponse
    {
        $user = $request->user('api_customers');
        $cartKey = $request->header('X-Cart-Key') ?? $request->input('cart_key');

        if ($user) {
            $cart = $this->cartService->getOrCreateCart($user);
            $this->cartItemService->clearCart($cart);
        } elseif ($cartKey) {
            $this->cartItemService->clearCart($cartKey);
        }

        return $this->apiMessage('Cart cleared successfully.')
            ->apiResponse();
    }

    public function saveForLater(Request $request, $id): JsonResponse
    {
        $user = $request->user('api_customers');
        $item = CartItem::whereHas('cart', fn($q) => $q->where('customer_id', $user->id))->findOrFail($id);

        $savedItem = $this->cartItemService->saveForLater($item);

        return $this->apiMessage('Item saved for later.')
            ->apiBody([
                'saved_item' => $savedItem
            ])
            ->apiResponse();
    }

    protected function resolveCartItem($id, $user, $cartKey)
    {
        if ($user) {
            return CartItem::whereHas('cart', fn($q) => $q->where('customer_id', $user->id))->find($id);
        }
        
        if ($cartKey) {
            return \Modules\Cart\Models\GuestCart::byCartKey($cartKey)->find($id);
        }

        return null;
    }
}
