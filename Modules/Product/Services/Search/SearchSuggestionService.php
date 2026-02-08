<?php

namespace Modules\Product\Services\Search;

use App\DTOs\ProductSearchDTO;
use Illuminate\Database\Eloquent\Collection;
use Modules\Product\Models\Product;
use const Modules\Product\Services\Search\browse;
use const Modules\Product\Services\Search\catalog;
use const Modules\Product\Services\Search\children;
use const Modules\Product\Services\Search\DESC;
use const Modules\Product\Services\Search\featured_categories;
use const Modules\Product\Services\Search\id;
use const Modules\Product\Services\Search\message;
use const Modules\Product\Services\Search\name;
use const Modules\Product\Services\Search\our;
use const Modules\Product\Services\Search\parent_id;
use const Modules\Product\Services\Search\shop_slug;
use const Modules\Product\Services\Search\vendor;
use const Modules\Product\Services\Search\vendor_id;

/**
 * Search suggestion service for "no results" handling
 *
 * Provides intelligent suggestions when search returns no results
 */
class SearchSuggestionService
{
    /**
     * Get suggestions for empty search results
     */
    public function getSuggestions(ProductSearchDTO $dto): array
    {
        $suggestions = [];

        // 1. Try with fewer filters
        if ($dto->hasFilters()) {
            $relaxedResults = $this->searchWithFewerFilters($dto);
            if ($relaxedResults->count() > 0) {
                $suggestions['try_without_filters'] = [
                    'message' => 'No exact matches found. Try removing some filters.',
                    'products' => $relaxedResults->take(5)->map(fn($p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                        'price' => $p->price,
                    ])->toArray(),
                ];
            }
        }

        // 2. Find similar keywords
        if ($dto->hasQuery()) {
            $similar = $this->findSimilarKeywords($dto->query);
            if (!empty($similar)) {
                $suggestions['similar_keywords'] = $similar;
            }
        }

        // 3. Popular products in category
        if ($dto->category_id) {
            $popular = $this->getPopularInCategory($dto->category_id);
            if (!empty($popular)) {
                $suggestions['popular_in_category'] = $popular;
            }
        }

        // 4. Top vendors
        $topVendors = $this->getTopVendors();
        if (!empty($topVendors)) {
            $suggestions['top_vendors'] = $topVendors;
        }

        // 5. Browse suggestions
        $suggestions['browse_suggestions'] = $this->getBrowseSuggestions();

        return $suggestions;
    }

    /**
     * Try search with progressively fewer filters
     */
    protected function searchWithFewerFilters(ProductSearchDTO $dto): Collection
    {
        $query = Product::query()->where('status', 'active');

        if ($dto->hasQuery()) {
            $query = $query->whereRaw(
                "MATCH(name, description, keywords) AGAINST(? IN BOOLEAN MODE)",
                ["+{$dto->query}*"]
            );
        }

        // Keep only primary filters
        if ($dto->category_id) {
            $query = $query->where('category_id', $dto->category_id);
        }

        if ($dto->vendor_id) {
            $query = $query->where('vendor_id', $dto->vendor_id);
        }

        if ($dto->in_stock_only) {
            $query = $query->where('stock', '>', 0);
        }

        return $query->limit(10)->get();
    }

    /**
     * Find keywords similar to search term
     */
    protected function findSimilarKeywords(string $keyword): array
    {
        // Get keywords that contain words from the search
        $words = explode(' ', strtolower($keyword));

        $keywords = Product::query()
            ->where('status', 'active')
            ->where('stock', '>', 0)
            ->select('keywords')
            ->limit(20)
            ->pluck('keywords')
            ->filter()
            ->flatMap(fn($kw) => explode(',', $kw))
            ->map(fn($kw) => trim($kw))
            ->unique();

        // Filter for partial matches
        return $keywords
            ->filter(function ($kw) use ($words) {
                foreach ($words as $word) {
                    if (strlen($word) > 3 && str_contains(strtolower($kw), $word)) {
                        return true;
                    }
                }
                return false;
            })
            ->take(5)
            ->toArray();
    }

    /**
     * Get popular products in category
     */
    protected function getPopularInCategory(int $categoryId): array
    {
        return Product::query()
            ->where('category_id', $categoryId)
            ->where('status', 'active')
            ->where('stock', '>', 0)
            ->orderByDesc('sales_count')
            ->select(['id', 'name', 'price', 'discounted_price'])
            ->limit(5)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'price' => $p->price,
            ])
            ->toArray();
    }

    /**
     * Get top vendors with most products
     */
    protected function getTopVendors(): array
    {
        return Product::query()
            ->where('status', 'active')
            ->where('stock', '>', 0')
            ->with('vendor')
            ->groupBy('vendor_id')
            ->orderByRaw('COUNT(*) DESC')
            ->select('vendor_id')
            ->limit(5)
            ->pluck('vendor')
            ->filter()
            ->map(fn($v) => [
                'id' => $v->id,
                'name' => $v->name ?? $v->shop_name,
                'shop_slug' => $v->shop_slug ?? null,
            ])
            ->unique('id')
            ->values()
            ->toArray();
    }

    /**
     * Get general browse suggestions (featured categories, etc)
     */
    protected function getBrowseSuggestions(): array
    {
        return [
            'message' => 'Or browse our catalog',
            'featured_categories' => \Modules\Category\Models\Category::query()
                ->with('children')
                ->whereNull('parent_id')
                ->limit(6)
                ->get()
                ->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                ])
                ->toArray(),
        ];
    }
}
