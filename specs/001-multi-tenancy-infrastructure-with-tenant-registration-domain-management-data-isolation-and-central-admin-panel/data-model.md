# Data Model: Multi-Tenancy Infrastructure (stancl/tenancy)

**Feature**: Multi-Tenancy Infrastructure  
**Date**: 20 فبراير 2026  
**Branch**: `001-multi-tenancy-infrastructure-with-tenant-registration-domain-management-data-isolation-and-central-admin-panel`

## Overview

This document defines the data entities, relationships, and validation rules for the multi-tenancy infrastructure using **stancl/tenancy** package. The model extends stancl/tenancy's base models with custom fields for subscription management, domain verification, and tenant status tracking.

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                     CENTRAL DATABASE                             │
│  (sater_central - manages tenants, domains, subscriptions)      │
├─────────────────────────────────────────────────────────────────┤
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐  │
│  │    Tenant    │  │    Domain    │  │  SubscriptionPlan    │  │
│  │  (extends    │  │  (extends    │  │                      │  │
│  │   stancl)    │  │   stancl)    │  │                      │  │
│  └──────┬───────┘  └──────┬───────┘  └──────────┬───────────┘  │
│         │                 │                      │              │
│         │                 │                      │              │
│         └────────┬────────┘                      │              │
│                  │                               │              │
│         ┌────────▼────────┐             ┌────────▼────────┐    │
│         │  Subscription   │             │    AdminUser    │    │
│         │                 │             │                 │    │
│         └─────────────────┘             └─────────────────┘    │
└─────────────────────────────────────────────────────────────────┘
                  │
                  │ stancl/tenancy manages
                  ▼
┌─────────────────────────────────────────────────────────────────┐
│                    TENANT DATABASES                              │
│  (tenant_{uuid} - one per tenant, isolated)                     │
├─────────────────────────────────────────────────────────────────┤
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐  │
│  │     User     │  │    Product   │  │       Order          │  │
│  │  (tenant-    │  │  (tenant-    │  │    (tenant-scoped)   │  │
│  │   scoped)    │  │   scoped)    │  │                      │  │
│  └──────────────┘  └──────────────┘  └──────────────────────┘  │
│  [All application models are tenant-scoped automatically]       │
└─────────────────────────────────────────────────────────────────┘
```

---

## Central Database Entities

These entities reside in the central database (`sater_central`) and are managed by stancl/tenancy.

### Tenant (Extended stancl/tenancy Model)

**Purpose**: Represents a merchant's store account. Extends `Stancl\Tenancy\Database\Models\Tenant`.

**Table**: `tenants`

**Attributes**:

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | UUID | PRIMARY KEY | Unique tenant identifier (stancl/tenancy default) |
| store_name | string | max 100, required | Merchant's store name |
| email | email | unique, required | Primary contact email |
| password_hash | string | required | Hashed password |
| language | enum | 'ar', 'en', default 'en' | Preferred language |
| status | enum | pending_email_verification, active, suspended, cancelled, deleted | Account status |
| current_plan_id | UUID | FK → subscription_plans.id, nullable | Current subscription plan |
| suspension_reason | text | nullable | Reason for suspension |
| deletion_scheduled_at | timestamp | nullable | Scheduled deletion date |
| created_at | timestamp | | Registration date (stancl/tenancy) |
| updated_at | timestamp | | Last modification (stancl/tenancy) |

**Model Definition**:

```php
// app/Models/Tenant.php
namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;
    
    protected $fillable = [
        'store_name',
        'email',
        'password_hash',
        'language',
        'status',
        'current_plan_id',
        'suspension_reason',
        'deletion_scheduled_at',
    ];
    
    protected $hidden = [
        'password_hash',
    ];
    
    protected $casts = [
        'password_hash' => 'hashed',
        'deletion_scheduled_at' => 'datetime',
    ];
    
    /**
     * Get the tenant's subscription plan
     */
    public function currentPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }
    
    /**
     * Get the tenant's subscriptions
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    
    /**
     * Check if tenant is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
    
    /**
     * Check if tenant is suspended
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }
}
```

**Relationships**:
- Has Many: Domain (via `HasDomains` trait), Subscription, EmailVerification
- Belongs To: SubscriptionPlan (via current_plan_id)
- Has One: Database (via `HasDatabase` trait)

**Validation Rules**:
- `store_name`: Required, 3-100 characters
- `email`: Required, valid email, unique across all tenants
- `password`: Required, minimum 8 characters, uppercase, lowercase, number
- `language`: Must be 'ar' or 'en'
- `status`: Must be valid enum value

**State Transitions**:
```
pending_email_verification → active (on email verification)
pending_email_verification → deleted (after 7 days unverified)
active → suspended (by admin action)
active → cancelled (by admin or tenant request)
suspended → active (by admin reactivation)
suspended → cancelled (after 30 days suspended)
cancelled → deleted (after 30 days grace period)
cancelled → active (during grace period, plan renewal)
```

**stancl/tenancy Integration**:
- Extends `Stancl\Tenancy\Database\Models\Tenant`
- Uses `HasDatabase` trait for automatic database creation
- Uses `HasDomains` trait for domain relationship
- Tenant creation triggers `TenantCreated` event
- Database creation triggers `DatabaseCreated` event

---

### Domain (Extended stancl/tenancy Model)

**Purpose**: Maps domain names to tenants. Extends `Stancl\Tenancy\Database\Models\Domain`.

**Table**: `domains`

**Attributes**:

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | UUID | PRIMARY KEY | Unique domain identifier (stancl/tenancy) |
| domain | string | unique, required, max 255 | Full domain name (stancl/tenancy) |
| tenant_id | UUID | FK → tenants.id, required | Owner tenant (stancl/tenancy) |
| verified | boolean | default false | Ownership verified |
| verified_at | timestamp | nullable | Verification timestamp |
| verification_token | string | nullable, unique | DNS verification token |
| ssl_status | enum | pending, active, failed, expired | SSL certificate status |
| ssl_expires_at | timestamp | nullable | SSL expiration date |
| is_primary | boolean | default false | Primary domain for tenant |
| created_at | timestamp | | Creation date (stancl/tenancy) |
| updated_at | timestamp | | Last modification (stancl/tenancy) |

**Model Definition**:

```php
// app/Models/Domain.php
namespace App\Models;

use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

class Domain extends BaseDomain
{
    protected $fillable = [
        'domain',
        'tenant_id',
        'verified',
        'verified_at',
        'verification_token',
        'ssl_status',
        'ssl_expires_at',
        'is_primary',
    ];
    
    protected $casts = [
        'verified' => 'boolean',
        'verified_at' => 'datetime',
        'ssl_expires_at' => 'datetime',
        'is_primary' => 'boolean',
    ];
    
    /**
     * Verify domain ownership via DNS TXT record
     */
    public function verify(): bool
    {
        $expectedValue = "sater-verify={$this->verification_token}";
        
        $records = dns_get_record($this->domain, DNS_TXT);
        
        foreach ($records as $record) {
            if (isset($record['txt']) && strpos($record['txt'], 'sater-verify=') === 0) {
                if ($record['txt'] === $expectedValue) {
                    $this->update([
                        'verified' => true,
                        'verified_at' => now(),
                    ]);
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Generate verification token
     */
    public function generateVerificationToken(): string
    {
        $token = bin2hex(random_bytes(16));
        
        $this->update(['verification_token' => $token]);
        
        return $token;
    }
}
```

**Relationships**:
- Belongs To: Tenant (stancl/tenancy base relationship)

**Validation Rules**:
- `domain`: Required, valid domain format, unique
- `verified`: Boolean
- `ssl_status`: Must be valid enum value
- `is_primary`: Boolean (only one primary per tenant)

**Domain Types**:

| Type | Format | Verification |
|------|--------|--------------|
| Subdomain | `{subdomain}.sater.com` | Auto-verified on creation |
| Custom Domain | `www.example.com` | DNS TXT record verification required |

---

### SubscriptionPlan

**Purpose**: Defines subscription tiers with features and pricing.

**Table**: `subscription_plans`

**Attributes**:

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | UUID | PRIMARY KEY | Unique plan identifier |
| name | string | required, max 100 | Display name |
| slug | string | unique, required, max 50 | URL-friendly identifier |
| description | text | nullable | Plan description (translatable) |
| price_monthly | decimal | required, min 0 | Monthly price |
| price_yearly | decimal | required, min 0 | Annual price |
| features | json | required | Feature flags and limits |
| trial_days | integer | default 0, min 0 | Free trial period |
| is_active | boolean | default true | Available for selection |
| sort_order | integer | default 0 | Display order |
| created_at | timestamp | | Creation date |
| updated_at | timestamp | | Last modification |

**Model Definition**:

```php
// app/Models/SubscriptionPlan.php
class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_yearly',
        'features',
        'trial_days',
        'is_active',
        'sort_order',
    ];
    
    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'features' => 'array',
        'trial_days' => 'integer',
        'is_active' => 'boolean',
    ];
    
    public function tenants()
    {
        return $this->hasMany(Tenant::class, 'current_plan_id');
    }
    
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    
    public function getFeature(string $key, $default = null)
    {
        return $this->features[$key] ?? $default;
    }
    
    public function hasFeature(string $key): bool
    {
        return isset($this->features[$key]);
    }
}
```

**Features JSON Structure**:

```json
{
  "products_limit": 100,
  "storage_gb": 5,
  "users_limit": 3,
  "custom_domain": false,
  "analytics": "basic",
  "support_level": "email",
  "api_rate_limit": 1000
}
```

**Feature Limits Reference**:

| Feature | Type | Description |
|---------|------|-------------|
| `products_limit` | integer | Max products (-1 for unlimited) |
| `storage_gb` | integer | Max storage in GB |
| `users_limit` | integer | Max tenant users (-1 for unlimited) |
| `custom_domain` | boolean | Allow custom domains |
| `analytics` | string | analytics level (basic/advanced) |
| `support_level` | string | Support type (email/priority/dedicated) |
| `api_rate_limit` | integer | API requests per minute |

---

### Subscription

**Purpose**: Tracks tenant's subscription history and current plan.

**Table**: `subscriptions`

**Attributes**:

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | UUID | PRIMARY KEY | Unique subscription identifier |
| tenant_id | UUID | FK → tenants.id, required | Subscriber tenant |
| plan_id | UUID | FK → subscription_plans.id, required | Subscribed plan |
| status | enum | required | active, cancelled, expired, past_due |
| billing_cycle | enum | required | monthly, yearly |
| starts_at | timestamp | required | Subscription start date |
| ends_at | timestamp | nullable | Subscription end date |
| cancelled_at | timestamp | nullable | Cancellation timestamp |
| trial_ends_at | timestamp | nullable | Trial period end |
| currency | string | 3-char ISO, required | Billing currency |
| amount | decimal | required | Amount charged |
| metadata | json | nullable | Payment processor metadata |
| created_at | timestamp | | Creation date |
| updated_at | timestamp | | Last modification |

**Model Definition**:

```php
// app/Models/Subscription.php
class Subscription extends Model
{
    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'billing_cycle',
        'starts_at',
        'ends_at',
        'cancelled_at',
        'trial_ends_at',
        'currency',
        'amount',
        'metadata',
    ];
    
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'amount' => 'decimal:2',
        'metadata' => 'array',
    ];
    
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    
    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }
    
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }
    
    public function isOnTrial(): bool
    {
        return $this->trial_ends_at !== null 
            && $this->trial_ends_at->isFuture();
    }
}
```

**Relationships**:
- Belongs To: Tenant, SubscriptionPlan

---

### AdminUser

**Purpose**: Platform administrators with access to central admin panel.

**Table**: `admin_users`

**Attributes**:

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | UUID | PRIMARY KEY | Unique admin identifier |
| name | string | required, max 100 | Full name |
| email | email | unique, required | Admin email |
| password_hash | string | required | Hashed password |
| is_super_admin | boolean | default false | Full platform access |
| is_active | boolean | default true | Can access admin panel |
| last_login_at | timestamp | nullable | Last login timestamp |
| created_at | timestamp | | Creation date |
| updated_at | timestamp | | Last modification |

**Model Definition**:

```php
// app/Models/AdminUser.php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class AdminUser extends Authenticatable
{
    use HasRoles;
    
    protected $fillable = [
        'name',
        'email',
        'password_hash',
        'is_super_admin',
        'is_active',
        'last_login_at',
    ];
    
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];
    
    protected $casts = [
        'password_hash' => 'hashed',
        'is_super_admin' => 'boolean',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];
    
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}
```

**Authorization**:
- Uses Spatie Laravel Permission package
- Roles: Super Admin, Support Admin, Billing Admin
- Permissions granular per resource

---

## Tenant Database Entities

These entities exist in each tenant's isolated database. stancl/tenancy automatically switches to the correct database based on the request domain.

### User (Tenant-Scoped)

**Purpose**: Users within a tenant's store (staff, managers).

**Table**: `users`

**Attributes**:

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | UUID | PRIMARY KEY | Unique user identifier |
| email | email | unique within tenant, required | User email |
| password_hash | string | required | Hashed password |
| name | string | required, max 100 | Full name |
| language | enum | 'ar', 'en', default 'en' | Preferred language |
| is_active | boolean | default true | Can access store |
| created_at | timestamp | | Creation date |
| updated_at | timestamp | | Last modification |

**Model Definition**:

```php
// app/Models/User.php (tenant-scoped)
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class User extends Authenticatable
{
    use BelongsToTenant;
    
    protected $fillable = [
        'email',
        'password_hash',
        'name',
        'language',
        'is_active',
    ];
    
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];
    
    protected $casts = [
        'password_hash' => 'hashed',
        'is_active' => 'boolean',
    ];
    
    /**
     * BelongsToTenant trait automatically scopes queries
     * to the current tenant via tenant_id
     */
}
```

**Note**: Email uniqueness is per-tenant, not global. Same email can exist in different tenants.

---

## Base Model Pattern (Tenant-Scoped)

All tenant-scoped models extend a base model with the `BelongsToTenant` trait from stancl/tenancy:

```php
// app/Models/TenantModel.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

abstract class TenantModel extends Model
{
    use BelongsToTenant;
    
    /**
     * The "booted" method of the model.
     * 
     * BelongsToTenant trait automatically:
     * 1. Adds global scope to filter by tenant_id
     * 2. Sets tenant_id on creating
     * 3. Prevents saving without tenant context
     */
}

// Example usage
class Product extends TenantModel
{
    protected $fillable = ['name', 'description', 'price'];
}

// All queries automatically scoped:
Product::all(); 
// → SELECT * FROM products WHERE tenant_id = current_tenant_id
```

---

## Migrations Structure

### Central Database Migrations

```
database/migrations/
├── 2026_02_20_000001_create_tenants_table.php (stancl/tenancy)
├── 2026_02_20_000002_create_domains_table.php (stancl/tenancy)
├── 2026_02_20_000003_create_subscription_plans_table.php
├── 2026_02_20_000004_create_subscriptions_table.php
├── 2026_02_20_000005_create_admin_users_table.php
├── 2026_02_20_000006_create_permission_tables.php (Spatie)
└── 2026_02_20_000007_create_email_verifications_table.php
```

### Tenant Database Migrations

```
database/migrations/tenant/
├── 2026_02_20_000001_create_users_table.php
├── 2026_02_20_000002_create_permission_tables.php (Spatie)
├── 2026_02_20_000003_create_media_table.php (Spatie Media Library)
├── 2026_02_20_000004_create_products_table.php
├── 2026_02_20_000005_create_orders_table.php
└── [application-specific migrations...]
```

**Running Migrations**:

```bash
# Central database migrations
php artisan migrate

# Tenant database migrations (all tenants)
php artisan tenants:migrate

# Tenant database migrations (specific tenant)
php artisan tenants:migrate --tenants=uuid-here
```

---

## Indexes & Performance

### Central Database Indexes

```sql
-- Tenant lookups
CREATE INDEX idx_tenants_email ON tenants(email);
CREATE INDEX idx_tenants_status ON tenants(status);
CREATE INDEX idx_tenants_plan ON tenants(current_plan_id);

-- Domain lookups (critical for tenant resolution)
CREATE UNIQUE INDEX idx_domains_domain ON domains(domain);
CREATE INDEX idx_domains_tenant ON domains(tenant_id);
CREATE INDEX idx_domains_verified ON domains(verified);

-- Subscription lookups
CREATE INDEX idx_subscriptions_tenant ON subscriptions(tenant_id);
CREATE INDEX idx_subscriptions_plan ON subscriptions(plan_id);
CREATE INDEX idx_subscriptions_status ON subscriptions(status, ends_at);

-- Composite indexes
CREATE INDEX idx_tenants_status_plan ON tenants(status, current_plan_id);
```

### Tenant Database Indexes

```sql
-- User lookups
CREATE UNIQUE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_active ON users(is_active);

-- Common entity indexes
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_orders_customer ON orders(customer_id);
CREATE INDEX idx_orders_status ON orders(status);
```

---

## Validation Rules Summary

### Subdomain Validation

```php
use Illuminate\Validation\Rule;

[
    'subdomain' => [
        'required',
        'string',
        'min:3',
        'max:50',
        'regex:/^[a-z0-9]+([\-][a-z0-9]+)*$/',
        Rule::unique('domains', 'domain')->where(function ($query) {
            return $query->where('domain', 'like', '%.sater.com');
        }),
        Rule::notIn([
            'www', 'mail', 'admin', 'api', 'app', 'blog', 'shop', 
            'store', 'support', 'help', 'docs', 'dev', 'staging', 
            'prod', 'test', 'demo', 'm', 'mobile', 'static', 'cdn', 'assets'
        ]),
    ],
]
```

### Domain Validation

```php
[
    'domain' => [
        'required',
        'string',
        'max:255',
        'regex:/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/',
        Rule::unique('domains', 'domain'),
    ],
]
```

### Email Validation

```php
[
    'email' => [
        'required',
        'email',
        'max:255',
        Rule::unique('tenants', 'email'),
    ],
]
```

### Password Validation

```php
[
    'password' => [
        'required',
        'min:8',
        'regex:/[A-Z]/',      // Uppercase
        'regex:/[a-z]/',      // Lowercase
        'regex:/[0-9]/',      // Number
        'regex:/[^a-zA-Z0-9]/', // Special character
    ],
]
```

---

## Data Retention & Deletion

### Retention Policies

| Data Type | Retention Period | Trigger |
|-----------|------------------|---------|
| Tenant (deleted) | 30 days | Cancellation or manual deletion |
| Tenant (unverified) | 7 days | Registration without email verification |
| Subscription history | 7 years | Financial compliance |
| Domain records | 30 days post-deletion | After tenant deleted |
| Backups | 30 days daily, 12 months monthly | Automated rotation |

### Deletion Process with stancl/tenancy

```php
// TenantDeletionService.php
use Stancl\Tenancy\Events\TenantDeleted;

class TenantDeletionService
{
    public function executeDeletion(Tenant $tenant): void
    {
        // stancl/tenancy automatically deletes database via event listener
        $tenant->delete(); // Triggers TenantDeleted event
        
        // Event listener handles:
        // 1. Database deletion
        // 2. File deletion
        // 3. Cache cleanup
        
        // Additional cleanup
        $tenant->domains()->delete();
        
        // Anonymize central records
        $tenant->update([
            'store_name' => 'Deleted Store',
            'email' => null,
            'subdomain' => null,
            'password_hash' => null,
        ]);
        
        // Log for compliance
        ActivityLog::create([
            'action' => 'tenant_deleted',
            'tenant_id' => $tenant->id,
            'deleted_at' => now(),
        ]);
    }
}
```

**stancl/tenancy Event Listener**:

```php
// TenancyServiceProvider.php
use Stancl\Tenancy\Events\TenantDeleted;

Events::listen(TenantDeleted::class, function (TenantDeleted $event) {
    // Delete tenant database
    $event->tenant->database()->delete();
    
    // Delete tenant files
    File::deleteDirectory(storage_path("app/tenants/{$event->tenant->id}"));
    
    Log::info("Tenant database and files deleted", [
        'tenant_id' => $event->tenant->id,
    ]);
});
```

---

## References

- Specification: [spec.md](./spec.md)
- Research: [research.md](./research.md) - stancl/tenancy implementation details
- Contracts: [contracts/](./contracts/)
- Plan: [plan.md](./plan.md)
- stancl/tenancy Documentation: https://tenancyforlaravel.com
