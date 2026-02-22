<?php

namespace Modules\Checkout\Repositories;

use Modules\Checkout\Models\CheckoutSession;
use Modules\Checkout\Repositories\Contracts\CheckoutSessionRepositoryInterface;

class CheckoutSessionRepository implements CheckoutSessionRepositoryInterface
{
    public function create(array $data): CheckoutSession
    {
        return CheckoutSession::create($data);
    }

    public function selectAddress(CheckoutSession $session, array $address): void
    {
        $session->update([
            'shipping_address' => $address,
            'status'           => 'address_selected',
        ]);
    }

    public function selectShipping(CheckoutSession $session, string $method, float $cost): void
    {
        $session->update([
            'shipping_method' => $method,
            'shipping'        => $cost,
            'status'          => 'shipping_selected',
        ]);
    }

    public function selectPayment(CheckoutSession $session, string $method): void
    {
        $session->update([
            'payment_method' => $method,
            'status'         => 'payment_selected',
        ]);
    }

    public function recalculateTotals(CheckoutSession $session, float $tax, float $discount): void
    {
        $total = $session->subtotal + $tax + $session->shipping - $discount;

        $session->update([
            'tax'      => $tax,
            'discount' => $discount,
            'total'    => max(0, $total),
        ]);
    }

    public function markCompleted(CheckoutSession $session): void
    {
        $session->update(['status' => 'completed']);
    }
}
