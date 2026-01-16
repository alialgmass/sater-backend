<?php

namespace App\Services\Search;

use App\DTOs\ProductSearchDTO;
use Illuminate\Database\Eloquent\Builder;
use Modules\Product\Models\Product;

/**
 * Query builder for product search
 *
 * Handles:
 * - Full-text search
 * - Filtering
 * - Sorting
 * - Pagination preparation
 *
 * Kept separate from SearchService for single responsibility
 */
class ProductSearchQueryBuilder
{
    public function __construct(
        protected FilterService $filterService,
        protected SortService $sortService,
    ) {}

    /**
     * Build the search query based on DTO
     */
    public function build(ProductSearchDTO $dto): Builder
    {
        $query = Product::query()
            ->where('status', 'active');

        // Apply full-text search if query is present
        if ($dto->hasQuery()) {
            $query = $this->applyTextSearch($query, $dto->query);
        }

        // Apply filters
        if ($dto->hasFilters()) {
            $query = $this->filterService->apply($query, $dto);
        }

        // Apply sorting
        $query = $this->sortService->apply($query, $dto->sort, $dto->hasQuery());

        return $query;
    }

    /**
     * Apply full-text search using MySQL full-text index
     *
     * This uses the full-text index on name, description, keywords
     * which is significantly faster than LIKE queries
     */
    protected function applyTextSearch(Builder $query, string $searchTerm): Builder
    {
        return $query->whereRaw(
            "MATCH(name, description, keywords) AGAINST(? IN BOOLEAN MODE)",
            ["+{$searchTerm}*"]
        );
    }

    /**
     * Fallback search using LIKE if full-text not available
     * (e.g., if database doesn't support full-text search)
     */
    public function applyLikeSearch(Builder $query, string $searchTerm): Builder
    {
        $term = "%{$searchTerm}%";

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'LIKE', $term)
              ->orWhere('description', 'LIKE', $term)
              ->orWhere('keywords', 'LIKE', $term)
              ->orWhere('sku', 'LIKE', $term);
        });
    }
}
