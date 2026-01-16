<?php

namespace App\Services\Search;

use App\DTOs\ProductSearchDTO;
use Modules\Product\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Main search service orchestrating the search pipeline
 *
 * This service coordinates:
 * - Query building
 * - Filtering
 * - Sorting
 * - Pagination
 *
 * Designed to be extensible for:
 * - Laravel Scout integration
 * - Meilisearch/Elasticsearch migration
 * - Custom search engines
 */
class SearchService
{
    public function __construct(
        protected ProductSearchQueryBuilder $queryBuilder,
        protected FilterService $filterService,
        protected SortService $sortService,
    ) {}

    /**
     * Execute a product search
     *
     * @return Paginator
     */
    public function search(ProductSearchDTO $dto): LengthAwarePaginator
    {
        $query = $this->queryBuilder->build($dto);

        return $query->paginate(
            perPage: $dto->per_page,
            page: $dto->page
        );
    }

    /**
     * Search with cursor pagination (for API pagination)
     * More efficient for large datasets
     */
    public function searchWithCursor(ProductSearchDTO $dto, ?string $cursor = null)
    {
        $query = $this->queryBuilder->build($dto);

        return $query->cursorPaginate(
            perPage: $dto->per_page,
            cursor: $cursor
        );
    }

    /**
     * Get search suggestions (autocomplete)
     *
     * @return array
     */
    public function getAutocomplete(string $query, ?int $vendorId = null, int $limit = 10): array
    {
        // Get product name suggestions
        $productSuggestions = Product::query()
            ->where('status', 'active')
            ->when($vendorId, fn($q) => $q->where('vendor_id', $vendorId))
            ->where('stock', '>', 0)
            ->whereRaw("MATCH(name, keywords) AGAINST(? IN BOOLEAN MODE)", ["+{$query}*"])
            ->select('name')
            ->distinct()
            ->limit($limit)
            ->pluck('name')
            ->toArray();

        // Get popular search terms (from keywords field)
        $keywordSuggestions = Product::query()
            ->where('status', 'active')
            ->when($vendorId, fn($q) => $q->where('vendor_id', $vendorId))
            ->where('stock', '>', 0)
            ->whereRaw("MATCH(keywords) AGAINST(? IN BOOLEAN MODE)", ["+{$query}*"])
            ->select('keywords')
            ->limit($limit)
            ->pluck('keywords')
            ->flatMap(fn($keywords) => explode(',', $keywords ?? ''))
            ->map(fn($kw) => trim($kw))
            ->filter(fn($kw) => str_contains(strtolower($kw), strtolower($query)))
            ->unique()
            ->values()
            ->slice(0, $limit)
            ->toArray();

        return array_merge($productSuggestions, $keywordSuggestions);
    }

    /**
     * Get search suggestions (fallback for LIKE search if full-text not available)
     */
    public function getAutocompleteFallback(string $query, ?int $vendorId = null, int $limit = 10): array
    {
        $query = "%{$query}%";

        $productSuggestions = Product::query()
            ->where('status', 'active')
            ->when($vendorId, fn($q) => $q->where('vendor_id', $vendorId))
            ->where('stock', '>', 0)
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', $query)
                  ->orWhere('keywords', 'LIKE', $query);
            })
            ->select('name')
            ->distinct()
            ->limit($limit)
            ->pluck('name')
            ->toArray();

        return $productSuggestions;
    }

    /**
     * Get no-results suggestions
     *
     * @return array
     */
    public function getSuggestions(ProductSearchDTO $dto): array
    {
        $suggestions = [];

        // Similar keywords
        if ($dto->hasQuery()) {
            $similar = $this->findSimilarKeywords($dto->query);
            if (!empty($similar)) {
                $suggestions['similar_keywords'] = $similar;
            }
        }

        // Popular products in category
        if ($dto->category_id) {
            $suggestions['popular_in_category'] = $this->getPopularInCategory($dto->category_id);
        }

        // Top vendors
        $suggestions['top_vendors'] = $this->getTopVendors();

        return $suggestions;
    }

    /**
     * Find similar keywords using full-text search
     */
    protected function findSimilarKeywords(string $keyword, int $limit = 5): array
    {
        return Product::query()
            ->where('status', 'active')
            ->where('stock', '>', 0)
            ->whereRaw("MATCH(keywords) AGAINST(? IN BOOLEAN MODE)", ["+{$keyword}"])
            ->select('keywords')
            ->distinct()
            ->limit($limit)
            ->pluck('keywords')
            ->flatMap(fn($keywords) => explode(',', $keywords ?? ''))
            ->map(fn($kw) => trim($kw))
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get popular products in a category
     */
    protected function getPopularInCategory(int $categoryId, int $limit = 5): array
    {
        return Product::query()
            ->where('category_id', $categoryId)
            ->where('status', 'active')
            ->where('stock', '>', 0)
            ->orderByDesc('sales_count')
            ->select(['id', 'name', 'price', 'discounted_price'])
            ->limit($limit)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'price' => $p->price,
            ])
            ->toArray();
    }

    /**
     * Get top vendors
     */
    protected function getTopVendors(int $limit = 5): array
    {
        return Product::query()
            ->where('status', 'active')
            ->where('stock', '>', 0)
            ->with('vendor')
            ->groupBy('vendor_id')
            ->orderByRaw('COUNT(*) DESC')
            ->select('vendor_id')
            ->limit($limit)
            ->pluck('vendor')
            ->map(fn($v) => [
                'id' => $v->id,
                'name' => $v->name ?? $v->shop_name,
            ])
            ->unique('id')
            ->values()
            ->toArray();
    }
}
