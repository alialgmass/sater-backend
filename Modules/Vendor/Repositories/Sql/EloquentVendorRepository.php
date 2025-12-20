<?php

namespace Modules\Vendor\Repositories\Sql;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Vendor\DTOs\VendorDTO;
use Modules\Vendor\Enums\VendorStatus;
use Modules\Vendor\Models\Vendor;
use Modules\Vendor\Repositories\Contracts\VendorRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentVendorRepository implements VendorRepositoryInterface
{
    public function __construct(
        private Vendor $model
    ) {
    }

    /**
     * Create a new vendor.
     */
    public function create(VendorDTO $dto): Vendor
    {
        return $this->model->create($dto->toArray());
    }

    /**
     * Find vendor by ID.
     */
    public function findById(int $id): ?Vendor
    {
        return $this->model->find($id);
    }

    /**
     * Find vendor by phone number.
     */
    public function findByPhone(string $phone): ?Vendor
    {
        return $this->model->where('phone', $phone)->first();
    }

    /**
     * Find vendor by shop slug.
     */
    public function findBySlug(string $slug): ?Vendor
    {
        return $this->model->where('shop_slug', $slug)->first();
    }

    /**
     * Update vendor.
     */
    public function update(Vendor $vendor, array $data): Vendor
    {
        $vendor->update($data);
        return $vendor->fresh();
    }

    /**
     * Delete vendor (hard delete).
     */
    public function delete(Vendor $vendor): bool
    {
        return $vendor->forceDelete();
    }

    /**
     * Soft delete vendor.
     */
    public function softDelete(Vendor $vendor): bool
    {
        return $vendor->delete();
    }

    /**
     * Restore soft deleted vendor.
     */
    public function restore(int $id): bool
    {
        $vendor = $this->model->withTrashed()->find($id);

        if (!$vendor) {
            return false;
        }

        return $vendor->restore();
    }

    /**
     * Update vendor status.
     */
    public function updateStatus(int $vendorId, string $status): Vendor
    {
        $vendor = $this->model->findOrFail($vendorId);

        $vendor->update([
            'status' => VendorStatus::fromString($status)
        ]);

        return $vendor->fresh();
    }

    /**
     * List all active vendors.
     */
    public function listActiveVendors():LengthAwarePaginator
    {
        return $this->model
            ->active()
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    /**
     * List all pending vendors.
     */
    public function listPendingVendors(): Collection
    {
        return $this->model
            ->pending()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * List all suspended vendors.
     */
    public function listSuspendedVendors(): Collection
    {
        return $this->model
            ->suspended()
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * Get paginated vendors.
     */
    public function paginate(int $perPage = 15, array $filters = [])
    {
        $query = $this->model->query();

        // Apply status filter
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply date range filter
        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Apply search filter
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('shop_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('shop_slug', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Check if phone exists.
     */
    public function phoneExists(string $phone): bool
    {
        return $this->model->where('phone', $phone)->exists();
    }

    /**
     * Check if slug exists.
     */
    public function slugExists(string $slug): bool
    {
        return $this->model->where('shop_slug', $slug)->exists();
    }

    /**
     * Search vendors by query.
     */
    public function search(string $query): Collection
    {
        return $this->model
            ->where('name', 'like', "%{$query}%")
            ->orWhere('shop_name', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->orWhere('shop_slug', 'like', "%{$query}%")
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
