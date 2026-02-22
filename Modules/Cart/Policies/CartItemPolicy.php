<?php

namespace Modules\Cart\Policies;

use Modules\Cart\Models\CartItem;
use Modules\Customer\Models\Customer;

class CartItemPolicy
{
    public function update(Customer $customer, CartItem $item): bool
    {
        return $customer->id === $item->cart->customer_id;
    }

    public function delete(Customer $customer, CartItem $item): bool
    {
        return $customer->id === $item->cart->customer_id;
    }
}
