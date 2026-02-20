# Quickstart: Multi-Tenancy Infrastructure (stancl/tenancy)

**Feature**: Multi-Tenancy Infrastructure  
**Date**: 20 فبراير 2026  
**Branch**: `001-multi-tenancy-infrastructure-with-tenant-registration-domain-management-data-isolation-and-central-admin-panel`

## Overview

This quickstart guide provides step-by-step instructions for setting up and testing the multi-tenancy infrastructure using **stancl/tenancy** package in a local development environment.

---

## Prerequisites

- PHP 8.2+ installed
- Composer installed
- Node.js & npm installed
- MySQL 8.0+ or PostgreSQL 14+
- Git installed
- Docker (optional, for containerized development)

---

## Step 1: Clone and Install

```bash
# Navigate to project root
cd D:\Projects\sater-backend

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

---

## Step 2: Install stancl/tenancy

```bash
# Install stancl/tenancy package
composer require stancl/tenancy

# Publish configuration and migrations
php artisan vendor:publish --provider="Stancl\Tenancy\TenancyServiceProvider" --tag=config
php artisan vendor:publish --provider="Stancl\Tenancy\TenancyServiceProvider" --tag=migrations
```

---

## Step 3: Configure Environment

Edit `.env` file with your database credentials:

```env
# Central Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE_CENTRAL=sater_central
DB_USERNAME=root
DB_PASSWORD=your_password

# Tenant Database Template
TENANCY_TEMPLATE_DATABASE=mysql_template

# Mail Configuration (for email verification)
MAIL_MAILER=log
# Or use SMTP for actual email sending:
# MAIL_MAILER=smtp
# MAIL_HOST=smtp.mailtrap.io
# MAIL_PORT=2525
# MAIL_USERNAME=your_username
# MAIL_PASSWORD=your_password
# MAIL_FROM_ADDRESS=noreply@sater.com
# MAIL_FROM_NAME="${APP_NAME}"

# App URL
APP_URL=http://localhost:8000
```

---

## Step 4: Create Template Database

```bash
# Create template database for tenant provisioning
mysql -u root -p -e "CREATE DATABASE mysql_template CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

---

## Step 5: Run Migrations

```bash
# Run central database migrations (includes stancl/tenancy tables)
php artisan migrate
```

---

## Step 6: Create Initial Admin User

```bash
php artisan tinker
```

```php
use App\Models\AdminUser;

AdminUser::create([
    'name' => 'Platform Admin',
    'email' => 'admin@sater.com',
    'password' => bcrypt('SecurePassword123!'),
    'is_super_admin' => true,
    'is_active' => true,
]);
```

---

## Step 6: Create Subscription Plans

```bash
php artisan tinker
```

```php
use Modules\MultiTenancy\Entities\SubscriptionPlan;

// Starter Plan (Free)
SubscriptionPlan::create([
    'name' => 'Starter',
    'slug' => 'starter',
    'description' => 'Perfect for small stores getting started',
    'price_monthly' => 0,
    'price_yearly' => 0,
    'features' => [
        'products_limit' => 50,
        'storage_gb' => 2,
        'users_limit' => 2,
        'custom_domain' => false,
        'analytics' => 'basic',
        'support_level' => 'email',
    ],
    'trial_days' => 14,
    'is_active' => true,
]);

// Professional Plan
SubscriptionPlan::create([
    'name' => 'Professional',
    'slug' => 'professional',
    'description' => 'For growing businesses',
    'price_monthly' => 299,
    'price_yearly' => 2990,
    'features' => [
        'products_limit' => 1000,
        'storage_gb' => 20,
        'users_limit' => 10,
        'custom_domain' => true,
        'analytics' => 'advanced',
        'support_level' => 'priority',
    ],
    'trial_days' => 14,
    'is_active' => true,
]);

// Enterprise Plan
SubscriptionPlan::create([
    'name' => 'Enterprise',
    'slug' => 'enterprise',
    'description' => 'For large-scale operations',
    'price_monthly' => 999,
    'price_yearly' => 9990,
    'features' => [
        'products_limit' => -1, // unlimited
        'storage_gb' => 100,
        'users_limit' => -1, // unlimited
        'custom_domain' => true,
        'analytics' => 'advanced',
        'support_level' => 'dedicated',
    ],
    'trial_days' => 30,
    'is_active' => true,
]);
```

---

## Step 7: Start Development Server

```bash
# Start the Laravel development server
php artisan serve

# In another terminal, start Vite for frontend assets
npm run dev

# In another terminal, start queue worker (for emails, etc.)
php artisan queue:work
```

Visit: http://localhost:8000

---

## Step 8: Test Tenant Registration

### Option A: Via API (Postman/cURL)

```bash
# Register a new tenant
curl -X POST http://localhost:8000/api/v1/tenants/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "store_name": "Fashion House",
    "email": "owner@fashionhouse.com",
    "password": "SecurePass123!",
    "subdomain": "fashionhouse",
    "language": "en"
  }'
```

Expected response:
```json
{
  "message": "Registration successful. Please check your email to verify your account.",
  "tenant": {
    "id": "uuid-string",
    "store_name": "Fashion House",
    "subdomain": "fashionhouse",
    "email": "owner@fashionhouse.com",
    "language": "en",
    "status": "pending_email_verification"
  }
}
```

### Option B: Via Web Interface

1. Navigate to http://localhost:8000/register
2. Fill in the registration form:
   - Store Name: Fashion House
   - Email: owner@fashionhouse.com
   - Password: SecurePass123!
   - Subdomain: fashionhouse
   - Language: English
3. Click "Create Store"
4. Check email (or logs if using `MAIL_MAILER=log`)

---

## Step 9: Verify Email

If using `MAIL_MAILER=log`, check `storage/logs/laravel.log`:

```bash
tail -f storage/logs/laravel.log
```

Find the verification URL and visit it in your browser, or use the API:

```bash
curl http://localhost:8000/tenants/verify/{token-from-email}
```

---

## Step 10: Subscribe to a Plan

```bash
curl -X POST http://localhost:8000/api/v1/tenants/{tenant-id}/subscribe \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "plan_id": "uuid-of-professional-plan",
    "billing_cycle": "monthly"
  }'
```

---

## Step 11: Access Tenant Dashboard

After subscription, access your tenant dashboard:

```
http://fashionhouse.localhost:8000/dashboard
```

**Note**: For local development, you may need to configure wildcard subdomains.

### Configure Wildcard Subdomains (Local)

#### Option 1: Edit Hosts File

Add to `/etc/hosts` (Linux/Mac) or `C:\Windows\System32\drivers\etc\hosts` (Windows):

```
127.0.0.1   fashionhouse.localhost
127.0.0.1   techstore.localhost
127.0.0.1   *.localhost
```

#### Option 2: Use Laravel Valet (Mac/Linux)

```bash
valet install
valet link sater
```

Now access: http://fashionhouse.sater.test

#### Option 3: Use Docker

Update `compose.yaml` with wildcard domain configuration.

---

## Step 12: Test Data Isolation

Create two tenants and verify isolation:

```bash
# Tenant 1: Fashion House
curl -X POST http://localhost:8000/api/v1/tenants/register \
  -d '{"store_name":"Fashion House","email":"fashion@test.com","password":"Test123!","subdomain":"fashionhouse","language":"en"}'

# Tenant 2: Tech Store
curl -X POST http://localhost:8000/api/v1/tenants/register \
  -d '{"store_name":"Tech Store","email":"tech@test.com","password":"Test123!","subdomain":"techstore","language":"en"}'
```

After verification and subscription, log into each tenant and verify:
- Products created in Fashion House are NOT visible in Tech Store
- Users created in one tenant cannot access the other
- Database connections are separate

---

## Step 13: Test Admin Panel

Access the central admin panel:

```
http://localhost:8000/admin
```

Login with admin credentials:
- Email: admin@sater.com
- Password: SecurePassword123!

### Admin Actions to Test:

1. **View Tenants List**
   - Navigate to Admin → Tenants
   - Verify both tenants appear

2. **Suspend a Tenant**
   - Click on "Fashion House"
   - Click "Suspend"
   - Add reason: "Testing suspension"
   - Verify tenant cannot access their store

3. **Reactivate Tenant**
   - Click "Activate"
   - Verify tenant access restored

4. **View Statistics**
   - Check product count, order count, revenue
   - Verify metrics are accurate

---

## Step 14: Test Domain Management

### Add Custom Domain

```bash
# First, authenticate and get token
curl -X POST http://localhost:8000/api/v1/login \
  -d '{"email":"owner@fashionhouse.com","password":"SecurePass123!"}'

# Add custom domain
curl -X POST http://localhost:8000/api/v1/tenants/{tenant-id}/domains \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "www.fashionhouse.com",
    "type": "custom"
  }'
```

### Verify Domain (Simulated)

In production, add DNS TXT record. For local testing, manually mark as verified:

```bash
php artisan tinker
```

```php
use Modules\MultiTenancy\Entities\Domain;

$domain = Domain::where('name', 'www.fashionhouse.com')->first();
$domain->update([
    'verified' => true,
    'verified_at' => now(),
]);
```

---

## Testing Checklist

- [ ] Tenant registration completes successfully
- [ ] Email verification works
- [ ] Subscription plan selection works
- [ ] Tenant database created and isolated
- [ ] Tenant can access dashboard
- [ ] Admin can view all tenants
- [ ] Admin can suspend/activate tenants
- [ ] Data isolation verified (cross-tenant access blocked)
- [ ] Custom domain can be added
- [ ] Subdomain change works (once free)
- [ ] Multi-language (AR/EN) works in registration

---

## Troubleshooting

### Issue: Tenant database not created

**Solution**: Ensure database user has CREATE DATABASE privileges:

```sql
GRANT CREATE ON *.* TO 'your_username'@'localhost';
FLUSH PRIVILEGES;
```

### Issue: Email not sending

**Solution**: Use `MAIL_MAILER=log` for development and check logs:

```bash
tail -f storage/logs/laravel.log
```

### Issue: Subdomain not resolving

**Solution**: Add wildcard entry to hosts file or use Laravel Valet.

### Issue: Middleware not identifying tenant

**Solution**: Check middleware order in `app/Http/Kernel.php`:

```php
protected $middlewarePriority = [
    \Modules\MultiTenancy\Http\Middleware\IdentifyTenant::class,
    // ... other middleware
];
```

### Issue: Permission denied for tenant database

**Solution**: Ensure database user has proper grants:

```sql
GRANT ALL PRIVILEGES ON tenant_*.* TO 'your_username'@'localhost';
FLUSH PRIVILEGES;
```

---

## Next Steps

After completing quickstart:

1. **Review Full Documentation**:
   - [spec.md](./spec.md) - Feature specification
   - [data-model.md](./data-model.md) - Data model details
   - [contracts/api.md](./contracts/api.md) - API contracts
   - [research.md](./research.md) - Technical research

2. **Run Tests**:
   ```bash
   php artisan test --filter MultiTenancy
   ```

3. **Review Tasks**:
   - See [tasks.md](./tasks.md) for implementation tasks

4. **Development Workflow**:
   ```bash
   # Create feature branch
   git checkout -b feature/multi-tenancy-component
   
   # Make changes, run tests
   php artisan test
   
   # Commit and push
   git add .
   git commit -m "feat: implement tenant registration"
   git push origin feature/multi-tenancy-component
   ```

---

## References

- Specification: [spec.md](./spec.md)
- Data Model: [data-model.md](./data-model.md)
- API Contracts: [contracts/api.md](./contracts/api.md)
- Research: [research.md](./research.md)
- Plan: [plan.md](./plan.md)
