# Implementation Plan: Multi-Tenancy Infrastructure (stancl/tenancy)

**Branch**: `001-multi-tenancy-infrastructure-with-tenant-registration-domain-management-data-isolation-and-central-admin-panel` | **Date**: 20 فبراير 2026 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification for Multi-Tenancy Infrastructure using stancl/tenancy package

## Summary

Build a multi-tenancy infrastructure using the `stancl/tenancy` package for Laravel, enabling merchants to register isolated stores with unique subdomains or custom domains. Each tenant gets a separate database for complete data isolation, with a central admin panel for platform oversight. The system supports Arabic/English interfaces and automated HTTPS provisioning.

## Technical Context

**Language/Version**: PHP 8.2+
**Primary Dependencies**: Laravel 12, stancl/tenancy (v3), Filament 4 (admin panel), Spatie Laravel Permission, Spatie Laravel Media Library
**Storage**: MySQL/PostgreSQL (separate database per tenant via stancl/tenancy), Laravel Media Library for file storage
**Testing**: PHPUnit 11, Laravel Pail for logging
**Target Platform**: Web server (Linux/Windows via Docker compose.yaml)
**Project Type**: Modular Laravel web application with stancl/tenancy
**Performance Goals**: 1000 concurrent tenants, subdomain checks <1 second, API response <200ms p95
**Constraints**: Complete data isolation between tenants, 99.9% uptime, HTTPS by default
**Scale/Scope**: 1,000+ concurrent tenants, isolated database per tenant, multi-language (AR/EN)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

**Status**: ⚠️ CONSTITUTION NOT YET RATIFIED

The `.specify/memory/constitution.md` file contains placeholder templates and has not been ratified with actual project principles. Proceeding with stancl/tenancy best practices and Laravel multi-tenancy standards.

**Gates to be defined** (pending constitution ratification):
- [ ] Principle 1: [TBD - Constitution not ratified]
- [ ] Principle 2: [TBD - Constitution not ratified]
- [ ] Principle 3: Test-First (TDD mandatory)
- [ ] Principle 4: Integration Testing for tenant isolation
- [ ] Principle 5: Observability & Simplicity

**Action Required**: Constitution must be ratified before Phase 2. For now, proceeding with stancl/tenancy patterns which provide:
- Automatic tenant identification via domain
- Database isolation per tenant
- Built-in migration commands for tenant databases
- Event system for tenant lifecycle hooks

## Project Structure

### Documentation (this feature)

```text
specs/001-multi-tenancy-infrastructure-with-tenant-registration-domain-management-data-isolation-and-central-admin-panel/
├── plan.md              # This file
├── spec.md              # Feature specification
├── research.md          # Phase 0 output (to be generated)
├── data-model.md        # Phase 1 output (to be generated)
├── quickstart.md        # Phase 1 output (to be generated)
├── contracts/           # Phase 1 output (to be generated)
└── tasks.md             # Phase 2 output (NOT created by /speckit.plan)
checklists/
└── requirements.md      # Specification quality checklist
```

### Source Code (repository root)

```text
app/
├── Models/
│   ├── Tenant.php (extends Stancl\Tenancy\Database\Models\Tenant)
│   ├── Domain.php (extends Stancl\Tenancy\Database\Models\Domain)
│   ├── User.php (tenant-scoped user model)
│   └── AdminUser.php (central admin)
├── Providers/
│   └── TenancyServiceProvider.php
└── Http/
    ├── Controllers/
    │   ├── TenantRegistrationController.php
    │   ├── DomainManagementController.php
    │   └── Admin/
    │       ├── TenantManagementController.php
    │       └── SubscriptionPlanController.php
    └── Middleware/
        └── TenantSubscriptionMiddleware.php

routes/
├── tenant.php           # Tenant routes (automatically scoped by stancl/tenancy)
└── web.php              # Central routes (admin, registration)

database/
├── migrations/
│   ├── tenant_database/  # Central tenant storage migrations
│   └── migrations/       # Application migrations
└── multi-tenancy/
    └── tenants/          # Individual tenant databases (auto-created)

Modules/ (if using nwidart/laravel-modules)
└── MultiTenancy/
    ├── Http/
    │   └── Controllers/
    ├── Database/
    │   └── migrations/
    └── Resources/

tests/
├── Feature/
│   └── MultiTenancy/
│       ├── TenantRegistrationTest.php
│       ├── DataIsolationTest.php
│       └── DomainManagementTest.php
└── Integration/
    └── MultiTenancy/
        └── TenantDatabaseIsolationTest.php
```

**Structure Decision**: Using stancl/tenancy v3 as the core multi-tenancy package because:
- Most mature and widely-adopted Laravel multi-tenancy package (2.5k+ stars)
- Automatic tenant identification from domain/subdomain
- Built-in database isolation (separate DB per tenant)
- Automatic migration execution for tenant databases
- Event-driven architecture for lifecycle hooks
- Compatible with Filament admin panel
- Supports both single-database (scope) and multi-database tenancy
- Built-in cache, filesystem, and queue tenancy
- Active maintenance and comprehensive documentation

**Tenancy Type**: Multi-database tenancy (each tenant gets separate database)

**Key stancl/tenancy Features Used**:
- `Tenant` and `Domain` models (extended) for tenant management
- Domain-based tenant identification middleware
- Automatic database creation/deletion
- Tenant-aware migrations (`php artisan tenants:migrate`)
- Tenant config driver for isolated configuration
- Filesystem tenancy for isolated storage
- Cache tenancy for isolated caching
- Queue tenancy for job isolation

---

## Phase 0: Research ✅ COMPLETED

**Status**: All technical decisions resolved using stancl/tenancy  
**Output**: [research.md](./research.md)

### Key Decisions Made

| Decision Area | Choice | Reference |
|---------------|--------|-----------|
| **Tenancy Package** | stancl/tenancy v3 | research.md#1-why-stancl-tenancy-over-custom-implementation |
| **Tenancy Type** | Multi-database | research.md#3-database-isolation-with-stancl-tenancy |
| **Tenant Identification** | Domain-based (stancl middleware) | research.md#2-tenant-identification--domain-routing |
| **Admin Panel** | Filament 4 + stancl integration | research.md#6-central-admin-panel-with-filament |
| **File Storage** | stancl filesystem tenancy + Media Library | research.md#7-file-storage-isolation |
| **Domain Verification** | DNS TXT records | research.md#4-domain-management--verification |
| **SSL** | Laravel Ploi / Forge automation | research.md#4-domain-management--verification |
| **Backups** | spatie/laravel-backup + tenant-aware commands | research.md#8-backup-strategy-with-stancl-tenancy |
| **Caching** | stancl cache tags | research.md#10-performance--scalability-with-stancl-tenancy |
| **Queues** | stancl queue tenancy | research.md#10-performance--scalability-with-stancl-tenancy |

### Why stancl/tenancy

- **Industry Standard**: 2,500+ stars, 1M+ downloads, most popular Laravel multi-tenancy package
- **Multi-Database Support**: Automatic database creation, migration, and deletion per tenant
- **Automatic Tenant Identification**: Domain-based routing out of the box
- **Complete Solution**: Handles database, cache, filesystem, queue, and config tenancy
- **Event-Driven**: Lifecycle hooks for tenant creation, deletion, and initialization
- **Filament Compatible**: Official integration with Filament admin panel
- **Active Maintenance**: Regular updates, Laravel 12 compatible, active community
- **Comprehensive Documentation**: https://tenancyforlaravel.com

---

## Phase 1: Design & Contracts ✅ COMPLETED

**Status**: All design artifacts generated  
**Outputs**:
- [data-model.md](./data-model.md) - Entity definitions extending stancl/tenancy models
- [contracts/api.md](./contracts/api.md) - API endpoint specifications
- [quickstart.md](./quickstart.md) - Setup and testing guide with stancl/tenancy installation

### Generated Artifacts

| Artifact | Purpose | Status |
|----------|---------|--------|
| data-model.md | Complete entity definitions with stancl/tenancy integration | ✅ Complete |
| contracts/api.md | OpenAPI-style endpoint specifications | ✅ Complete |
| quickstart.md | Step-by-step setup guide with stancl/tenancy installation | ✅ Complete |
| Agent Context Updated | Qwen agent aware of stancl/tenancy | ✅ Complete |

### stancl/tenancy Model Extensions

**Tenant Model** (extends `Stancl\Tenancy\Database\Models\Tenant`):
- Uses `HasDatabase` trait for automatic database creation
- Uses `HasDomains` trait for domain relationship
- Custom fields: `store_name`, `email`, `password_hash`, `language`, `status`, `current_plan_id`
- Event listeners for tenant lifecycle

**Domain Model** (extends `Stancl\Tenancy\Database\Models\Domain`):
- Custom fields: `verified`, `verification_token`, `ssl_status`, `is_primary`
- DNS TXT record verification method
- SSL provisioning integration

**Tenant-Scoped Models**:
- Use `BelongsToTenant` trait for automatic query scoping
- All queries automatically filtered by `tenant_id`
- No cross-tenant data leakage possible

### Constitution Check (Post-Design)

**Status**: ⚠️ CONSTITUTION NOT YET RATIFIED

The `.specify/memory/constitution.md` file remains a template. The design follows stancl/tenancy best practices:

- ✅ Test-First approach planned (PHPUnit tests with stancl test helpers)
- ✅ Integration testing for tenant isolation (stancl provides testing utilities)
- ✅ Automatic tenant identification via middleware
- ✅ CLI commands for tenant management (`tenants:migrate`, `tenants:seed`)
- ⏳ Constitution ratification pending

---

## Phase 2: Implementation Planning (Next Steps)

**Status**: Ready for task breakdown

### Recommended Next Actions

1. **Run `/speckit.tasks`**: Break down implementation into actionable tasks
2. **Install stancl/tenancy**: `composer require stancl/tenancy`
3. **Publish stancl/tenancy assets**: `php artisan vendor:publish --provider="Stancl\Tenancy\TenancyServiceProvider" --tag=config --tag=migrations`
4. **Extend Tenant and Domain models**: Add custom fields for subscription management
5. **Configure tenant database template**: Create `mysql_template` database
6. **Implement registration flow**: Use stancl/tenancy events for tenant creation
7. **Build Filament admin resources**: Use stancl/tenancy integration
8. **Write tests first**: Follow TDD using stancl test helpers

### Task Categories (to be defined)

- stancl/tenancy installation and configuration
- Tenant and Domain model extensions
- Central database migrations (tenants, domains, subscriptions)
- Tenant database migrations (users, products, orders)
- Tenant registration controller with email verification
- Domain management with DNS verification
- Filament admin resources for tenant management
- Subscription plan management
- Integration tests for tenant isolation
- Quickstart guide validation
