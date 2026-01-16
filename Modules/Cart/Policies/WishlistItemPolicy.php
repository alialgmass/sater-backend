<?php

namespace Modules\Cart\Policies;

use Modules\Auth\Models\Customer;
use Modules\Cart\Models\WishlistItem;

class WishlistItemPolicy
{
    public function delete(Customer $customer, WishlistItem $item): bool
    {
        return $customer->id === $item->wishlist->customer_id;
    }
}
