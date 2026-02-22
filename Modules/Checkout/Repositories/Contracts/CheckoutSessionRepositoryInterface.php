<?php

namespace Modules\Checkout\Repositories\Contracts;

use Modules\Checkout\Models\CheckoutSession;

interface CheckoutSessionRepositoryInterface
{
    public function create(array $data): CheckoutSession;

    public function selectAddress(CheckoutSession $session, array $address): void;

    public function selectShipping(CheckoutSession $session, string $method, float $cost): void;

    public function selectPayment(CheckoutSession $session, string $method): void;

    public function recalculateTotals(CheckoutSession $session, float $tax, float $discount): void;

    public function markCompleted(CheckoutSession $session): void;
}
