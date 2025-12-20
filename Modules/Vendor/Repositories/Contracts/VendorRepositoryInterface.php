<?php

namespace Modules\Vendor\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Vendor\DTOs\VendorDTO;
use Modules\Vendor\Models\Vendor;

interface VendorRepositoryInterface
{
    /**
     * Create a new vendor.
     */
    public function create(VendorDTO $dto): Vendor;

    /**
     * Find vendor by ID.
     */
    public function findById(int $id): ?Vendor;

    /**
     * Find vendor by phone number.
     */
    public function findByPhone(string $phone): ?Vendor;

    /**
     * Find vendor by shop slug.
     */
    public function findBySlug(string $slug): ?Vendor;

    /**
     * Update vendor.
     */
    public function update(Vendor $vendor, array $data): Vendor;

    /**
     * Delete vendor.
     */
    public function delete(Vendor $vendor): bool;

    /**
     * Soft delete vendor.
     */
    public function softDelete(Vendor $vendor): bool;

    /**
     * Restore soft deleted vendor.
     */
    public function restore(int $id): bool;

    /**
     * Update vendor status.
     */
    public function updateStatus(int $vendorId, string $status): Vendor;

    /**
     * List all active vendors.
     */
    public function listActiveVendors(): LengthAwarePaginator;

    /**
     * List all pending vendors.
     */
    public function listPendingVendors(): Collection;

    /**
     * List all suspended vendors.
     */
    public function listSuspendedVendors(): Collection;

    /**
     * Get paginated vendors.
     */
    public function paginate(int $perPage = 15, array $filters = []);

    /**
     * Check if phone exists.
     */
    public function phoneExists(string $phone): bool;

    /**
     * Check if slug exists.
     */
    public function slugExists(string $slug): bool;

    /**
     * Search vendors by query.
     */
    public function search(string $query): Collection;
}
