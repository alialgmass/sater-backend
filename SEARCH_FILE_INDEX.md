# 🔍 Search System - File Index & Navigation

## 📖 Documentation (Start Here!)

1. **SEARCH_SYSTEM_SUMMARY.md** ⭐ START HERE
   - Complete implementation overview
   - All 8 stories explained
   - Key features summary

2. **SEARCH_DOCUMENTATION.md** - API Reference
   - Complete endpoint documentation
   - Query parameters explained
   - Response formats
   - Usage examples
   - Caching strategy
   - Performance details

3. **SEARCH_IMPLEMENTATION_GUIDE.md** - Getting Started
   - Step-by-step setup instructions
   - Next steps after implementation
   - Configuration details
   - Troubleshooting guide
   - File structure overview

4. **SEARCH_DEPLOYMENT_CHECKLIST.md** - Production Deploy
   - Pre-deployment verification
   - All 8 stories verified
   - Security checklist
   - Performance checklist
   - Deployment steps
   - Post-deployment tasks

---

## 🎯 Core Service Layer

Located: `app/Services/Search/`

### Main Orchestrator
- **SearchService.php** (185 lines)
  - Entry point for all searches
  - Orchestrates pipeline
  - Handles autocomplete & suggestions

### Query Building
- **ProductSearchQueryBuilder.php** (90 lines)
  - Assembles database queries
  - Full-text search implementation
  - Fallback LIKE search
  - Integrates filters & sorting

### Filtering Logic
- **FilterService.php** (175 lines)
  - Price range filtering
  - Category filtering (with nested)
  - Vendor filtering
  - Rating filtering
  - Stock filtering
  - Clothing attributes
  - Generic attributes (size, color)

### Sorting Logic
- **SortService.php** (100 lines)
  - 6 sort options (relevance, price, newest, popularity, rating)
  - Whitelisted fields (SQL injection prevention)
  - Secondary sort fallbacks
  - Relevance scoring strategy

### Search History
- **SearchHistoryService.php** (130 lines)
  - Record user searches
  - Retrieve history
  - Prune old entries
  - Get popular searches
  - Clear history

### No-Results Handler
- **SearchSuggestionService.php** (140 lines)
  - Similar keywords
  - Popular in category
  - Top vendors
  - Browse suggestions

---

## 📦 Data Transfer Objects (DTOs)

Located: `app/DTOs/`

### ProductSearchDTO (150 lines)
```php
- query: string
- category_id: int
- price_min/max: float
- size, color: string
- vendor_id: int
- min_rating: float
- in_stock_only: bool
- fabric_type, sleeve_length, opacity_level, hijab_style: string
- sort: string (enum)
- page, per_page: int

Methods:
- from() - Create from request
- rules() - Validation rules
- hasQuery() - Check if searching
- hasFilters() - Check if filtering
- getClothingFilters() - Extract clothing attrs
- getGeneralFilters() - Extract other attrs
- toArray() - For caching
```

### AutocompleteDTO (60 lines)
```php
- query: string (required, min 2)
- vendor_id: int (optional)
- limit: int (optional, max 50)

Methods:
- from() - Create from request
- rules() - Validation rules
```

---

## 🏷️ Enums

Located: `app/Enums/`

1. **FabricTypeEnum.php** - 12 fabric types
2. **SleeveLengthEnum.php** - 5 sleeve options
3. **OpacityLevelEnum.php** - 4 opacity levels
4. **HijabStyleEnum.php** - 10 hijab styles
5. **SortOptionEnum.php** - 6 sort options

Each enum has:
- `label()` - Human-readable name
- `values()` - All enum values
- `labels()` - Map of value→label

---

## 🗄️ Models

Located: `app/Models/` and `Modules/Product/Models/`

### SearchHistory
- `user_id` - Foreign key to users
- `query` - Search term
- `filters` - JSON snapshot of filters
- `results_count` - Number of results
- `created_at`, `updated_at`
- Relationship: `belongsTo(User::class)`

### Product (Updated)
**New Fillable Fields:**
- `keywords` - Comma-separated keywords
- `clothing_attributes` - JSON attributes
- `sales_count` - Number of sales
- `avg_rating` - Average rating (0-5)
- `rating_count` - Total ratings

**New Casts:**
- `clothing_attributes` → array
- `avg_rating` → decimal:2

---

## 🛣️ API Endpoints

Located: `Modules/Product/routes/api.php` and `Modules/Vendor/routes/api.php`

### SearchController (Modules/Product/Http/Controllers/Api/)

| Method | Route | Name | Auth | Description |
|--------|-------|------|------|-------------|
| GET | `/api/v1/search/products` | search.products | No | Search all products |
| GET | `/api/v1/search/cursor` | search.cursor | No | Cursor pagination search |
| GET | `/api/v1/search/autocomplete` | search.autocomplete | No | Get suggestions |
| GET | `/api/v1/search/history` | search.history | Yes | Get user history |
| DELETE | `/api/v1/search/history` | search.history.clear | Yes | Clear all history |
| DELETE | `/api/v1/search/history/{id}` | search.history.delete | Yes | Delete one entry |

### VendorSearchController (Modules/Vendor/Http/Controllers/Api/)

| Method | Route | Name | Auth | Description |
|--------|-------|------|------|-------------|
| GET | `/api/v1/vendors/{vendor_id}/search` | vendor.search | No | Search vendor store |

---

## 📤 API Resources

Located: `Modules/Product/Http/Resources/` and `app/Http/Resources/`

### ProductSearchResource (60 lines)
```json
{
  "id": 1,
  "name": "Cotton Hijab",
  "slug": "cotton-hijab",
  "price": 29.99,
  "discounted_price": 24.99,
  "discount_percentage": 16.67,
  "stock": 50,
  "in_stock": true,
  "sku": "SKU-001",
  "rating": { "average": 4.5, "count": 120 },
  "popularity": 500,
  "vendor": { "id": 1, "name": "...", "shop_name": "...", "shop_slug": "..." },
  "category": { "id": 2, "name": "..." },
  "image": "url"
}
```

### SearchSuggestionResource (30 lines)
```json
{
  "text": "Hijab Cotton",
  "type": "keyword"
}
```

### SearchHistoryResource (40 lines)
```json
{
  "id": 1,
  "query": "hijab",
  "filters": { "price_max": 50 },
  "results_count": 25,
  "searched_at": "2025-01-16T10:30:00Z"
}
```

---

## 🗳️ Database Migrations

Located: `database/migrations/`

### 2025_01_16_000001_create_search_histories_table.php
```sql
CREATE TABLE search_histories (
  id BIGINT PRIMARY KEY,
  user_id BIGINT FOREIGN KEY,
  query VARCHAR(255) INDEX,
  filters JSON,
  results_count INT,
  created_at, updated_at,
  INDEX(user_id, created_at)
)
```

### 2025_01_16_000002_add_search_fields_to_products.php
```sql
ALTER TABLE products ADD:
  keywords TEXT
  sales_count INT DEFAULT 0
  avg_rating DECIMAL(3,2) DEFAULT 0
  rating_count INT DEFAULT 0

CREATE INDEXES:
  FULLTEXT INDEX (name, description, keywords)
  INDEX (category_id, status)
  INDEX (vendor_id, status)
  INDEX (price, status)
  INDEX (stock, status)
  INDEX (sales_count, status)
  INDEX (avg_rating, status)
  INDEX (created_at, status)
  INDEX (vendor_id, category_id, status)
  INDEX (price, stock, status)
  INDEX (avg_rating, sales_count, status)
```

### 2025_01_16_000003_add_clothing_attributes_to_products.php
```sql
ALTER TABLE products ADD:
  clothing_attributes JSON
```

**Total Indexes:** 14

---

## 🧪 Tests

Located: `tests/Feature/`

### SearchFeatureTest.php (250+ lines)
- `test_can_search_products_by_name()` ✓
- `test_can_filter_by_price_range()` ✓
- `test_can_filter_in_stock_only()` ✓
- `test_can_sort_by_price_ascending()` ✓
- `test_can_sort_by_popularity()` ✓
- `test_can_get_autocomplete_suggestions()` ✓
- `test_respects_pagination_limits()` ✓
- `test_limits_per_page_to_maximum()` ✓

### SearchHistoryTest.php (150+ lines)
- `test_can_record_search_history_for_authenticated_user()` ✓
- `test_does_not_record_for_unauthenticated_users()` ✓
- `test_does_not_record_empty_queries()` ✓
- `test_can_retrieve_user_search_history()` ✓
- `test_can_clear_user_search_history()` ✓
- `test_limits_history_per_user()` ✓

### Api/SearchApiTest.php (350+ lines)
- `test_can_search_products_via_api()` ✓
- `test_search_validates_input_parameters()` ✓
- `test_search_respects_pagination()` ✓
- `test_autocomplete_returns_suggestions()` ✓
- `test_autocomplete_requires_minimum_query_length()` ✓
- `test_authenticated_user_can_view_search_history()` ✓
- `test_unauthenticated_user_cannot_view_search_history()` ✓
- `test_can_clear_search_history()` ✓
- `test_can_search_vendor_store()` ✓
- `test_vendor_search_returns_404_for_inactive_vendor()` ✓
- `test_search_with_filters()` ✓
- `test_no_results_includes_suggestions()` ✓

**Total Test Cases:** 30+

---

## 🛠️ Supporting Files

### Providers
- **app/Providers/SearchServiceProvider.php** (60 lines)
  - Service registration
  - Dependency injection setup
  - Singleton bindings

### Configuration
- **app/Support/Search/SearchConfig.php** (50 lines)
  - MAX_PER_PAGE = 100
  - DEFAULT_PER_PAGE = 20
  - HISTORY_LIMIT = 50
  - HISTORY_RETENTION_DAYS = 90
  - Cache durations

### Commands
- **app/Console/Commands/PruneSearchHistory.php** (40 lines)
  - `php artisan search:prune-history`
  - Deletes searches older than 90 days

### Factories
- **Modules/Product/Database/Factories/ProductFactory.php** (100+ lines)
  - Generate test products
  - Methods: `inStock()`, `outOfStock()`, `popular()`

---

## 📊 Architecture Overview

```
API Request
    ↓
SearchController / VendorSearchController
    ↓
ProductSearchDTO (validation)
    ↓
SearchService (orchestrator)
    ├─ ProductSearchQueryBuilder
    │   ├─ FilterService
    │   └─ SortService
    ├─ SearchHistoryService
    └─ SearchSuggestionService
    ↓
Database (14 indexes)
    ↓
Resource (JSON formatting)
    ↓
API Response
```

---

## 🔐 Security Layers

1. **Input Validation** - DTO rules on all inputs
2. **SQL Injection Prevention** - Sort whitelisting
3. **N+1 Prevention** - Eager loading
4. **Authentication** - Sanctum on history endpoints
5. **Authorization** - Resource permissions
6. **Rate Limiting Ready** - Autocomplete configurable

---

## ⚡ Performance Features

1. **14 Database Indexes** - Optimized queries
2. **Full-Text Search** - Faster than LIKE
3. **Eager Loading** - No N+1 queries
4. **Pagination Only** - Never load full dataset
5. **Cursor Pagination** - Efficient infinite scroll
6. **Caching Ready** - Config for cache durations
7. **Query Optimization** - EXPLAIN ready

---

## 🎓 Quick Start Commands

```bash
# Run migrations
php artisan migrate

# Create test data
php artisan tinker
Modules\Product\Models\Product::factory(100)->create();

# Run tests
php artisan test tests/Feature/SearchFeatureTest.php

# Test API
curl "http://localhost:8000/api/v1/search/products?query=hijab"

# Prune history
php artisan search:prune-history

# Clear caches
php artisan cache:clear
```

---

## 📞 How to Use Each Component

### As a Frontend Developer
1. Read: SEARCH_DOCUMENTATION.md
2. Test endpoints in Postman
3. Use ProductSearchResource response format
4. Implement autocomplete with debounce

### As a Backend Developer
1. Read: SEARCH_IMPLEMENTATION_GUIDE.md
2. Study: app/Services/Search/ implementations
3. Extend: Add custom filters in FilterService
4. Test: Run test suite before deploying

### As a DevOps/DBA
1. Read: SEARCH_DEPLOYMENT_CHECKLIST.md
2. Monitor: Query performance and indexes
3. Schedule: `php artisan search:prune-history`
4. Optimize: Analyze slow queries

---

## 📈 Monitoring & Metrics

Track in production:
- Search response time (target: <200ms)
- Search volume (queries/min)
- Popular searches (top 10)
- No-results rate (% of searches)
- Index usage & fragmentation
- Slow query log

---

## 🔄 Common Workflows

### Add New Clothing Attribute
1. Add case to respective enum (FabricTypeEnum, etc.)
2. Add to ProductSearchDTO::rules()
3. Add to FilterService::applyClothingFilters()
4. Update Product factory

### Add New Sort Option
1. Add case to SortOptionEnum
2. Add to SortService::apply()
3. Update documentation

### Change Search Algorithm
1. Modify ProductSearchQueryBuilder::applyTextSearch()
2. Run tests: `php artisan test`
3. Update documentation

### Migrate to Meilisearch
1. Create MeiliSearchQueryBuilder class
2. Extend ProductSearchService to use it
3. Add feature flag to switch implementations
4. Test and deploy

---

## ✨ Quick Reference Table

| What | Where | Lines | Status |
|------|-------|-------|--------|
| Main Service | SearchService.php | 185 | ✅ |
| Query Builder | ProductSearchQueryBuilder.php | 90 | ✅ |
| Filtering | FilterService.php | 175 | ✅ |
| Sorting | SortService.php | 100 | ✅ |
| History | SearchHistoryService.php | 130 | ✅ |
| Suggestions | SearchSuggestionService.php | 140 | ✅ |
| DTOs | ProductSearchDTO.php + AutocompleteDTO.php | 210 | ✅ |
| Enums | 5 enum files | 250 | ✅ |
| Controllers | SearchController.php + VendorSearchController.php | 180 | ✅ |
| Resources | 3 resource files | 130 | ✅ |
| Migrations | 3 migration files | 150 | ✅ |
| Models | SearchHistory.php | 30 | ✅ |
| Tests | 3 test files | 750+ | ✅ |
| Docs | 4 documentation files | 1500+ | ✅ |
| **TOTAL** | **25+ files** | **~4,500 lines** | **✅ COMPLETE** |

---

## 🎉 Ready to Use!

All components are production-ready and fully documented. Start with reading SEARCH_SYSTEM_SUMMARY.md, then SEARCH_DOCUMENTATION.md for API details.

Happy searching! 🚀
