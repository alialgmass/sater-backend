<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

/**
 * Tenant Statistics Service
 * 
 * Calculates statistics and metrics for tenants.
 */
class TenantStatisticsService
{
    /**
     * Get statistics for a single tenant.
     * 
     * @param Tenant $tenant
     * @return array<string, mixed>
     */
    public function getTenantStatistics(Tenant $tenant): array
    {
        // Note: These queries would need to run in tenant context
        // This is a placeholder for the actual implementation
        
        return [
            'tenant_id' => $tenant->id,
            'store_name' => $tenant->store_name,
            'products_count' => 0, // Would query tenant database
            'orders_count' => 0,   // Would query tenant database
            'revenue_total' => 0,  // Would query tenant database
            'storage_used_gb' => $this->calculateStorageUsed($tenant),
            'customers_count' => 0, // Would query tenant database
        ];
    }

    /**
     * Get platform-wide statistics.
     * 
     * @return array<string, mixed>
     */
    public function getPlatformStatistics(): array
    {
        return [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'suspended_tenants' => Tenant::where('status', 'suspended')->count(),
            'new_tenants_this_month' => Tenant::whereMonth('created_at', now()->month)->count(),
            'total_revenue' => 0, // Would aggregate from all tenants
            'total_products' => 0, // Would aggregate from all tenants
            'total_orders' => 0,   // Would aggregate from all tenants
        ];
    }

    /**
     * Calculate storage used by a tenant.
     * 
     * @param Tenant $tenant
     * @return float Storage in GB
     */
    protected function calculateStorageUsed(Tenant $tenant): float
    {
        $tenantPath = storage_path("app/tenants/{$tenant->id}");
        
        if (!file_exists($tenantPath)) {
            return 0.0;
        }
        
        $size = $this->getDirectorySize($tenantPath);
        
        return round($size / 1024 / 1024 / 1024, 2); // Convert to GB
    }

    /**
     * Get directory size recursively.
     * 
     * @param string $path
     * @return int Size in bytes
     */
    protected function getDirectorySize(string $path): int
    {
        $size = 0;
        
        if (!file_exists($path)) {
            return 0;
        }
        
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        
        return $size;
    }

    /**
     * Get tenant growth trend (last 6 months).
     * 
     * @return array<int, array<string, mixed>>
     */
    public function getTenantGrowthTrend(): array
    {
        $trend = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            
            $trend[] = [
                'month' => $month->format('Y-m'),
                'label' => $month->format('M Y'),
                'new_tenants' => Tenant::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
                'active_tenants' => Tenant::where('status', 'active')
                    ->whereYear('created_at', '<=', $month->year)
                    ->whereMonth('created_at', '<=', $month->month)
                    ->count(),
            ];
        }
        
        return $trend;
    }

    /**
     * Get top tenants by creation date.
     * 
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getTopTenants(int $limit = 10)
    {
        return Tenant::with('currentPlan')
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($tenant) {
                return [
                    'id' => $tenant->id,
                    'store_name' => $tenant->store_name,
                    'subdomain' => $tenant->domains->first()?->domain,
                    'plan' => $tenant->currentPlan?->name,
                    'created_at' => $tenant->created_at,
                ];
            });
    }

    /**
     * Get subscription plan distribution.
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getPlanDistribution()
    {
        return Tenant::select('current_plan_id')
            ->selectRaw('COUNT(*) as tenant_count')
            ->groupBy('current_plan_id')
            ->with('currentPlan')
            ->get()
            ->map(function ($item) {
                return [
                    'plan_id' => $item->current_plan_id,
                    'plan_name' => $item->currentPlan?->name ?? 'No Plan',
                    'tenant_count' => $item->tenant_count,
                ];
            });
    }
}
