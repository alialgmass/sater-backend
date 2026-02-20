# API Contracts: Multi-Tenancy Infrastructure (stancl/tenancy)

**Feature**: Multi-Tenancy Infrastructure  
**Date**: 20 فبراير 2026  
**Branch**: `001-multi-tenancy-infrastructure-with-tenant-registration-domain-management-data-isolation-and-central-admin-panel`

## Overview

This document defines the API contracts for multi-tenancy operations using stancl/tenancy. All endpoints follow RESTful conventions and return JSON responses. Authentication is required for all endpoints except registration and email verification.

**Note**: Tenant routes are automatically scoped by stancl/tenancy middleware. When a request comes to a tenant domain, stancl/tenancy identifies the tenant and switches the database connection automatically.

---

## Authentication

### Tenant Authentication
- **Method**: Bearer Token (Laravel Sanctum)
- **Header**: `Authorization: Bearer {token}`
- **Token Scope**: Tenant context automatically attached

### Admin Authentication
- **Method**: Bearer Token (Laravel Sanctum)
- **Header**: `Authorization: Bearer {token}`
- **Token Scope**: Admin permissions attached

---

## Central API Endpoints

Base URL: `/api/v1`

### Tenant Registration

#### POST /tenants/register

Register a new tenant (store).

**Request**:
```json
{
  "store_name": "Fashion House",
  "email": "owner@fashionhouse.com",
  "password": "SecurePass123!",
  "subdomain": "fashionhouse",
  "language": "ar"
}
```

**Validation Rules**:
- `store_name`: required, string, max:100
- `email`: required, email, unique:tenants
- `password`: required, min:8, mixed case + numbers
- `subdomain`: required, 3-50 chars, alphanumeric+hyphen, unique
- `language`: required, in:ar,en

**Response (201 Created)**:
```json
{
  "message": "Registration successful. Please check your email to verify your account.",
  "tenant": {
    "id": "uuid-string",
    "store_name": "Fashion House",
    "subdomain": "fashionhouse",
    "email": "owner@fashionhouse.com",
    "language": "ar",
    "status": "pending_email_verification",
    "created_at": "2026-02-20T10:00:00Z"
  }
}
```

**Response (400 Bad Request)**:
```json
{
  "message": "Validation failed",
  "errors": {
    "subdomain": ["The subdomain has already been taken."]
  }
}
```

**Response (422 Unprocessable Entity)**:
```json
{
  "message": "The subdomain 'admin' is reserved and cannot be registered.",
  "code": "RESERVED_SUBDOMAIN"
}
```

---

#### GET /tenants/verify/{token}

Verify email address using token from confirmation email.

**Path Parameters**:
- `token`: string, required (from email)

**Response (200 OK)**:
```json
{
  "message": "Email verified successfully. Please select a subscription plan.",
  "tenant": {
    "id": "uuid-string",
    "store_name": "Fashion House",
    "subdomain": "fashionhouse",
    "status": "active"
  },
  "redirect_url": "https://fashionhouse.sater.com/onboarding/plan-selection"
}
```

**Response (404 Not Found)**:
```json
{
  "message": "Invalid or expired verification token.",
  "code": "INVALID_TOKEN"
}
```

**Response (410 Gone)**:
```json
{
  "message": "This verification token has expired. Please request a new one.",
  "code": "TOKEN_EXPIRED"
}
```

---

#### POST /tenants/{tenantId}/resend-verification

Resend email verification token.

**Path Parameters**:
- `tenantId`: UUID, required

**Request**:
```json
{
  "email": "owner@fashionhouse.com"
}
```

**Response (200 OK)**:
```json
{
  "message": "Verification email sent successfully."
}
```

**Response (404 Not Found)**:
```json
{
  "message": "Tenant not found.",
  "code": "TENANT_NOT_FOUND"
}
```

---

### Subscription Plans

#### GET /subscription-plans

List available subscription plans.

**Query Parameters**:
- `billing_cycle`: optional, in:monthly,yearly (default: monthly)

**Response (200 OK)**:
```json
{
  "data": [
    {
      "id": "uuid-string",
      "name": "Starter",
      "slug": "starter",
      "description": "Perfect for small stores getting started",
      "price": {
        "monthly": 0,
        "yearly": 0,
        "currency": "SAR"
      },
      "features": {
        "products_limit": 50,
        "storage_gb": 2,
        "users_limit": 2,
        "custom_domain": false,
        "analytics": "basic",
        "support_level": "email"
      },
      "trial_days": 14,
      "is_active": true
    },
    {
      "id": "uuid-string",
      "name": "Professional",
      "slug": "professional",
      "description": "For growing businesses",
      "price": {
        "monthly": 299,
        "yearly": 2990,
        "currency": "SAR"
      },
      "features": {
        "products_limit": 1000,
        "storage_gb": 20,
        "users_limit": 10,
        "custom_domain": true,
        "analytics": "advanced",
        "support_level": "priority"
      },
      "trial_days": 14,
      "is_active": true
    }
  ]
}
```

---

#### POST /tenants/{tenantId}/subscribe

Subscribe to a subscription plan.

**Path Parameters**:
- `tenantId`: UUID, required

**Request**:
```json
{
  "plan_id": "uuid-string",
  "billing_cycle": "monthly"
}
```

**Validation Rules**:
- `plan_id`: required, exists:subscription_plans
- `billing_cycle`: required, in:monthly,yearly

**Response (200 OK)**:
```json
{
  "message": "Subscription activated successfully.",
  "subscription": {
    "id": "uuid-string",
    "tenant_id": "uuid-string",
    "plan": {
      "id": "uuid-string",
      "name": "Professional"
    },
    "status": "active",
    "billing_cycle": "monthly",
    "amount": 299,
    "currency": "SAR",
    "starts_at": "2026-02-20T10:00:00Z",
    "trial_ends_at": "2026-03-05T10:00:00Z"
  },
  "redirect_url": "https://fashionhouse.sater.com/dashboard"
}
```

**Response (404 Not Found)**:
```json
{
  "message": "Tenant or plan not found.",
  "code": "RESOURCE_NOT_FOUND"
}
```

---

### Domain Management

#### GET /tenants/{tenantId}/domains

List domains for a tenant.

**Authentication**: Required (tenant admin)

**Response (200 OK)**:
```json
{
  "data": [
    {
      "id": "uuid-string",
      "name": "fashionhouse.sater.com",
      "type": "subdomain",
      "is_primary": true,
      "verified": true,
      "verified_at": "2026-02-20T10:00:00Z",
      "ssl_status": "active",
      "ssl_expires_at": "2026-05-20T10:00:00Z",
      "created_at": "2026-02-20T10:00:00Z"
    },
    {
      "id": "uuid-string",
      "name": "www.fashionhouse.com",
      "type": "custom",
      "is_primary": false,
      "verified": false,
      "verified_at": null,
      "ssl_status": "pending",
      "ssl_expires_at": null,
      "verification_instructions": {
        "type": "TXT",
        "name": "_sater-verification.fashionhouse.com",
        "value": "sater-verify=abc123xyz456"
      },
      "created_at": "2026-02-20T10:00:00Z"
    }
  ]
}
```

---

#### POST /tenants/{tenantId}/domains

Add a new domain to tenant.

**Authentication**: Required (tenant admin)

**Request**:
```json
{
  "name": "www.fashionhouse.com",
  "type": "custom",
  "is_primary": false
}
```

**Validation Rules**:
- `name`: required, valid domain format, unique
- `type`: required, in:subdomain,custom
- `is_primary`: boolean (only one primary per tenant)

**Response (201 Created)**:
```json
{
  "message": "Domain added successfully. Please verify ownership.",
  "domain": {
    "id": "uuid-string",
    "name": "www.fashionhouse.com",
    "type": "custom",
    "verified": false,
    "verification_instructions": {
      "type": "TXT",
      "name": "_sater-verification.fashionhouse.com",
      "value": "sater-verify=abc123xyz456"
    }
  }
}
```

**Response (409 Conflict)**:
```json
{
  "message": "This domain is already registered to another tenant.",
  "code": "DOMAIN_EXISTS"
}
```

---

#### PUT /tenants/{tenantId}/domains/{domainId}/verify

Verify domain ownership via DNS check.

**Authentication**: Required (tenant admin)

**Response (200 OK)**:
```json
{
  "message": "Domain verified successfully. SSL certificate is being provisioned.",
  "domain": {
    "id": "uuid-string",
    "name": "www.fashionhouse.com",
    "verified": true,
    "verified_at": "2026-02-20T10:30:00Z",
    "ssl_status": "pending"
  }
}
```

**Response (400 Bad Request)**:
```json
{
  "message": "Domain verification failed. DNS record not found.",
  "code": "VERIFICATION_FAILED",
  "hint": "Ensure you've added the TXT record and wait up to 5 minutes for DNS propagation."
}
```

---

#### PUT /tenants/{tenantId}/domains/{domainId}/primary

Set domain as primary.

**Authentication**: Required (tenant admin)

**Response (200 OK)**:
```json
{
  "message": "Primary domain updated successfully.",
  "domain": {
    "id": "uuid-string",
    "name": "www.fashionhouse.com",
    "is_primary": true
  }
}
```

---

#### DELETE /tenants/{tenantId}/domains/{domainId}

Remove a domain.

**Authentication**: Required (tenant admin)

**Constraints**:
- Cannot delete primary domain
- Subdomain cannot be deleted (only changed via subdomain change endpoint)

**Response (200 OK)**:
```json
{
  "message": "Domain removed successfully."
}
```

**Response (400 Bad Request)**:
```json
{
  "message": "Cannot delete primary domain. Set another domain as primary first.",
  "code": "CANNOT_DELETE_PRIMARY"
}
```

---

#### PUT /tenants/{tenantId}/subdomain

Change tenant subdomain (once free).

**Authentication**: Required (tenant admin)

**Request**:
```json
{
  "subdomain": "newfashionhouse"
}
```

**Validation Rules**:
- `subdomain`: required, 3-50 chars, alphanumeric+hyphen, unique, not reserved

**Response (200 OK)**:
```json
{
  "message": "Subdomain changed successfully. Your store is now accessible at newfashionhouse.sater.com.",
  "tenant": {
    "subdomain": "newfashionhouse",
    "subdomain_changed_at": "2026-02-20T10:00:00Z"
  },
  "old_url": "https://fashionhouse.sater.com",
  "new_url": "https://newfashionhouse.sater.com"
}
```

**Response (400 Bad Request)**:
```json
{
  "message": "You have already used your free subdomain change.",
  "code": "SUBDOMAIN_CHANGE_LIMIT_EXCEEDED"
}
```

**Response (409 Conflict)**:
```json
{
  "message": "This subdomain is already taken.",
  "code": "SUBDOMAIN_EXISTS"
}
```

---

### Tenant Admin Panel (Central)

#### GET /admin/tenants

List all tenants (platform admin only).

**Authentication**: Required (platform admin)

**Query Parameters**:
- `status`: optional, in:pending_email_verification,active,suspended,cancelled,deleted
- `search`: optional, string (search by store_name, email, subdomain)
- `plan_id`: optional, UUID filter
- `sort`: optional, in:created_at,store_name,status (default: created_at)
- `order`: optional, in:asc,desc (default: desc)
- `per_page`: optional, integer 1-100 (default: 20)

**Response (200 OK)**:
```json
{
  "data": [
    {
      "id": "uuid-string",
      "store_name": "Fashion House",
      "subdomain": "fashionhouse",
      "email": "owner@fashionhouse.com",
      "status": "active",
      "current_plan": {
        "id": "uuid-string",
        "name": "Professional"
      },
      "stats": {
        "products_count": 45,
        "orders_count": 128,
        "revenue_total": 15420.50
      },
      "created_at": "2026-02-20T10:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 95
  }
}
```

---

#### GET /admin/tenants/{tenantId}

Get tenant details (platform admin only).

**Authentication**: Required (platform admin)

**Response (200 OK)**:
```json
{
  "id": "uuid-string",
  "store_name": "Fashion House",
  "subdomain": "fashionhouse",
  "email": "owner@fashionhouse.com",
  "language": "ar",
  "status": "active",
  "current_plan": {
    "id": "uuid-string",
    "name": "Professional",
    "slug": "professional"
  },
  "subscription": {
    "id": "uuid-string",
    "status": "active",
    "billing_cycle": "monthly",
    "starts_at": "2026-02-20T10:00:00Z",
    "ends_at": "2026-03-20T10:00:00Z"
  },
  "domains": [
    {
      "id": "uuid-string",
      "name": "fashionhouse.sater.com",
      "type": "subdomain",
      "is_primary": true,
      "verified": true,
      "ssl_status": "active"
    }
  ],
  "stats": {
    "products_count": 45,
    "orders_count": 128,
    "revenue_total": 15420.50,
    "storage_used_gb": 3.2
  },
  "created_at": "2026-02-20T10:00:00Z",
  "updated_at": "2026-02-20T10:00:00Z"
}
```

---

#### PUT /admin/tenants/{tenantId}/status

Update tenant status (suspend/activate).

**Authentication**: Required (platform admin)

**Request**:
```json
{
  "status": "suspended",
  "reason": "Violation of terms of service"
}
```

**Validation Rules**:
- `status`: required, in:active,suspended,cancelled
- `reason`: required if status is suspended or cancelled

**Response (200 OK)**:
```json
{
  "message": "Tenant status updated successfully.",
  "tenant": {
    "id": "uuid-string",
    "status": "suspended",
    "suspended_at": "2026-02-20T10:00:00Z",
    "suspension_reason": "Violation of terms of service"
  }
}
```

---

#### GET /admin/subscription-plans

List all subscription plans (platform admin only).

**Authentication**: Required (platform admin)

**Response (200 OK)**:
```json
{
  "data": [
    {
      "id": "uuid-string",
      "name": "Starter",
      "slug": "starter",
      "price_monthly": 0,
      "price_yearly": 0,
      "features": {
        "products_limit": 50,
        "storage_gb": 2
      },
      "is_active": true,
      "active_subscriptions_count": 45
    }
  ]
}
```

---

#### POST /admin/subscription-plans

Create a new subscription plan (platform admin only).

**Authentication**: Required (platform admin)

**Request**:
```json
{
  "name": "Enterprise",
  "slug": "enterprise",
  "description": "For large-scale operations",
  "price_monthly": 999,
  "price_yearly": 9990,
  "features": {
    "products_limit": -1,
    "storage_gb": 100,
    "users_limit": -1,
    "custom_domain": true,
    "analytics": "advanced",
    "support_level": "dedicated"
  },
  "trial_days": 30,
  "is_active": true
}
```

**Response (201 Created)**:
```json
{
  "message": "Subscription plan created successfully.",
  "plan": {
    "id": "uuid-string",
    "name": "Enterprise",
    "slug": "enterprise"
  }
}
```

---

#### PUT /admin/subscription-plans/{planId}

Update subscription plan (platform admin only).

**Authentication**: Required (platform admin)

**Request**:
```json
{
  "price_monthly": 899,
  "price_yearly": 8990
}
```

**Response (200 OK)**:
```json
{
  "message": "Subscription plan updated successfully.",
  "plan": {
    "id": "uuid-string",
    "price_monthly": 899,
    "price_yearly": 8990
  }
}
```

---

## Error Responses

### Standard Error Format

```json
{
  "message": "Human-readable error message",
  "code": "ERROR_CODE",
  "details": {}
}
```

### Common Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `VALIDATION_ERROR` | 422 | Request validation failed |
| `UNAUTHENTICATED` | 401 | Missing or invalid authentication |
| `FORBIDDEN` | 403 | Insufficient permissions |
| `NOT_FOUND` | 404 | Resource not found |
| `CONFLICT` | 409 | Resource conflict (duplicate) |
| `TENANT_NOT_FOUND` | 404 | Tenant does not exist |
| `DOMAIN_EXISTS` | 409 | Domain already registered |
| `SUBDOMAIN_EXISTS` | 409 | Subdomain already taken |
| `RESERVED_SUBDOMAIN` | 422 | Subdomain is reserved |
| `INVALID_TOKEN` | 404 | Verification token invalid |
| `TOKEN_EXPIRED` | 410 | Verification token expired |
| `TENANT_SUSPENDED` | 403 | Tenant account suspended |
| `TENANT_INACTIVE` | 403 | Tenant account not active |

---

## Rate Limiting

| Endpoint Type | Limit | Window |
|---------------|-------|--------|
| Registration | 5 requests | per minute (per IP) |
| Authentication | 10 requests | per minute (per IP) |
| Tenant API | 100 requests | per minute (per tenant) |
| Admin API | 200 requests | per minute (per admin) |
| Domain verification | 10 requests | per hour (per domain) |

**Rate Limit Headers**:
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1645351200
```

**Response (429 Too Many Requests)**:
```json
{
  "message": "Too many requests. Please try again in 60 seconds.",
  "retry_after": 60
}
```

---

## Versioning

- API version: `v1` (in URL path: `/api/v1/`)
- Backward compatibility maintained within major version
- Breaking changes require new major version

---

## References

- Specification: [spec.md](./spec.md)
- Data Model: [data-model.md](./data-model.md)
- Research: [research.md](./research.md)
