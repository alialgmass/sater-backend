<?php

namespace Modules\Checkout\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Modules\Cart\Services\CartService;
use Modules\Checkout\Http\Requests\CheckoutRequest;
use Modules\Checkout\Models\CheckoutSession;
use Modules\Checkout\Repositories\Contracts\CheckoutSessionRepositoryInterface;
use Modules\Customer\Models\Customer;

class CheckoutService
{
    public function __construct(
        protected CheckoutSessionRepositoryInterface $sessionRepository,
        protected TaxCalculationService $taxService,
        protected ShippingCalculationService $shippingService,
        protected CouponService $couponService,
        protected CartService $cartService,
    ) {}

    /**
     * Orchestrate the full single-step checkout flow.
     * Returns the completed CheckoutSession ready for order creation.
     */
    public function placeOrder(CheckoutRequest $request, ?Customer $customer): CheckoutSession
    {
        // 1. Resolve cart (authenticated or guest)
        $cart = $request->cart_key
            ? $this->cartService->getGuestCart($request->cart_key)
            : $this->cartService->getOrCreateCart($customer);

        // 2. Create checkout session
        $session = $this->createSession($cart, $customer, $request);

        // 3. Address
        $this->sessionRepository->selectAddress($session, $request->address);
        $session->refresh();

        // 4. Shipping — calculate cost, persist
        $shippingCost = $this->shippingService->calculateShipping(
            [],
            $request->shipping_method,
            $request->address,
        );
        $this->sessionRepository->selectShipping($session, $request->shipping_method, $shippingCost);
        $session->refresh();

        // 5. Payment method
        $this->sessionRepository->selectPayment($session, $request->payment_method);

        // 6. Optional coupon
        if ($request->filled('coupon_code')) {
            $this->couponService->applyCoupon($request->coupon_code, $session);
            $session->refresh();
        }

        // 7. Recalculate totals
        $this->recalculateTotals($session);
        $session->refresh();

        return $session;
    }

    /**
     * Read-only summary for the given session.
     */
    public function getSummary(CheckoutSession $session): array
    {
        $session->load('appliedCoupons');

        return [
            'subtotal' => $session->subtotal,
            'tax'      => $session->tax,
            'shipping' => $session->shipping,
            'discount' => $session->discount,
            'total'    => $session->total,
            'coupons'  => $session->appliedCoupons,
        ];
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function createSession($cart, ?Customer $customer, CheckoutRequest $request): CheckoutSession
    {
        $items    = $cart instanceof \Modules\Cart\Models\Cart ? $cart->items : $cart;
        $subtotal = $items->sum(fn($item) => $item->product->price * $item->quantity);

        return $this->sessionRepository->create([
            'session_key' => (string) Str::uuid(),
            'customer_id' => $customer?->id,
            'cart_key'    => $request->cart_key,
            'email'       => $customer?->email,
            'phone'       => $customer?->phone,
            'subtotal'    => $subtotal,
            'total'       => $subtotal,
            'status'      => 'pending',
            'expires_at'  => Carbon::now()->addMinutes(30),
        ]);
    }

    private function recalculateTotals(CheckoutSession $session): void
    {
        $tax      = $this->taxService->calculateTax($session->subtotal, $session->shipping_address ?? []);
        $discount = $session->appliedCoupons->sum('discount_amount');

        $this->sessionRepository->recalculateTotals($session, $tax, $discount);
    }
}
