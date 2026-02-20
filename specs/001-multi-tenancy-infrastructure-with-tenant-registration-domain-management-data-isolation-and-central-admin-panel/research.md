# Research: Multi-Tenancy Infrastructure with stancl/tenancy

**Feature**: Multi-Tenancy Infrastructure  
**Date**: 20 فبراير 2026  
**Branch**: `001-multi-tenancy-infrastructure-with-tenant-registration-domain-management-data-isolation-and-central-admin-panel`

## Overview

This document consolidates research findings for implementing multi-tenancy in a Laravel 12 application using the `stancl/tenancy` package. All technical decisions leverage stancl/tenancy's built-in functionality for maximum reliability and maintainability.

---

## Research Topics

### 1. Why stancl/tenancy Over Custom Implementation

**Decision**: Use stancl/tenancy v3 as the primary multi-tenancy package

**Rationale**:
- **Most Popular**: 2,500+ GitHub stars, 1M+ downloads, industry standard for Laravel
- **Battle-Tested**: Used in production by thousands of SaaS applications since 2019
- **Complete Solution**: Handles database, cache, filesystem, queue, and config tenancy
- **Automatic Tenant Identification**: Domain-based routing out of the box
- **Database Isolation**: Automatic database creation, migration, and deletion per tenant
- **Event System**: Lifecycle hooks for tenant creation, deletion, and initialization
- **Filament Compatible**: Works seamlessly with Filament admin panel
- **Active Maintenance**: Regular updates, Laravel 12 compatible, active Discord community
- **Documentation**: Comprehensive docs at https://tenancyforlaravel.com

**Alternatives Considered**:

| Package | Pros | Cons | Why Rejected |
|---------|------|------|--------------|
| **Custom Implementation** | Full control, no dependencies | Reinventing wheel, maintenance burden, security risks | stancl/tenancy already solves all requirements |
| **spatie/laravel-multitenancy** | Simple, lightweight | Only supports single-database tenancy (scope-based) | Doesn't meet FR-007 "complete isolation" requirement |
| **hyn/multi-tenant** | Early solution | Abandoned, not maintained, security concerns | Package is deprecated and unmaintained |
| **nwidart/laravel-modules** | Module structure | Not a tenancy solution, just code organization | Would still need tenancy package on top |

**Implementation Pattern with stancl/tenancy**:

```php
// Tenant Model (extends stancl's base)
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    use HasDomains;
    
    protected $fillable = [
        'store_name',
        'email',
        'password_hash',
        'language',
        'status',
        'current_plan_id',
    ];
    
    // Custom tenant initialization
    public static function boot(): void
    {
        parent::boot();
        
        static::created(function (Tenant $tenant) {
            // Create database automatically
            $tenant->database()->create();
            
            // Run migrations
            Artisan::call('tenants:migrate', [
                '--tenants' => [$tenant->id]
            ]);
            
            // Seed initial data
            Artisan::call('tenants:seed', [
                '--tenants' => [$tenant->id]
            ]);
        });
    }
}

// Domain Model
use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

class Domain extends BaseDomain
{
    // Add custom domain verification logic
}
```

**Key Configuration** (`config/tenancy.php`):

```php
return [
    'tenant_model' => \App\Models\Tenant::class,
    'domain_model' => \App\Models\Domain::class,
    
    'identification' => [
        'domain' => [
            'middleware' => [
                \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
                \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            ],
        ],
    ],
    
    'database' => [
        'template_database' => env('TENANCY_TEMPLATE_DATABASE'),
        'managers' => [
            'mysql' => [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'template' => 'mysql_template',
            ],
        ],
    ],
    
    'cache' => [
        'tag_base' => 'tenant',
    ],
    
    'filesystem' => [
        'storage' => [
            'driver' => 'local',
            'root' => storage_path('app/tenants'),
        ],
    ],
];
```

**References**:
- Documentation: https://tenancyforlaravel.com
- GitHub: https://github.com/stancl/tenancy
- Laravel News: https://laravel-news.com/stancl-tenancy

---

### 2. Tenant Identification & Domain Routing

**Decision**: Use stancl/tenancy's built-in domain-based tenant identification

**Rationale**:
- Automatic tenant resolution from request hostname
- Supports wildcard subdomains out of the box
- Custom domain mapping via Domain model
- Middleware-based initialization ensures consistent tenancy context
- Prevents access from central domains to tenant routes

**Implementation with stancl/tenancy**:

```php
// Routes are automatically tenant-scoped
// routes/tenant.php

Route::middleware([
    'web',
    InitializeTenancyByDomain::class, // stancl middleware
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::resource('products', ProductController::class);
    Route::resource('orders', OrderController::class);
});

// Central routes (registration, admin) are separate
// routes/web.php

Route::prefix('api/v1')->group(function () {
    Route::post('/tenants/register', [TenantRegistrationController::class, 'register']);
    Route::get('/tenants/verify/{token}', [TenantRegistrationController::class, 'verify']);
});

Route::prefix('admin')->middleware(['auth:admin'])->group(function () {
    Route::resource('tenants', AdminTenantController::class);
    Route::resource('subscription-plans', SubscriptionPlanController::class);
});
```

**Domain Configuration**:

```php
// Create tenant with domain
$tenant = Tenant::create([
    'store_name' => 'Fashion House',
    'email' => 'owner@fashionhouse.com',
    'password_hash' => Hash::make('SecurePass123!'),
    'language' => 'en',
]);

$domain = Domain::create([
    'domain' => 'fashionhouse.sater.com',
    'tenant_id' => $tenant->id,
]);

// Custom domain
$customDomain = Domain::create([
    'domain' => 'www.fashionhouse.com',
    'tenant_id' => $tenant->id,
    'verified' => false,
]);
```

**Wildcard DNS Setup**:

```
# DNS Configuration
*.sater.com    CNAME    sater.com
sater.com      A        [server IP]

# Local development (hosts file)
127.0.0.1    *.localhost
127.0.0.1    *.test
```

**Middleware Stack** (`app/Http/Kernel.php`):

```php
protected $middlewareGroups = [
    'tenant' => [
        \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
        \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
        \App\Http\Middleware\EnsureTenantIsActive::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    ],
    
    'web' => [
        // Central routes middleware
    ],
];
```

---

### 3. Database Isolation with stancl/tenancy

**Decision**: Use stancl/tenancy's automatic database management

**Rationale**:
- Automatic database creation on tenant creation
- Automatic database deletion on tenant deletion
- Built-in migration commands for tenant databases
- Connection pooling managed by Laravel
- Template database support for quick provisioning

**Database Configuration** (`config/database.php`):

```php
'connections' => [
    'central' => [
        // Central database for tenants, domains, admin users
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'database' => env('DB_DATABASE_CENTRAL', 'sater_central'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        // ...
    ],
    
    'tenant' => [
        // Template for tenant databases
        // stancl/tenancy will create individual databases
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'database' => null, // Filled automatically per tenant
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => null,
    ],
],
```

**Tenant Database Creation** (handled by stancl/tenancy):

```php
// In Tenant model boot method
static::created(function (Tenant $tenant) {
    // Create database with naming convention: tenant_{id}
    $tenant->database()->create();
    
    // Run migrations on tenant database
    Artisan::call('tenants:migrate', [
        '--tenants' => [$tenant->id]
    ]);
});
```

**Migration Commands**:

```bash
# Run migrations on all tenant databases
php artisan tenants:migrate

# Run migrations on specific tenant
php artisan tenants:migrate --tenants=uuid-here

# Rollback tenant migrations
php artisan tenants:rollback

# Seed tenant databases
php artisan tenants:seed --tenants=uuid-here
```

**Database Naming Convention**:

```
Central Database: sater_central
Tenant Databases: tenant_{tenant_id}
  - tenant_550e8400-e29b-41d4-a716-446655440000
  - tenant_6ba7b810-9dad-11d1-80b4-00c04fd430c8
```

**Security Measures**:
- Database name derived from tenant UUID (not user input)
- Tenant context validated by middleware before database switch
- Connection isolated per request (no cross-request leakage)
- Central database never accessible from tenant context

---

### 4. Domain Management & Verification

**Decision**: Extend stancl/tenancy Domain model with custom verification

**Rationale**:
- stancl/tenancy provides base Domain model
- Custom fields added for verification status and SSL
- DNS TXT record verification (industry standard)
- Automatic SSL via Laravel Ploi or server automation

**Extended Domain Model**:

```php
// app/Models/Domain.php
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
        $expectedToken = "_sater-verification.{$this->domain}";
        $expectedValue = "sater-verify={$this->verification_token}";
        
        // Query DNS TXT records
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
        
        $this->update([
            'verification_token' => $token,
        ]);
        
        return $token;
    }
}
```

**Domain Verification Flow**:

```php
// DomainManagementController.php
public function addDomain(Request $request, Tenant $tenant)
{
    $validated = $request->validate([
        'domain' => 'required|domain|unique:domains,domain',
        'is_primary' => 'boolean',
    ]);
    
    $domain = $tenant->domains()->create([
        'domain' => $validated['domain'],
        'verified' => false,
        'verification_token' => bin2hex(random_bytes(16)),
        'ssl_status' => 'pending',
    ]);
    
    return response()->json([
        'message' => 'Domain added. Please verify ownership.',
        'domain' => $domain,
        'verification_instructions' => [
            'type' => 'TXT',
            'name' => "_sater-verification.{$domain->domain}",
            'value' => "sater-verify={$domain->verification_token}",
        ],
    ]);
}

public function verifyDomain(Domain $domain)
{
    if ($domain->verify()) {
        // Provision SSL certificate
        $this->sslProvisioner->provision($domain);
        
        return response()->json([
            'message' => 'Domain verified. SSL certificate being provisioned.',
        ]);
    }
    
    return response()->json([
        'message' => 'Verification failed. DNS record not found.',
    ], 400);
}
```

**SSL Provisioning**:

For production, integrate with:
- **Laravel Ploi**: Automatic SSL via Let's Encrypt
- **Forge**: Built-in SSL management
- **Custom**: Use acme-php for Let's Encrypt automation

```bash
# Example with Laravel Ploi API
curl -X POST https://ploi.io/api/domains/{domain_id}/certificate \
  -H "Authorization: Bearer {token}" \
  -d '{"source": "letsencrypt"}'
```

---

### 5. Tenant Registration Flow with stancl/tenancy

**Decision**: Multi-step registration using stancl/tenancy events

**Rationale**:
- Leverage stancl/tenancy's tenant creation events
- Automatic database creation on registration
- Email verification before subscription
- Clean separation between central and tenant contexts

**Registration Controller**:

```php
// TenantRegistrationController.php
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Events\DatabaseCreated;

class TenantRegistrationController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'required|string|max:100',
            'email' => 'required|email|unique:tenants,email',
            'password' => 'required|min:8|mixed_case_numbers',
            'subdomain' => 'required|regex:/^[a-z0-9]+([\-][a-z0-9]+)*$/|min:3|max:50|unique_domains',
            'language' => 'required|in:ar,en',
        ]);
        
        // Check reserved subdomains
        if ($this->isReservedSubdomain($validated['subdomain'])) {
            return response()->json([
                'message' => "The subdomain '{$validated['subdomain']}' is reserved.",
                'code' => 'RESERVED_SUBDOMAIN',
            ], 422);
        }
        
        DB::beginTransaction();
        
        try {
            // Create tenant (triggers database creation)
            $tenant = Tenant::create([
                'store_name' => $validated['store_name'],
                'email' => $validated['email'],
                'password_hash' => Hash::make($validated['password']),
                'language' => $validated['language'],
                'status' => 'pending_email_verification',
            ]);
            
            // Create domain
            $domain = Domain::create([
                'domain' => "{$validated['subdomain']}.sater.com",
                'tenant_id' => $tenant->id,
                'verified' => true, // Auto-verify subdomains
                'is_primary' => true,
            ]);
            
            // Generate email verification token
            $verificationToken = $this->createEmailVerificationToken($tenant);
            
            // Send confirmation email
            Mail::to($tenant->email)->send(
                new TenantRegistrationEmail($tenant, $verificationToken)
            );
            
            DB::commit();
            
            return response()->json([
                'message' => 'Registration successful. Please check your email.',
                'tenant' => [
                    'id' => $tenant->id,
                    'store_name' => $tenant->store_name,
                    'subdomain' => $domain->domain,
                    'language' => $tenant->language,
                ],
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up tenant if created
            if (isset($tenant)) {
                $tenant->delete(); // Triggers database deletion
            }
            
            return response()->json([
                'message' => 'Registration failed. Please try again.',
            ], 500);
        }
    }
    
    public function verify(string $token)
    {
        $verification = EmailVerification::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();
        
        if (!$verification) {
            return response()->json([
                'message' => 'Invalid or expired verification token.',
                'code' => 'INVALID_TOKEN',
            ], 404);
        }
        
        $tenant = $verification->tenant;
        $tenant->update([
            'status' => 'active',
        ]);
        
        // Create default admin user for tenant
        User::create([
            'email' => $tenant->email,
            'password_hash' => $tenant->password_hash,
            'name' => $tenant->store_name,
            'language' => $tenant->language,
        ]);
        
        // Delete verification record
        $verification->delete();
        
        return response()->json([
            'message' => 'Email verified. Please select a subscription plan.',
            'tenant' => [
                'id' => $tenant->id,
                'store_name' => $tenant->store_name,
                'subdomain' => $tenant->domains->first()->domain,
            ],
            'redirect_url' => "https://{$tenant->domains->first()->domain}/onboarding/plan-selection",
        ]);
    }
}
```

**Event Listener** (`TenancyServiceProvider.php`):

```php
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Events\DatabaseCreated;

class TenancyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Events::listen(TenantCreated::class, function (TenantCreated $event) {
            // Run migrations automatically
            $tenant = $event->tenant;
            
            Artisan::call('tenants:migrate', [
                '--tenants' => [$tenant->id]
            ]);
        });
        
        Events::listen(DatabaseCreated::class, function (DatabaseCreated $event) {
            // Log database creation, send notifications, etc.
            Log::info("Tenant database created: {$event->tenant->id}");
        });
    }
}
```

---

### 6. Central Admin Panel with Filament

**Decision**: Use Filament 4 with stancl/tenancy integration

**Rationale**:
- Already installed in project
- stancl/tenancy has official Filament integration
- Rapid admin UI development
- Built-in authentication and authorization

**Filament Tenant Resource**:

```php
// app/Filament/Resources/TenantResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?int $navigationSort = 1;
    
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Store Information')
                    ->schema([
                        Forms\Components\TextInput::make('store_name')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('language')
                            ->options([
                                'en' => 'English',
                                'ar' => 'Arabic',
                            ])
                            ->default('en'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending_email_verification' => 'Pending Email Verification',
                                'active' => 'Active',
                                'suspended' => 'Suspended',
                                'cancelled' => 'Cancelled',
                                'deleted' => 'Deleted',
                            ])
                            ->required()
                            ->default('active'),
                        Forms\Components\Select::make('current_plan_id')
                            ->relationship('currentPlan', 'name')
                            ->label('Subscription Plan'),
                    ])
                    ->columns(2),
            ]);
    }
    
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('store_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('domains.first.domain')
                    ->label('Domain')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending_email_verification',
                        'success' => 'active',
                        'danger' => 'suspended',
                        'gray' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('currentPlan.name')
                    ->label('Plan'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('suspend')
                    ->requiresConfirmation()
                    ->action(fn (Tenant $record) => $record->update(['status' => 'suspended']))
                    ->visible(fn (Tenant $record) => $record->status === 'active'),
                Tables\Actions\Action::make('activate')
                    ->action(fn (Tenant $record) => $record->update(['status' => 'active']))
                    ->visible(fn (Tenant $record) => $record->status === 'suspended'),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('suspend')
                    ->action(fn ($records) => $records->each->update(['status' => 'suspended'])),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            'domains',
            'currentPlan',
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'view' => Pages\ViewTenant::route('/{record}'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
```

**Filament Configuration** (`config/filament.php`):

```php
return [
    'path' => '/admin',
    'auth' => [
        'guard' => 'admin',
        'model' => \App\Models\AdminUser::class,
    ],
    // ...
];
```

---

### 7. File Storage Isolation

**Decision**: Use stancl/tenancy filesystem tenancy with Spatie Media Library

**Rationale**:
- stancl/tenancy provides automatic filesystem switching
- Spatie Media Library already installed in project
- Tenant-scoped storage directories
- Automatic URL generation

**Filesystem Configuration** (`config/tenancy.php`):

```php
'filesystem' => [
    'storage' => [
        'driver' => 'local',
        'root' => storage_path('app/tenants'),
        'url' => '/storage/tenants',
    ],
],
```

**Tenant Storage Structure**:

```
storage/app/tenants/
├── {tenant_id}/
│   ├── products/
│   ├── banners/
│   └── media/
│       └── {model}/{id}/
│           └── conversions/
└── {tenant_id}/
```

**Media Library Integration**:

```php
// In tenant-scoped model
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends TenantModel implements HasMedia
{
    use InteractsWithMedia;
    
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('tenant'); // stancl/tenancy tenant disk
    }
}

// Usage
$product = Product::create([...]);
$product->addMedia($request->file('image'))
    ->toMediaCollection('images', 'tenant');
```

**Tenant Disk Configuration** (`config/filesystems.php`):

```php
'disks' => [
    'tenant' => [
        'driver' => 'local',
        'root' => storage_path('app/tenants/' . tenant('id')),
        'url' => '/storage/tenants/' . tenant('id'),
        'visibility' => 'public',
    ],
],
```

---

### 8. Backup Strategy with stancl/tenancy

**Decision**: Use spatie/laravel-backup with tenant-aware backup commands

**Rationale**:
- spatie/laravel-backup is industry standard
- Can backup each tenant database separately
- Supports multiple backup destinations
- Built-in cleanup and retention policies

**Backup Configuration** (`config/backup.php`):

```php
return [
    'backup' => [
        'source' => [
            'databases' => [
                'central',
                // Tenant databases backed up separately
            ],
            'files' => [
                base_path(),
                storage_path('app/tenants'),
            ],
        ],
        'destination' => [
            'disks' => ['s3'],
            'prefix' => 'backups/central',
        ],
    ],
    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,
        'default_backup' => [
            'keep_all_backups_for_days' => 7,
            'keep_daily_backups_for_days' => 30,
            'keep_weekly_backups_for_weeks' => 12,
            'keep_monthly_backups_for_months' => 12,
        ],
    ],
];
```

**Tenant Backup Command**:

```php
// app/Console/Commands/BackupTenants.php
class BackupTenants extends Command
{
    protected $signature = 'backup:tenants';
    protected $description = 'Backup all tenant databases';
    
    public function handle()
    {
        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);
            
            // Backup tenant database
            Artisan::call('backup:run', [
                '--only-db' => true,
                '--db-name' => $tenant->database()->getName(),
            ]);
            
            // Backup tenant files
            $this->backupTenantFiles($tenant);
            
            tenancy()->end();
        }
        
        $this->info('All tenants backed up successfully.');
    }
    
    protected function backupTenantFiles(Tenant $tenant): void
    {
        // Copy tenant files to backup location
        $source = storage_path("app/tenants/{$tenant->id}");
        $destination = storage_path("app/backups/tenants/{$tenant->id}");
        
        File::copyDirectory($source, $destination);
    }
}
```

**Scheduled Backups** (`app/Console/Kernel.php`):

```php
protected function schedule(Schedule $schedule): void
{
    // Central database backup daily at 2 AM
    $schedule->command('backup:run --only-db')
        ->dailyAt('02:00')
        ->onOneServer();
    
    // Tenant backups daily at 3 AM
    $schedule->command('backup:tenants')
        ->dailyAt('03:00')
        ->onOneServer();
}
```

---

### 9. Tenant Suspension & Cancellation

**Decision**: Status-based access control with stancl/tenancy middleware

**Rationale**:
- Extend stancl/tenancy middleware for status checks
- Preserve data for reactivation or export
- Different access levels per status
- Compliant with data retention requirements

**Custom Middleware**:

```php
// app/Http/Middleware/EnsureTenantIsActive.php
class EnsureTenantIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $tenant = $request->tenant;
        
        if (!$tenant) {
            return redirect()->route('home');
        }
        
        if ($tenant->status === 'suspended') {
            if ($request->routeIs('tenant.*')) {
                return response()->view('errors.suspended', [
                    'tenant' => $tenant,
                    'reason' => $tenant->suspension_reason,
                ], 403);
            }
        }
        
        if ($tenant->status === 'cancelled') {
            // Allow only data export routes
            if (!$request->routeIs('tenant.data-export.*')) {
                return redirect()->route('tenant.data-export.index');
            }
        }
        
        if ($tenant->status === 'deleted') {
            abort(410, 'This store has been deleted.');
        }
        
        return $next($request);
    }
}
```

**Tenant Status State Machine**:

```
pending_email_verification
    ↓ (email verified)
active
    ↓ (admin action)
suspended
    ↓ (admin reactivation)
active
    ↓ (30 days suspended)
cancelled
    ↓ (30 days grace period)
deleted
    ↓ (immediate)
data anonymized, database deleted
```

**Cancellation Flow**:

```php
// TenantCancellationService.php
class TenantCancellationService
{
    public function scheduleCancellation(Tenant $tenant, int $gracePeriodDays = 30): void
    {
        $tenant->update([
            'status' => 'cancelled',
            'deletion_scheduled_at' => now()->addDays($gracePeriodDays),
        ]);
        
        // Schedule deletion job
        DeleteTenantData::dispatch($tenant)
            ->delay(now()->addDays($gracePeriodDays));
        
        // Notify tenant
        Mail::to($tenant->email)->send(new TenantCancelledEmail($tenant));
    }
    
    public function executeDeletion(Tenant $tenant): void
    {
        // stancl/tenancy handles database deletion
        $tenant->delete(); // Triggers database deletion event
        
        // Delete files
        File::deleteDirectory(storage_path("app/tenants/{$tenant->id}"));
        
        // Anonymize central records
        $tenant->domains()->delete();
        
        $tenant->update([
            'store_name' => 'Deleted Store',
            'email' => null,
            'password_hash' => null,
        ]);
        
        Log::channel('audit')->info('Tenant deleted', [
            'tenant_id' => $tenant->id,
            'deleted_at' => now(),
        ]);
    }
}
```

---

### 10. Performance & Scalability with stancl/tenancy

**Decision**: Leverage stancl/tenancy's built-in caching and optimization

**Rationale**:
- Tenant identification cached automatically
- Database connections pooled by Laravel
- Cache tags for tenant isolation
- Queue tenancy for job isolation

**Caching Strategy**:

```php
// stancl/tenancy automatically tags cache with tenant ID
use Illuminate\Support\Facades\Cache;

// Tenant-scoped cache
Cache::remember('products.list', 3600, function () {
    return Product::all();
});

// This is automatically tagged with tenant ID
// No cross-tenant cache leakage
```

**Cache Configuration** (`config/tenancy.php`):

```php
'cache' => [
    'tag_base' => 'tenant',
    // Cache is automatically tagged with tenant ID
],
```

**Queue Tenancy**:

```php
// Jobs are automatically tenant-scoped
use Stancl\Tenancy\Queue\TenantedQueue;

class ProcessOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;
    
    public function handle(): void
    {
        // Automatically runs in tenant context
        $order = $this->order;
        // Process order...
    }
}

// Dispatch
ProcessOrder::dispatch($order);
```

**Queue Configuration** (`config/queue.php`):

```php
'default' => env('QUEUE_CONNECTION', 'database'),

'connections' => [
    'database' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
        'after_commit' => false,
    ],
],
```

**Monitoring Metrics**:

- Active tenant connections
- Average response time per tenant
- Database query counts
- Cache hit rates
- Queue job processing times

---

## Technology Decisions Summary

| Decision Area | Choice | Justification |
|---------------|--------|---------------|
| **Tenancy Package** | stancl/tenancy v3 | Industry standard, most mature, automatic DB isolation |
| **Tenancy Type** | Multi-database | Maximum isolation, independent backups, compliance-ready |
| **Tenant Identification** | Domain-based (stancl middleware) | Built-in, reliable, supports subdomains + custom domains |
| **Admin Panel** | Filament 4 + stancl integration | Already installed, official stancl integration |
| **File Storage** | stancl filesystem tenancy + Media Library | Automatic tenant switching, existing dependency |
| **Domain Verification** | DNS TXT records | Industry standard, non-intrusive |
| **SSL** | Laravel Ploi / Forge automation | Managed service, automatic renewal |
| **Backups** | spatie/laravel-backup + tenant-aware commands | Industry standard, per-tenant backups |
| **Testing** | PHPUnit + stancl test helpers | Official testing support |
| **Caching** | stancl cache tags | Automatic tenant isolation |
| **Queues** | stancl queue tenancy | Automatic tenant context in jobs |

---

## stancl/tenancy Installation & Configuration

### Step 1: Install Package

```bash
composer require stancl/tenancy
```

### Step 2: Publish Configuration

```bash
php artisan vendor:publish --provider="Stancl\Tenancy\TenancyServiceProvider" --tag=config
php artisan vendor:publish --provider="Stancl\Tenancy\TenancyServiceProvider" --tag=migrations
```

### Step 3: Configure Environment

```env
# Central database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE_CENTRAL=sater_central
DB_USERNAME=root
DB_PASSWORD=secret

# Tenant database template
TENANCY_TEMPLATE_DATABASE=mysql_template
```

### Step 4: Run Central Migrations

```bash
php artisan migrate
```

### Step 5: Create Template Database

```bash
# Create template database for tenant provisioning
mysql -u root -p -e "CREATE DATABASE mysql_template CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

---

## Unresolved Questions

**None** - All technical decisions resolved using stancl/tenancy's built-in functionality.

---

## References

- **stancl/tenancy Documentation**: https://tenancyforlaravel.com
- **GitHub Repository**: https://github.com/stancl/tenancy
- **Filament Documentation**: https://filamentphp.com/docs
- **Spatie Laravel Permission**: https://spatie.be/docs/laravel-permission
- **Spatie Laravel Media Library**: https://spatie.be/docs/laravel-medialibrary
- **Spatie Laravel Backup**: https://spatie.be/docs/laravel-backup
- **Laravel Documentation**: https://laravel.com/docs
