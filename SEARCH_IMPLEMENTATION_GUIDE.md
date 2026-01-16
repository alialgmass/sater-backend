# Search System Implementation Guide

## Quick Start

### 1. Run Migrations

```bash
php artisan migrate
```

This creates:
- `search_histories` table
- Adds search fields to `products` table
- Adds clothing attributes to `products` table
- Creates all necessary indexes

### 2. Register Service Provider

Already added to `bootstrap/providers.php`. Verify by checking:

```php
App\Providers\SearchServiceProvider::class,
```

### 3. Test the API

```bash
# Search products
curl "http://localhost:8000/api/v1/search/products?query=hijab"

# Autocomplete
curl "http://localhost:8000/api/v1/search/autocomplete?query=hij"

# Vendor search
curl "http://localhost:8000/api/v1/vendors/1/search?query=abaya"
```

## Implementation Checklist

### ✅ Core Components Created

- [x] Enums (FabricTypeEnum, SleeveLengthEnum, OpacityLevelEnum, HijabStyleEnum, SortOptionEnum)
- [x] DTOs (ProductSearchDTO, AutocompleteDTO)
- [x] Service Layer
  - [x] SearchService (main orchestrator)
  - [x] ProductSearchQueryBuilder
  - [x] FilterService
  - [x] SortService
  - [x] SearchHistoryService
  - [x] SearchSuggestionService
- [x] Service Provider (SearchServiceProvider)
- [x] Models
  - [x] SearchHistory model
  - [x] Product model (updated with new fields)
- [x] API Resources
  - [x] ProductSearchResource
  - [x] SearchSuggestionResource
  - [x] SearchHistoryResource
- [x] Controllers
  - [x] SearchController (global search)
  - [x] VendorSearchController
- [x] Routes
  - [x] Product module routes
  - [x] Vendor module routes
- [x] Migrations (3 total)
- [x] Tests
  - [x] Feature tests
  - [x] API tests
  - [x] History tests
- [x] Console Commands
  - [x] PruneSearchHistory command
- [x] Documentation

### 📋 Next Steps

1. **Seed test data:**
   ```bash
   php artisan tinker
   
   # Create vendors
   Modules\Vendor\Models\Vendor::factory(5)->create();
   
   # Create categories
   Modules\Category\Models\Category::factory(10)->create();
   
   # Create products with search fields
   Modules\Product\Models\Product::factory(100)->create();
   ```

2. **Run tests:**
   ```bash
   php artisan test tests/Feature/SearchFeatureTest.php
   php artisan test tests/Feature/Api/SearchApiTest.php
   php artisan test tests/Feature/SearchHistoryTest.php
   ```

3. **Update Product Filament form** to include new fields:
   - keywords
   - clothing_attributes
   - sales_count (read-only)
   - avg_rating (read-only)
   - rating_count (read-only)

4. **Schedule search history pruning:**
   ```php
   // app/Console/Kernel.php
   protected function schedule(Schedule $schedule)
   {
       $schedule->command('search:prune-history')->daily();
   }
   ```

5. **Add environment configuration** (optional):
   ```env
   SEARCH_CACHE_DURATION=60
   SEARCH_AUTOCOMPLETE_LIMIT=10
   SEARCH_RESULTS_PER_PAGE=20
   ```

## API Endpoints Summary

### Public Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/search/products` | Search all products |
| GET | `/api/v1/search/cursor` | Cursor-based search (infinite scroll) |
| GET | `/api/v1/search/autocomplete` | Get suggestions |
| GET | `/api/v1/vendors/{id}/search` | Search vendor store |

### Authenticated Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/search/history` | Get user's search history |
| DELETE | `/api/v1/search/history` | Clear all history |
| DELETE | `/api/v1/search/history/{id}` | Delete single entry |

## Usage Examples

### Basic Search

```bash
curl "http://localhost:8000/api/v1/search/products?query=hijab&sort=popularity"
```

### Advanced Search with Filters

```bash
curl "http://localhost:8000/api/v1/search/products\
  ?query=hijab\
  &category_id=2\
  &price_min=10\
  &price_max=50\
  &fabric_type=cotton\
  &sort=price_asc\
  &per_page=20"
```

### Vendor Store Search

```bash
curl "http://localhost:8000/api/v1/vendors/5/search?query=abaya&sort=newest"
```

### Autocomplete

```bash
curl "http://localhost:8000/api/v1/search/autocomplete\
  ?query=hij\
  &limit=10"
```

### Search History (Authenticated)

```bash
# Get history
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/v1/search/history?limit=20"

# Clear history
curl -X DELETE \
  -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/v1/search/history"
```

## Configuration

### SearchConfig (app/Support/Search/SearchConfig.php)

Customize these constants:

```php
const MAX_PER_PAGE = 100;
const DEFAULT_PER_PAGE = 20;
const HISTORY_LIMIT = 50;
const HISTORY_RETENTION_DAYS = 90;
const AUTOCOMPLETE_CACHE_MINUTES = 60;
const POPULAR_SEARCHES_CACHE_MINUTES = 1440;
```

## Performance Tuning

### Database Indexes

All necessary indexes are created by migrations. To verify:

```bash
php artisan tinker
DB::select('SHOW INDEX FROM products')
DB::select('SHOW INDEX FROM search_histories')
```

### Query Optimization

The system uses:
- **Full-text search** on name, description, keywords
- **Composite indexes** for common filter combinations
- **Eager loading** to prevent N+1 queries
- **Cursor pagination** for efficient offset-less traversal

### Caching Strategy

- Autocomplete results: 60 minutes
- Popular searches: 24 hours
- Search history: Real-time (not cached)

## Future Integration Points

### Scout + Meilisearch

When ready to migrate to full-text search engine:

1. Install Scout and Meilisearch
2. Replace `ProductSearchQueryBuilder::applyTextSearch()` with Scout calls
3. Update filters to use Meilisearch filters API
4. Use facets for better filtering UI

### Custom Search Engines

1. Create new QueryBuilder class implementing same interface
2. Update ProductSearchService to use new builder
3. Add feature flags to switch between implementations

## Troubleshooting

### No search results

1. Verify full-text index exists:
   ```sql
   SHOW INDEX FROM products WHERE Key_name = 'idx_fulltext';
   ```

2. Check if products have `status = 'active'`

3. Try fallback LIKE search:
   ```php
   $results = app(SearchService::class)->getAutocompleteFallback('query');
   ```

### Search is slow

1. Check index usage:
   ```sql
   EXPLAIN SELECT * FROM products WHERE MATCH(name) AGAINST('+term*' IN BOOLEAN MODE);
   ```

2. Verify indexes are built:
   ```bash
   php artisan migrate
   ```

3. Monitor slow query log:
   ```sql
   SET GLOBAL slow_query_log = 'ON';
   ```

### History not recording

1. Verify authentication:
   ```php
   auth()->check() // Should return true
   ```

2. Check table exists:
   ```bash
   php artisan migrate
   ```

3. Verify service is registered:
   ```php
   app(SearchHistoryService::class) // Should work
   ```

## File Structure

```
app/
├── DTOs/
│   ├── ProductSearchDTO.php
│   └── AutocompleteDTO.php
├── Enums/
│   ├── FabricTypeEnum.php
│   ├── SleeveLengthEnum.php
│   ├── OpacityLevelEnum.php
│   ├── HijabStyleEnum.php
│   └── SortOptionEnum.php
├── Models/
│   └── SearchHistory.php
├── Providers/
│   └── SearchServiceProvider.php
├── Services/Search/
│   ├── SearchService.php
│   ├── ProductSearchQueryBuilder.php
│   ├── FilterService.php
│   ├── SortService.php
│   ├── SearchHistoryService.php
│   └── SearchSuggestionService.php
├── Support/Search/
│   └── SearchConfig.php
├── Console/Commands/
│   └── PruneSearchHistory.php
└── Http/Resources/
    └── SearchHistoryResource.php

Modules/Product/Http/
├── Controllers/Api/
│   └── SearchController.php
└── Resources/
    ├── ProductSearchResource.php
    └── SearchSuggestionResource.php

Modules/Vendor/Http/Controllers/Api/
└── VendorSearchController.php

database/migrations/
├── 2025_01_16_000001_create_search_histories_table.php
├── 2025_01_16_000002_add_search_fields_to_products.php
└── 2025_01_16_000003_add_clothing_attributes_to_products.php

tests/Feature/
├── SearchFeatureTest.php
├── SearchHistoryTest.php
└── Api/SearchApiTest.php
```

## Support

For issues or questions:
1. Check SEARCH_DOCUMENTATION.md for detailed API docs
2. Review service implementations in app/Services/Search/
3. Check test files for usage examples
4. Review migrations for database schema
