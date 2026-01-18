<?php

namespace Modules\Order\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Order\Models\VendorOrder;

class VendorOrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any vendor orders.
     */
    public function viewAny(User $user): bool
    {
        // Only vendors can access vendor orders
        return $user->hasRole('vendor');
    }

    /**
     * Determine whether the user can view the vendor order.
     */
    public function view(User $user, VendorOrder $vendorOrder): bool
    {
        // Only the owner of the vendor order can view it
        return $user->id === $vendorOrder->vendor_id;
    }

    /**
     * Determine whether the user can update the vendor order.
     */
    public function update(User $user, VendorOrder $vendorOrder): bool
    {
        // Only the owner of the vendor order can update it
        return $user->id === $vendorOrder->vendor_id;
    }

    /**
     * Determine whether the user can delete the vendor order.
     */
    public function delete(User $user, VendorOrder $vendorOrder): bool
    {
        // Only the owner of the vendor order can delete it
        return $user->id === $vendorOrder->vendor_id;
    }

    /**
     * Determine whether the user can update the status of the vendor order.
     */
    public function updateStatus(User $user, VendorOrder $vendorOrder): bool
    {
        // Only the owner of the vendor order can update its status
        return $user->id === $vendorOrder->vendor_id;
    }

    /**
     * Determine whether the user can add shipping info to the vendor order.
     */
    public function addShippingInfo(User $user, VendorOrder $vendorOrder): bool
    {
        // Only the owner of the vendor order can add shipping info
        return $user->id === $vendorOrder->vendor_id;
    }

    /**
     * Determine whether the user can generate packing slip for the vendor order.
     */
    public function generatePackingSlip(User $user, VendorOrder $vendorOrder): bool
    {
        // Only the owner of the vendor order can generate packing slip
        return $user->id === $vendorOrder->vendor_id;
    }
}