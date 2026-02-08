# Search, Filtering & Sorting System Documentation

## Overview

This is a production-grade, API-first search system for the Sater multi-vendor marketplace. It's designed to be:

- **API-first**: All functionality exposed through RESTful endpoints
- **High-performance**: Optimized with database indexes and caching
- **Extensible**: Easy migration to Scout, Meilisearch, or Elasticsearch
- **Clean Architecture**: Separation of concerns with services, DTOs, and resources

## Architecture

### Core Components

```
SearchService (main orchestrator)
├── ProductSearchQueryBuilder (query assembly)
├── FilterService (applies filters)
├── SortService (applies sorting)
├── SearchHistoryService (user search history)
└── SearchSuggestionService (no-results handling)
```

### Data Flow

1. **Request** → ValidationLayer (DTO)
2. **DTO** → SearchService
3. **SearchService** → ProductSearchQueryBuilder
4. **Query Builder** → FilterService → SortService
5. **Database** → Results
6. **Resource** → JSON Response

## API Endpoints

### 1. Product Search

```
GET /api/v1/search/products
```

**Query Parameters:**
```
query              string   optional   Search term
category_id        integer  optional   Filter by category ID
price_min          float    optional   Minimum price
price_max          float    optional   Maximum price
size               string   optional   Product size
color              string   optional   Product color
vendor_id          integer  optional   Filter by vendor
min_rating         float    optional   Minimum rating (0-5)
in_stock_only      boolean  optional   Only in-stock products
fabric_type        string   optional   Cotton, silk, etc.
sleeve_length      string   optional   Sleeveless, half, 3/4, full
opacity_level      string   optional   Transparent to opaque
hijab_style        string   optional   Hijab style
sort               string   optional   relevance (default), price_asc, price_desc, newest, popularity, rating
page               integer  optional   Page number (default: 1)
per_page           integer  optional   Results per page (default: 20, max: 100)
```

**Response:**
```json
{
  "products": [
    {
      "id": 1,
      "name": "Hijab Cotton Blend",
      "slug": "hijab-cotton-blend",
      "price": 29.99,
      "discounted_price": 24.99,
      "discount_percentage": 16.67,
      "stock": 50,
      "in_stock": true,
      "sku": "SKU-001",
      "rating": {
        "average": 4.5,
        "count": 120
      },
      "popularity": 500,
      "vendor": {
        "id": 1,
        "name": "Islamic Fashion",
        "shop_name": "Islamic Fashion Store",
        "shop_slug": "islamic-fashion"
      },
      "category": {
        "id": 2,
        "name": "Hijabs"
      },
      "image": "https://example.com/images/hijab.jpg"
    }
  ],
  "pagination": {
    "total": 150,
    "per_page": 20,
    "current_page": 1,
    "last_page": 8
  }
}
```

### 2. Autocomplete Suggestions

```
GET /api/v1/search/autocomplete
```

**Query Parameters:**
```
query       string   required   Search term (min 2 chars)
vendor_id   integer  optional   Limit to vendor products
limit       integer  optional   Max suggestions (default: 10, max: 50)
```

**Response:**
```json
{
  "suggestions": [
    { "text": "Hijab Cotton Blend", "type": "keyword" },
    { "text": "Hijab Silk", "type": "keyword" },
    { "text": "Hijab Long", "type": "keyword" }
  ]
}
```

### 3. Cursor Pagination Search

```
GET /api/v1/search/cursor
```

Useful for infinite scroll. Same parameters as `/products` but returns cursor tokens.

**Response:**
```json
{
  "products": [...],
  "next_cursor": "eyJpZCI6IDEsICJfcG9pbnRzVG8iOiAibmV4dCJ9",
  "prev_cursor": null
}
```

### 4. Search History (Authenticated)

```
GET /api/v1/search/history
```

**Query Parameters:**
```
limit   integer   optional   Max history entries (default: 20, max: 100)
```

**Response:**
```json
{
  "history": [
    {
      "id": 1,
      "query": "hijab",
      "filters": {
        "price_max": 50,
        "in_stock_only": true
      },
      "results_count": 25,
      "searched_at": "2025-01-16T10:30:00Z"
    }
  ]
}
```

### 5. Clear Search History

```
DELETE /api/v1/search/history
```

### 6. Delete Single History Entry

```
DELETE /api/v1/search/history/{id}
```

### 7. Vendor Store Search

```
GET /api/v1/vendors/{vendor_id}/search
```

Same parameters as `/search/products` but scoped to vendor's products.

## Usage Examples

### Basic Search

```bash
curl "https://api.sater.com/api/v1/search/products?query=hijab"
```

### Advanced Filtering

```bash
curl "https://api.sater.com/api/v1/search/products?query=hijab&category_id=2&price_min=10&price_max=50&fabric_type=cotton&sort=price_asc"
```

### Clothing-Specific Search

```bash
curl "https://api.sater.com/api/v1/search/products?fabric_type=silk&sleeve_length=full_sleeve&opacity_level=opaque"
```

### Vendor Store Search

```bash
curl "https://api.sater.com/api/v1/vendors/5/search?query=abaya&sort=popularity"
```

## How It Works

### Full-Text Search

The system uses MySQL full-text search on `products` table with indexed columns:
- `name`
- `description`
- `keywords`

**Boolean Search Mode** is used:
```
+term*  - terms must match, * for prefix matching
```

### Filtering Strategy

1. **Price Range**: Direct column comparison
2. **Category**: Recursive query to get all child categories
3. **Vendor**: Foreign key index
4. **Rating**: Column comparison with index
5. **Stock**: Boolean check
6. **Clothing Attributes**: JSON column queries
7. **Generic Attributes**: JSON column LIKE searches

### Sorting

Supported sorts with whitelisting to prevent SQL injection:

| Sort | Implementation |
|------|---|
| `relevance` | MySQL full-text score + sales_count + avg_rating |
| `price_asc` | ORDER BY price ASC |
| `price_desc` | ORDER BY price DESC |
| `newest` | ORDER BY created_at DESC |
| `popularity` | ORDER BY sales_count DESC |
| `rating` | ORDER BY avg_rating DESC, rating_count DESC |

### Caching Strategy

- Autocomplete: Cached for 60 minutes
- Popular searches: Cached for 24 hours
- Search history: Real-time (not cached)

## Performance Optimizations

### Database Indexes

Created indexes on high-cardinality columns:

```sql
-- Full-text index
FULLTEXT INDEX idx_fulltext (name, description, keywords)

-- Filter indexes
INDEX idx_category_status (category_id, status)
INDEX idx_vendor_status (vendor_id, status)
INDEX idx_price_status (price, status)
INDEX idx_stock_status (stock, status)

-- Sort indexes
INDEX idx_sales_count_status (sales_count, status)
INDEX idx_avg_rating_status (avg_rating, status)
INDEX idx_created_at_status (created_at, status)

-- Composite indexes
INDEX idx_vendor_category_status (vendor_id, category_id, status)
INDEX idx_price_stock_status (price, stock, status)
INDEX idx_rating_sales_status (avg_rating, sales_count, status)
```

### Query Optimization

- **No N+1 queries**: Relations loaded via Eloquent eager loading
- **Pagination only**: All results paginated (default 20, max 100)
- **Index coverage**: All WHERE/ORDER clauses use indexed columns
- **Cursor pagination**: For infinite scroll without offset overhead

## Search History

- **Storage**: `search_histories` table
- **Retention**: 90 days automatic pruning
- **Per-user limit**: 50 most recent searches
- **Fields stored**: query, filters (snapshot), result count

Prune old entries via command:
```bash
php artisan search:prune-history
```

## No Results Handling

When search returns 0 results, suggestions include:

1. **Similar keywords**: From existing products
2. **Popular in category**: Top sellers in selected category
3. **Top vendors**: Best-selling vendors
4. **Browse categories**: Featured categories for exploration

## Security

- **Input validation**: Via ProductSearchDTO rules
- **Sort whitelisting**: Prevents SQL injection
- **Rate limiting**: On autocomplete endpoint
- **Authentication**: Search history requires `auth:sanctum`
- **Data exposure**: Resources only return public fields

## Service Injection

All services are registered in `SearchServiceProvider`:

```php
use Modules\Product\Services\Search\SearchService;

public function __construct(SearchService $search) {
    $this->search = $search;
}
```

## Extension Points

### Adding to Scout (Laravel Scout)

Replace `ProductSearchQueryBuilder::applyTextSearch()`:

```php
protected function applyTextSearch(Builder $query, string $searchTerm): Builder
{
    return $query->search($searchTerm);
}
```

### Adding to Meilisearch

Create `MeiliSearchQueryBuilder` extending the same interface.

### Custom Filters

Add to `FilterService::apply()`:

```php
$query = $this->applyCustomFilter($query, $dto->custom_field);
```

## Future Enhancements

- [ ] Implement Scout + Meilisearch integration
- [ ] Add search analytics dashboard
- [ ] Implement relevance tuning
- [ ] Add saved searches feature
- [ ] Implement search facets/aggregations
- [ ] Add trending searches
- [ ] Implement search synonyms
- [ ] Add A/B testing framework

## Testing

Run search tests:

```bash
php artisan test tests/Feature/SearchTest.php
```

## Configuration

Edit `app/Support/Search/SearchConfig.php` to customize:

- Max results per page
- History retention period
- Cache durations
- Autocomplete limits
