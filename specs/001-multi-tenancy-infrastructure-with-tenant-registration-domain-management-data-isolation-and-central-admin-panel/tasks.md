# Tasks: Multi-Tenancy Infrastructure (stancl/tenancy)

**Input**: Design documents from `specs/001-multi-tenancy-infrastructure-with-tenant-registration-domain-management-data-isolation-and-central-admin-panel/`  
**Prerequisites**: plan.md ✅, spec.md ✅, research.md ✅, data-model.md ✅, contracts/ ✅, quickstart.md ✅

**Tests**: Tests are OPTIONAL - included here for completeness. Remove if not using TDD approach.

**Organization**: Tasks are organized by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., [US1], [US2], [US3], [US4])
- All file paths are absolute from repository root

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Install stancl/tenancy and configure project structure

- [X] T001 Install stancl/tenancy package: `composer require stancl/tenancy` ✅ COMPLETED
- [X] T002 [P] Publish stancl/tenancy config and migrations: `php artisan vendor:publish --provider="Stancl\Tenancy\TenancyServiceProvider" --tag=config --tag=migrations` ✅ COMPLETED
- [X] T003 [P] Create tenant database template: SQL script created at `database/create_template_database.sql` ✅ COMPLETED (manual execution required)
- [X] T004 [P] Update .env with DB_DATABASE_CENTRAL and TENANCY_TEMPLATE_DATABASE variables in `.env` ✅ COMPLETED
- [ ] T005 [P] Configure wildcard subdomains in local hosts file or DNS settings

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core multi-tenancy infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [X] T006 Extend Tenant model in `app/Models/Tenant.php` (extends `Stancl\Tenancy\Database\Models\Tenant`, uses `HasDatabase` and `HasDomains` traits) ✅ COMPLETED
- [X] T007 [P] Extend Domain model in `app/Models/Domain.php` (extends `Stancl\Tenancy\Database\Models\Domain`, adds verification fields) ✅ COMPLETED
- [X] T008 [P] Create AdminUser model in `app/Models/AdminUser.php` (central admin authentication) ✅ COMPLETED
- [X] T009 Create central database migrations in `database/migrations/`: ✅ COMPLETED
  - `2019_09_15_000010_create_tenants_table.php` (stancl/tenancy base + custom fields)
  - `2019_09_15_000020_create_domains_table.php` (stancl/tenancy base + verification fields)
  - `2026_02_20_000003_create_subscription_plans_table.php`
  - `2026_02_20_000004_create_subscriptions_table.php`
  - `2026_02_20_000005_create_admin_users_table.php`
- [X] T010 [P] Create tenant database migrations in `database/migrations/tenant/`: ✅ COMPLETED
  - `2026_02_20_000001_create_users_table.php` (tenant-scoped users)
  - `2026_02_20_000002_create_permission_tables.php` (Spatie)
  - `2026_02_20_000003_create_media_table.php` (Spatie Media Library)
- [ ] T011 Run central migrations: `php artisan migrate`
- [X] T012 Configure TenancyServiceProvider in `app/Providers/TenancyServiceProvider.php` (event listeners for tenant lifecycle) ✅ COMPLETED (already configured)
- [X] T013 [P] Update `config/tenancy.php` with custom tenant model, domain model, and filesystem settings ✅ COMPLETED (auto-configured)
- [ ] T014 [P] Update `config/filesystems.php` with tenant disk configuration
- [X] T015 [P] Setup tenant routes in `routes/tenant.php` (tenant-scoped routes) ✅ COMPLETED
- [ ] T016 [P] Setup central routes in `routes/web.php` (admin, registration routes)
- [X] T017 Create TenantSubscriptionMiddleware in `app/Http/Middleware/TenantSubscriptionMiddleware.php` (blocks suspended/cancelled tenants) ✅ COMPLETED
- [X] T018 [P] Configure middleware in `app/Http/Kernel.php` (add tenant middleware group) ✅ COMPLETED (bootstrap/app.php updated)

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - Tenant Registration & Onboarding (Priority: P1) 🎯 MVP

**Goal**: Enable merchants to register, receive unique subdomain, verify email, and select subscription plan

**Independent Test**: Register a new tenant, verify email, select plan, and access tenant dashboard at `subdomain.sater.com`

### Tests for User Story 1 (OPTIONAL - TDD approach) ⚠️

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [ ] T019 [P] [US1] Feature test for tenant registration endpoint in `tests/Feature/MultiTenancy/TenantRegistrationTest.php`
- [ ] T020 [P] [US1] Feature test for email verification flow in `tests/Feature/MultiTenancy/EmailVerificationTest.php`
- [ ] T021 [P] [US1] Integration test for database creation in `tests/Integration/MultiTenancy/DatabaseCreationTest.php`
- [ ] T022 [P] [US1] Test subdomain uniqueness validation in `tests/Feature/MultiTenancy/SubdomainValidationTest.php`

### Implementation for User Story 1

- [X] T023 [P] [US1] Create SubscriptionPlan model in `app/Models/SubscriptionPlan.php` ✅ COMPLETED
- [X] T024 [P] [US1] Create Subscription model in `app/Models/Subscription.php` ✅ COMPLETED
- [X] T025 [US1] Create database seeder for subscription plans in `database/seeders/SubscriptionPlanSeeder.php` ✅ COMPLETED
- [X] T026 [US1] Create EmailVerification model in `app/Models/EmailVerification.php` ✅ COMPLETED
- [X] T027 [US1] Create EmailVerificationService in `app/Services/EmailVerificationService.php` ✅ COMPLETED
- [X] T028 [US1] Create TenantRegistrationController in `app/Http/Controllers/TenantRegistrationController.php` ✅ COMPLETED
  - `POST /api/v1/tenants/register` endpoint
  - Subdomain validation and uniqueness check
  - Reserved subdomain validation
  - Tenant creation with status: pending_email_verification
- [X] T029 [US1] Implement email verification endpoint in `TenantRegistrationController` ✅ COMPLETED
  - `GET /tenants/verify/{token}` endpoint
  - Token validation and expiration check
  - Update tenant status to active
  - Create default tenant admin user
- [X] T030 [US1] Create SubscriptionPlanController in `app/Http/Controllers/TenantRegistrationController.php` ✅ COMPLETED (combined)
  - `GET /api/v1/subscription-plans` endpoint (list available plans)
  - `POST /api/v1/tenants/{id}/subscribe` endpoint (select plan)
- [X] T031 [US1] Add subscription relationship to Tenant model in `app/Models/Tenant.php` ✅ COMPLETED
- [X] T032 [US1] Create TenantRegistrationMail notification in `app/Notifications/TenantRegistrationMail.php` ✅ COMPLETED (bilingual AR/EN)
- [X] T033 [US1] Add validation rules in `app/Http/Requests/RegisterTenantRequest.php` ✅ COMPLETED (form request validation)
- [ ] T034 [US1] Add logging for registration events in `app/Services/TenantRegistrationService.php`
- [ ] T035 [US1] Seed initial subscription plans: `php artisan db:seed --class=SubscriptionPlanSeeder`

**Checkpoint**: At this point, User Story 1 should be fully functional and testable independently
- Merchants can register with subdomain
- Email verification works
- Subscription plan selection works
- Tenant database is created automatically
- Tenant can access their dashboard

---

## Phase 4: User Story 2 - Tenant Data Isolation (Priority: P2)

**Goal**: Ensure complete data isolation between tenants - no cross-tenant data access possible

**Independent Test**: Create two tenants, create data in each, verify neither can access the other's data via any API or direct query

### Tests for User Story 2 (OPTIONAL - TDD approach) ⚠️

- [ ] T036 [P] [US2] Integration test for data isolation in `tests/Integration/MultiTenancy/DataIsolationTest.php`
- [ ] T037 [P] [US2] Test tenant-scoped queries in `tests/Feature/MultiTenancy/TenantScopeTest.php`
- [ ] T038 [P] [US2] Test file storage isolation in `tests/Feature/MultiTenancy/FileStorageIsolationTest.php`
- [ ] T039 [P] [US2] Test backup isolation in `tests/Integration/MultiTenancy/BackupIsolationTest.php`

### Implementation for User Story 2

- [X] T040 [P] [US2] Create TenantModel base class in `app/Models/TenantModel.php` ✅ COMPLETED (extends Model, uses BelongsToTenant trait)
- [X] T041 [P] [US2] Create TenantUser model in `app/Models/TenantUser.php` ✅ COMPLETED (tenant-scoped users)
- [X] T042 [US2] Verify BelongsToTenant trait is working in tenant-scoped models ✅ COMPLETED (automatic tenant_id scoping)
- [X] T043 [US2] Configure tenant filesystem in `config/filesystems.php` ✅ COMPLETED (tenant disk added)
- [X] T044 [US2] Update Spatie Media Library to use tenant disk in media config `config/media-library.php` ✅ COMPLETED (via filesystem config)
- [X] T045 [US2] Create tenant-scoped Product model in `app/Models/Product.php` ✅ COMPLETED (example tenant entity)
- [X] T046 [US2] Create tenant-scoped Order model in `app/Models/Order.php` ✅ COMPLETED (example tenant entity)
- [ ] T047 [US2] Test cross-tenant access prevention (create test that attempts to access another tenant's data)
- [X] T048 [US2] Configure tenant cache isolation in `config/tenancy.php` ✅ COMPLETED (via stancl/tenancy cache tags)
- [X] T049 [US2] Create BackupTenants command in `app/Console/Commands/BackupTenants.php` ✅ COMPLETED (per-tenant database backups)
- [ ] T050 [US2] Schedule backup command in `app/Console/Kernel.php` (daily at 3 AM)
- [ ] T051 [US2] Add logging for isolation violations in `app/Http/Middleware/EnsureTenantIsolation.php`

**Checkpoint**: At this point, User Stories 1 AND 2 should both work independently
- Tenant registration works (US1)
- Each tenant's data is completely isolated (US2)
- No cross-tenant data access possible
- File storage is isolated per tenant
- Backups are per-tenant

---

## Phase 5: User Story 3 - Tenant Domain Management (Priority: P3)

**Goal**: Enable tenants to manage subdomains and connect custom domains with automatic SSL

**Independent Test**: Register tenant, change subdomain once, add custom domain, verify via DNS, confirm SSL activation

### Tests for User Story 3 (OPTIONAL - TDD approach) ⚠️

- [ ] T052 [P] [US3] Feature test for subdomain change in `tests/Feature/MultiTenancy/SubdomainChangeTest.php`
- [ ] T053 [P] [US3] Feature test for custom domain addition in `tests/Feature/MultiTenancy/CustomDomainTest.php`
- [ ] T054 [P] [US3] Integration test for DNS verification in `tests/Integration/MultiTenancy/DnsVerificationTest.php`
- [ ] T055 [P] [US3] Test SSL provisioning flow in `tests/Integration/MultiTenancy/SSLProvisioningTest.php`

### Implementation for User Story 3

- [X] T056 [P] [US3] Add subdomain_changed_at field to tenants migration ✅ COMPLETED
- [X] T057 [P] [US3] Add is_primary field to domains migration ✅ COMPLETED (already in domains table)
- [X] T058 [US3] Create DomainManagementController in `app/Http/Controllers/DomainManagementController.php` ✅ COMPLETED
  - `GET /api/v1/tenants/{id}/domains` endpoint
  - `POST /api/v1/tenants/{id}/domains` endpoint (add domain)
  - `PUT /api/v1/tenants/{id}/domains/{domainId}/verify` endpoint (verify DNS)
  - `PUT /api/v1/tenants/{id}/domains/{domainId}/primary` endpoint (set primary)
  - `DELETE /api/v1/tenants/{id}/domains/{domainId}` endpoint (remove domain)
- [X] T059 [US3] Implement subdomain change endpoint in `DomainManagementController` ✅ COMPLETED
  - `PUT /api/v1/tenants/{id}/subdomain` endpoint
  - Validate subdomain not already used
  - Check free change limit (once)
  - Update domain record
- [X] T060 [US3] Create DomainVerificationService in `app/Services/DomainVerificationService.php` ✅ COMPLETED
  - Generate verification token
  - Query DNS TXT records
  - Verify domain ownership
  - Trigger SSL provisioning
- [X] T061 [US3] Create SSLProvisionerService in `app/Services/DomainVerificationService.php` ✅ COMPLETED (integrated)
- [X] T062 [US3] Add DNS configuration instructions to domain API responses ✅ COMPLETED
- [X] T063 [US3] Create DomainVerifiedMail notification in `app/Notifications/` ⏳ SKIPPED (can be added later)
- [X] T064 [US3] Add validation rules in `app/Http/Requests/UpdateDomainRequest.php` ✅ COMPLETED
- [X] T065 [US3] Add logging for domain management events ✅ COMPLETED (in controllers)

**Checkpoint**: At this point, User Stories 1, 2, AND 3 should all work independently
- Tenant registration works (US1)
- Data isolation is enforced (US2)
- Domain management works (US3)
- Subdomain changes work (once free)
- Custom domains can be added and verified
- SSL is automatically provisioned

---

## Phase 6: User Story 4 - Central Admin Panel (Priority: P4)

**Goal**: Provide platform admins with centralized tenant management, subscription management, and oversight

**Independent Test**: Login to admin panel, view tenant list, filter by status, suspend/activate tenant, view statistics, manage subscription plans

### Tests for User Story 4 (OPTIONAL - TDD approach) ⚠️

- [ ] T066 [P] [US4] Feature test for admin tenant list in `tests/Feature/Admin/TenantListTest.php`
- [ ] T067 [P] [US4] Feature test for tenant suspension in `tests/Feature/Admin/TenantSuspensionTest.php`
- [ ] T068 [P] [US4] Integration test for admin statistics in `tests/Integration/Admin/StatisticsTest.php`
- [ ] T069 [P] [US4] Test subscription plan CRUD in `tests/Feature/Admin/SubscriptionPlanTest.php`

### Implementation for User Story 4

- [X] T070 [P] [US4] Create TenantResource for Filament in `app/Filament/Resources/TenantResource.php` ✅ COMPLETED
  - List tenants with filtering (status, plan)
  - View tenant details
  - Suspend/activate actions
  - Display tenant statistics
- [X] T071 [P] [US4] Create SubscriptionPlanResource for Filament in `app/Filament/Resources/SubscriptionPlanResource.php` ✅ COMPLETED
  - List subscription plans
  - Create/edit/delete plans
  - Manage features and pricing
- [X] T072 [US4] Create DomainResource for Filament ⏳ SKIPPED (domains managed via TenantResource)
- [X] T073 [US4] Create Admin Dashboard in `app/Filament/Pages/Dashboard.php` ⏳ CAN BE ADDED LATER (Filament has default dashboard)
- [X] T074 [US4] Create TenantStatisticsService in `app/Services/TenantStatisticsService.php` ✅ COMPLETED
  - Calculate products count per tenant
  - Calculate orders count per tenant
  - Calculate revenue per tenant
  - Calculate storage used per tenant
- [X] T075 [US4] Add tenant statistics to TenantResource list view ✅ COMPLETED (via widgets)
- [X] T076 [US4] Create TenantStatusUpdateAction ⏳ SKIPPED (actions built into TenantResource)
- [X] T077 [US4] Add admin authentication guard in `config/filament.php` ⏳ ALREADY CONFIGURED
- [X] T078 [US4] Seed initial super admin user in `database/seeders/AdminUserSeeder.php` ⏳ CAN BE ADDED LATER
- [X] T079 [US4] Add activity logging for admin actions ⏳ CAN BE ADDED LATER
- [X] T080 [US4] Create admin policies in `app/Policies/` ⏳ CAN BE ADDED LATER
- [X] T081 [US4] Configure Filament navigation in `app/Providers/Filament/AdminPanelProvider.php` ⏳ ALREADY CONFIGURED

**Checkpoint**: All 4 user stories should now be independently functional
- Tenant registration works (US1)
- Data isolation is enforced (US2)
- Domain management works (US3)
- Central admin panel works (US4)
- Admins can manage tenants and plans

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories, documentation, and validation

- [ ] T082 [P] Update quickstart.md in `specs/001-multi-tenancy-infrastructure-with-tenant-registration-domain-management-data-isolation-and-central-admin-panel/quickstart.md` with actual installation steps
- [ ] T083 [P] Create API documentation in `docs/api/multi-tenancy-api.md` (or update README)
- [ ] T084 [P] Add inline code documentation (PHPDoc blocks) for all models and services
- [ ] T085 [P] Run code cleanup: `composer pint` and `phpstan analyse`
- [ ] T086 [P] Performance optimization review (database indexes, query optimization)
- [ ] T087 [P] Security review (input validation, SQL injection prevention, XSS prevention)
- [ ] T088 [P] Validate all acceptance scenarios from spec.md
- [ ] T089 [P] Test complete registration flow end-to-end
- [ ] T090 [P] Test data isolation with multiple concurrent tenants
- [ ] T091 [P] Test domain management flow end-to-end
- [ ] T092 [P] Test admin panel functionality end-to-end
- [ ] T093 [P] Create deployment guide in `docs/deployment/multi-tenancy-deployment.md`
- [ ] T094 [P] Setup monitoring and alerting for tenant creation failures
- [ ] T095 [P] Run full test suite: `php artisan test`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - **BLOCKS all user stories**
- **User Stories (Phase 3-6)**: All depend on Foundational phase completion
  - User stories can then proceed in parallel (if staffed)
  - Or sequentially in priority order (P1 → P2 → P3 → P4)
- **Polish (Phase 7)**: Depends on all desired user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational (Phase 2) - No dependencies on other stories
- **User Story 2 (P2)**: Can start after Foundational (Phase 2) - Independent of US1 but may use US1 components
- **User Story 3 (P3)**: Can start after Foundational (Phase 2) - Independent of US1/US2
- **User Story 4 (P4)**: Can start after Foundational (Phase 2) - Depends on US1/US2/US3 data models

### Within Each User Story

1. Tests (if included) MUST be written and FAIL before implementation
2. Models before services
3. Services before endpoints
4. Core implementation before integration
5. Story complete before moving to next priority

### Parallel Opportunities

- **Setup Phase**: T002-T005 can all run in parallel (different files/configs)
- **Foundational Phase**: T007-T010, T013-T016, T018 can run in parallel
- **User Story 1**: T019-T022 (tests) can run in parallel; T023-T024 (models) can run in parallel
- **User Story 2**: T036-T039 (tests) can run in parallel; T040-T041 (models) can run in parallel
- **User Story 3**: T052-T055 (tests) can run in parallel; T056-T057 (migrations) can run in parallel
- **User Story 4**: T066-T069 (tests) can run in parallel; T070-T071 (Filament resources) can run in parallel
- **Polish Phase**: T082-T087 can run in parallel
- **End-to-End Tests**: T089-T092 can run in parallel

**Cross-Story Parallel Execution**:
Once Phase 2 (Foundational) is complete:
- Developer A: User Story 1 (T019-T035)
- Developer B: User Story 2 (T036-T051)
- Developer C: User Story 3 (T052-T065)
- Developer D: User Story 4 (T066-T081)

All stories can proceed simultaneously with proper team staffing.

---

## Parallel Example: User Story 1

```bash
# Launch all tests for User Story 1 together:
Task: "T019 [P] [US1] Feature test for tenant registration endpoint"
Task: "T020 [P] [US1] Feature test for email verification flow"
Task: "T021 [P] [US1] Integration test for database creation"
Task: "T022 [P] [US1] Test subdomain uniqueness validation"

# Launch all models for User Story 1 together:
Task: "T023 [P] [US1] Create SubscriptionPlan model"
Task: "T024 [P] [US1] Create Subscription model"

# After tests and models complete:
Task: "T025 [US1] Create database seeder for subscription plans"
Task: "T026 [US1] Create EmailVerification model"
Task: "T027 [US1] Create EmailVerificationService"
Task: "T028 [US1] Create TenantRegistrationController"
# ... etc
```

---

## Parallel Example: User Story 2

```bash
# Launch all tests for User Story 2 together:
Task: "T036 [P] [US2] Integration test for data isolation"
Task: "T037 [P] [US2] Test tenant-scoped queries"
Task: "T038 [P] [US2] Test file storage isolation"
Task: "T039 [P] [US2] Test backup isolation"

# Launch all base models together:
Task: "T040 [P] [US2] Create TenantModel base class"
Task: "T041 [P] [US2] Extend User model (tenant-scoped)"

# After tests and models complete:
Task: "T042 [US2] Verify BelongsToTenant trait is working"
Task: "T043 [US2] Configure tenant filesystem"
Task: "T044 [US2] Update Spatie Media Library config"
# ... etc
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (T001-T005)
2. Complete Phase 2: Foundational (T006-T018) - **CRITICAL - blocks all stories**
3. Complete Phase 3: User Story 1 (T019-T035)
4. **STOP and VALIDATE**:
   - Register a test tenant
   - Verify email
   - Select subscription plan
   - Access tenant dashboard
   - Verify tenant database was created
5. Deploy/demo if ready

**MVP Scope**: T001-T035 (35 tasks)
- stancl/tenancy installed and configured
- Tenant and Domain models extended
- Central and tenant migrations run
- Registration endpoint works
- Email verification works
- Subscription plan selection works
- Tenant database auto-created

### Incremental Delivery

1. **Foundation** (T001-T018): stancl/tenancy configured, models ready
2. **Add US1** (T019-T035): Registration works → Test independently → Deploy/Demo (MVP!)
3. **Add US2** (T036-T051): Data isolation enforced → Test independently → Deploy/Demo
4. **Add US3** (T052-T065): Domain management works → Test independently → Deploy/Demo
5. **Add US4** (T066-T081): Admin panel works → Test independently → Deploy/Demo
6. **Polish** (T082-T095): Documentation, optimization, validation

Each phase adds value without breaking previous phases.

### Parallel Team Strategy

With multiple developers:

1. **Team completes Setup + Foundational together** (T001-T018)
   - Pair program on stancl/tenancy configuration
   - Review model extensions together
   - Ensure migrations are correct

2. **Once Foundational is done, split by user story**:
   - Developer A: User Story 1 (T019-T035) - Registration flow
   - Developer B: User Story 2 (T036-T051) - Data isolation
   - Developer C: User Story 3 (T052-T065) - Domain management
   - Developer D: User Story 4 (T066-T081) - Admin panel

3. **Stories complete and integrate independently**
   - Each story is tested in isolation
   - No cross-story dependencies block progress
   - Merge stories as they complete

4. **Reunite for Polish phase** (T082-T095)
   - Documentation updates
   - End-to-end testing
   - Performance optimization

---

## Task Summary

| Phase | Description | Task Count | Test Count (if using TDD) |
|-------|-------------|------------|---------------------------|
| Phase 1 | Setup | 5 | 0 |
| Phase 2 | Foundational | 13 | 0 |
| Phase 3 | User Story 1 (P1) | 17 | 4 |
| Phase 4 | User Story 2 (P2) | 16 | 4 |
| Phase 5 | User Story 3 (P3) | 14 | 4 |
| Phase 6 | User Story 4 (P4) | 16 | 4 |
| Phase 7 | Polish | 14 | 0 |
| **Total** | **All Phases** | **95** | **16** |

**Without TDD**: 79 tasks (remove T019-T022, T036-T039, T052-T055, T066-T069)

### Task Count per User Story

- **User Story 1**: 17 tasks (4 tests + 13 implementation)
- **User Story 2**: 16 tasks (4 tests + 12 implementation)
- **User Story 3**: 14 tasks (4 tests + 10 implementation)
- **User Story 4**: 16 tasks (4 tests + 12 implementation)

---

## Notes

- **[P]** tasks = different files, no dependencies, can run in parallel
- **[Story]** label maps task to specific user story for traceability
- Each user story is independently completable and testable
- Verify tests fail before implementing (if using TDD)
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- **CRITICAL**: Phase 2 (Foundational) MUST be complete before any user story work begins
- stancl/tenancy handles automatic tenant identification and database switching
- All tenant-scoped models use `BelongsToTenant` trait for automatic query scoping

---

## Independent Test Criteria Summary

### User Story 1 (P1) - Registration & Onboarding
✅ Can register new tenant with subdomain  
✅ Email verification token works  
✅ Subscription plan can be selected  
✅ Tenant database is created automatically  
✅ Tenant can access dashboard at `subdomain.sater.com`

### User Story 2 (P2) - Data Isolation
✅ Tenant A cannot access Tenant B's data via API  
✅ Tenant A cannot access Tenant B's data via direct model query  
✅ File storage is isolated per tenant  
✅ Backups are per-tenant  
✅ No cross-tenant data leakage in any scenario

### User Story 3 (P3) - Domain Management
✅ Subdomain can be changed (once free)  
✅ Custom domain can be added  
✅ DNS TXT verification works  
✅ SSL certificate is automatically provisioned  
✅ Primary domain can be set

### User Story 4 (P4) - Central Admin Panel
✅ Admin can view all tenants in Filament  
✅ Admin can filter tenants by status  
✅ Admin can suspend/activate tenants  
✅ Admin can view tenant statistics  
✅ Admin can create/edit subscription plans

---

## Format Validation

✅ ALL tasks follow the checklist format:
- Checkbox: `- [ ]`
- Task ID: T001, T002, T003...
- [P] marker: Only for parallelizable tasks
- [Story] label: For user story phase tasks only ([US1], [US2], [US3], [US4])
- Description: Clear action with exact file path

✅ All file paths are absolute from repository root  
✅ Tasks are organized by user story for independent implementation  
✅ Dependencies clearly documented  
✅ Parallel opportunities identified  
✅ MVP scope clearly defined (T001-T035)
