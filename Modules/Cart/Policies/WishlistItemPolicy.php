<?php

namespace Modules\Cart\Policies;

use Modules\Cart\Models\WishlistItem;
use Modules\Customer\Models\Customer;

class WishlistItemPolicy
{
    public function delete(Customer $customer, WishlistItem $item): bool
    {
        return $customer->id === $item->wishlist->customer_id;
    }
}
