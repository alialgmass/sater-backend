<?php

namespace Modules\Product\Services\Search;

use App\Enums\SortOptionEnum;
use Illuminate\Database\Eloquent\Builder;

/**
 * Sort service for applying sorting to search results
 *
 * Centralized sorting logic with whitelisting to prevent SQL injection
 * Handles:
 * - Relevance (based on search match)
 * - Price (ascending/descending)
 * - Newest products
 * - Popularity (sales count)
 * - Rating
 */
class SortService
{
    /**
     * Whitelist of allowed sort fields for security
     */
    private const ALLOWED_SORTS = [
        'relevance' => 'relevance',
        'price_asc' => 'price_asc',
        'price_desc' => 'price_desc',
        'newest' => 'newest',
        'popularity' => 'popularity',
        'rating' => 'rating',
    ];

    /**
     * Apply sorting to query
     *
     * @param Builder $query
     * @param string $sort Sort option from SortOptionEnum
     * @param bool $hasSearchQuery Whether a text search was performed (for relevance)
     */
    public function apply(Builder $query, string $sort, bool $hasSearchQuery = false): Builder
    {
        // Whitelist validation - prevents SQL injection
        $sort = $this->validateSort($sort);

        return match($sort) {
            'relevance' => $this->applySortRelevance($query, $hasSearchQuery),
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderByDesc('price'),
            'newest' => $query->orderByDesc('created_at'),
            'popularity' => $query->orderByDesc('sales_count'),
            'rating' => $query->orderByDesc('avg_rating')->orderByDesc('rating_count'),
        };
    }

    /**
     * Validate sort parameter against whitelist
     */
    private function validateSort(string $sort): string
    {
        $validated = self::ALLOWED_SORTS[$sort] ?? 'relevance';
        return $this->isValidEnum($validated) ? $validated : 'relevance';
    }

    /**
     * Check if sort is valid enum value
     */
    private function isValidEnum(string $sort): bool
    {
        return in_array($sort, SortOptionEnum::values());
    }

    /**
     * Apply relevance sorting
     *
     * For full-text search, MySQL naturally scores results by relevance
     * For non-search queries, we sort by popularity + rating
     */
    protected function applySortRelevance(Builder $query, bool $hasSearchQuery): Builder
    {
        if ($hasSearchQuery) {
            // MySQL full-text search already provides relevance scoring
            // We can optionally add secondary sorting by popularity/rating
            return $query->orderByDesc('sales_count')
                        ->orderByDesc('avg_rating');
        }

        // For non-search queries, treat relevance as popularity
        return $query->orderByDesc('sales_count')
                    ->orderByDesc('avg_rating')
                    ->orderByDesc('created_at');
    }

    /**
     * Get all available sort options
     */
    public static function getAvailableOptions(): array
    {
        return self::ALLOWED_SORTS;
    }
}
