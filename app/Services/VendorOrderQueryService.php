<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Order\Enums\VendorOrderStatusEnum;
use Modules\Order\Models\VendorOrder;

class VendorOrderQueryService
{
    /**
     * Get paginated vendor orders with filters
     */
    public function getPaginatedVendorOrdersForVendor(
        int $vendorId,
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = $this->buildBaseQuery($vendorId);

        $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * Get a specific vendor order by vendor ID and order number
     */
    public function getVendorOrderByNumber(int $vendorId, string $vendorOrderNumber): ?VendorOrder
    {
        return $this->buildBaseQuery($vendorId)
            ->where('vendor_order_number', $vendorOrderNumber)
            ->first();
    }

    /**
     * Get vendor orders by vendor ID and order numbers
     */
    public function getVendorOrdersByNumbers(int $vendorId, array $vendorOrderNumbers): \Illuminate\Database\Eloquent\Collection
    {
        return $this->buildBaseQuery($vendorId)
            ->whereIn('vendor_order_number', $vendorOrderNumbers)
            ->get();
    }

    /**
     * Build base query with vendor filter
     */
    private function buildBaseQuery(int $vendorId): Builder
    {
        return VendorOrder::with(['items', 'shipment', 'masterOrder'])
            ->where('vendor_id', $vendorId)
            ->orderByDesc('created_at');
    }

    /**
     * Apply filters to the query
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        // Filter by status
        if (isset($filters['status']) && !empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by date range
        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Filter COD orders only
        if (isset($filters['cod_only']) && $filters['cod_only']) {
            $query->where('is_cod', true);
        }

        // Search by vendor order number
        if (isset($filters['vendor_order_number']) && !empty($filters['vendor_order_number'])) {
            $query->where('vendor_order_number', 'LIKE', '%' . $filters['vendor_order_number'] . '%');
        }

        // Search by customer name
        if (isset($filters['customer_name']) && !empty($filters['customer_name'])) {
            $query->whereHas('masterOrder.customer', function ($q) use ($filters) {
                $q->where('name', 'LIKE', '%' . $filters['customer_name'] . '%');
            });
        }
    }
}