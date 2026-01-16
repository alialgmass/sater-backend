<?php

namespace Modules\Checkout\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Checkout\DTOs\CheckoutStartDTO;
use Modules\Checkout\Services\CheckoutService;
use Modules\Checkout\Services\OrderCreationService;
use Modules\Checkout\Services\CouponService;
use Modules\Checkout\Services\PaymentService;
use Modules\Checkout\Models\CheckoutSession;
use Modules\Cart\Services\CartService;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService,
        protected OrderCreationService $orderService,
        protected CouponService $couponService,
        protected PaymentService $paymentService,
        protected CartService $cartService
    ) {}

    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'phone' => ['required', 'string'],
            'cart_key' => ['nullable', 'uuid'],
        ]);

        $data = CheckoutStartDTO::fromRequest($request);
        
        // Get cart
        $cart = $data->customer_id 
            ? $this->cartService->getOrCreateCart($request->user('api_customers'))
            : $this->cartService->getGuestCart($data->cart_key);

        $session = $this->checkoutService->startCheckout($cart, [
            'customer_id' => $data->customer_id,
            'cart_key' => $data->cart_key,
            'email' => $data->email,
            'phone' => $data->phone,
        ]);

        return response()->json([
            'message' => 'Checkout started successfully.',
            'session_key' => $session->session_key,
            'expires_at' => $session->expires_at,
        ], 201);
    }

    public function selectAddress(Request $request): JsonResponse
    {
        $request->validate([
            'session_key' => ['required', 'uuid', 'exists:checkout_sessions,session_key'],
            'address' => ['required', 'array'],
            'address.country' => ['required', 'string'],
            'address.city' => ['required', 'string'],
            'address.street' => ['required', 'string'],
        ]);

        $session = CheckoutSession::where('session_key', $request->session_key)->firstOrFail();
        $this->checkoutService->selectAddress($session, $request->address);

        return response()->json(['message' => 'Address selected successfully.']);
    }

    public function selectShipping(Request $request): JsonResponse
    {
        $request->validate([
            'session_key' => ['required', 'uuid'],
            'shipping_method' => ['required', 'in:standard,express'],
        ]);

        $session = CheckoutSession::where('session_key', $request->session_key)->firstOrFail();
        $this->checkoutService->selectShipping($session, $request->shipping_method);

        return response()->json(['message' => 'Shipping method selected successfully.']);
    }

    public function selectPayment(Request $request): JsonResponse
    {
        $request->validate([
            'session_key' => ['required', 'uuid'],
            'payment_method' => ['required', 'in:cod,online'],
        ]);

        $session = CheckoutSession::where('session_key', $request->session_key)->firstOrFail();
        $this->checkoutService->selectPayment($session, $request->payment_method);

        return response()->json(['message' => 'Payment method selected successfully.']);
    }

    public function applyCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'session_key' => ['required', 'uuid'],
            'coupon_code' => ['required', 'string'],
        ]);

        $session = CheckoutSession::where('session_key', $request->session_key)->firstOrFail();
        $this->checkoutService->applyCoupon($session, $request->coupon_code, $this->couponService);

        return response()->json(['message' => 'Coupon applied successfully.']);
    }

    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'session_key' => ['required', 'uuid'],
        ]);

        $session = CheckoutSession::where('session_key', $request->session_key)->firstOrFail();
        $summary = $this->checkoutService->getSummary($session);

        return response()->json($summary);
    }

    public function confirm(Request $request): JsonResponse
    {
        $request->validate([
            'session_key' => ['required', 'uuid'],
        ]);

        $session = CheckoutSession::where('session_key', $request->session_key)->firstOrFail();

        if ($session->isExpired()) {
            return response()->json(['message' => 'Checkout session expired.'], 400);
        }

        // Create order with multi-vendor splitting
        $masterOrder = $this->orderService->createOrder($session);

        // Initiate payments for vendor orders
        foreach ($masterOrder->vendorOrders as $vendorOrder) {
            $this->paymentService->initiatePayment($vendorOrder);
        }

        return response()->json([
            'message' => 'Order placed successfully.',
            'order_number' => $masterOrder->order_number,
            'total' => $masterOrder->total_amount,
        ], 201);
    }
}
