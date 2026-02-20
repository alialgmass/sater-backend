<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * Tenant Model (Base Class)
 * 
 * Base model for all tenant-scoped models.
 * Automatically scopes all queries to the current tenant
 * and sets the tenant_id on creation.
 * 
 * Usage:
 *   class Product extends TenantModel { }
 * 
 * All queries will automatically be scoped:
 *   Product::all() // WHERE tenant_id = current_tenant_id
 */
abstract class TenantModel extends Model
{
    use BelongsToTenant;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model and apply tenant scoping.
     * 
     * The BelongsToTenant trait automatically:
     * 1. Adds global scope to filter by tenant_id
     * 2. Sets tenant_id on creating
     * 3. Prevents saving without tenant context
     */
    protected static function boot()
    {
        parent::boot();
        
        // Additional boot logic can be added here if needed
        // The BelongsToTenant trait handles the core functionality
    }

    /**
     * Get the tenant that owns the model.
     * 
     * Note: This relationship is virtual - tenant data is in central DB
     * while this model is in tenant DB.
     */
    public function tenant()
    {
        // Return a mock tenant or null since we can't directly relate
        // to central database tenant from tenant database
        return null;
    }
}
