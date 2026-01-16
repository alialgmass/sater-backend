<?php

namespace Modules\Checkout\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Checkout\Models\CheckoutSession;
use Modules\Checkout\Services\TaxCalculationService;
use Modules\Checkout\Services\ShippingCalculationService;
use Carbon\Carbon;

class CheckoutService
{
    public function __construct(
        protected TaxCalculationService $taxService,
        protected ShippingCalculationService $shippingService
    ) {}

    public function startCheckout($cart, array $data): CheckoutSession
    {
        $items = $cart instanceof \Modules\Cart\Models\Cart 
            ? $cart->items 
            : $cart; // Guest cart collection

        $subtotal = $items->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        return CheckoutSession::create([
            'session_key' => (string) Str::uuid(),
            'customer_id' => $data['customer_id'] ?? null,
            'cart_key' => $data['cart_key'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'],
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'status' => 'pending',
            'expires_at' => Carbon::now()->addMinutes(30),
        ]);
    }

    public function selectAddress(CheckoutSession $session, array $address): void
    {
        $session->update([
            'shipping_address' => $address,
            'status' => 'address_selected',
        ]);
    }

    public function selectShipping(CheckoutSession $session, string $method): void
    {
        $shippingCost = $this->shippingService->calculateShipping([], $method, $session->shipping_address ?? []);
        
        $session->update([
            'shipping_method' => $method,
            'shipping' => $shippingCost,
            'status' => 'shipping_selected',
        ]);

        $this->recalculateTotals($session);
    }

    public function selectPayment(CheckoutSession $session, string $method): void
    {
        $session->update([
            'payment_method' => $method,
            'status' => 'payment_selected',
        ]);
    }

    public function applyCoupon(CheckoutSession $session, string $code, CouponService $couponService): void
    {
        $couponService->applyCoupon($code, $session);
        $this->recalculateTotals($session);
    }

    public function getSummary(CheckoutSession $session): array
    {
        $session->load('appliedCoupons');

        return [
            'subtotal' => $session->subtotal,
            'tax' => $session->tax,
            'shipping' => $session->shipping,
            'discount' => $session->discount,
            'total' => $session->total,
            'coupons' => $session->appliedCoupons,
        ];
    }

    protected function recalculateTotals(CheckoutSession $session): void
    {
        $tax = $this->taxService->calculateTax($session->subtotal, $session->shipping_address ?? []);
        $discount = $session->appliedCoupons->sum('discount_amount');
        $total = $session->subtotal + $tax + $session->shipping - $discount;

        $session->update([
            'tax' => $tax,
            'discount' => $discount,
            'total' => max(0, $total),
        ]);
    }
}
