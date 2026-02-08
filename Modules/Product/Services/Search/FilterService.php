<?php

namespace Modules\Product\Services\Search;

use App\DTOs\ProductSearchDTO;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filter service for applying search filters
 *
 * Handles:
 * - Price range filtering
 * - Category filtering (including nested)
 * - Vendor filtering
 * - Stock filtering
 * - Rating filtering
 * - Clothing-specific attributes
 *
 * Validation happens in DTO, so we can assume valid data here
 */
class FilterService
{
    /**
     * Apply all filters from DTO to query
     */
    public function apply(Builder $query, ProductSearchDTO $dto): Builder
    {
        // Apply general filters
        $query = $this->applyPriceFilter($query, $dto->price_min, $dto->price_max);
        $query = $this->applyCategoryFilter($query, $dto->category_id);
        $query = $this->applyVendorFilter($query, $dto->vendor_id);
        $query = $this->applyRatingFilter($query, $dto->min_rating);
        $query = $this->applyStockFilter($query, $dto->in_stock_only);
        $query = $this->applyAttributeFilters($query, $dto->size, $dto->color);

        // Apply clothing-specific filters
        $clothing = $dto->getClothingFilters();
        $query = $this->applyClothingFilters($query, $clothing);

        return $query;
    }

    /**
     * Filter by price range
     */
    protected function applyPriceFilter(Builder $query, ?float $minPrice, ?float $maxPrice): Builder
    {
        if ($minPrice !== null) {
            $query = $query->where('price', '>=', $minPrice);
        }

        if ($maxPrice !== null) {
            $query = $query->where('price', '<=', $maxPrice);
        }

        return $query;
    }

    /**
     * Filter by category (including nested categories)
     *
     * Gets all child categories recursively, then filters by all of them
     */
    protected function applyCategoryFilter(Builder $query, ?int $categoryId): Builder
    {
        if ($categoryId === null) {
            return $query;
        }

        // Get all descendant category IDs
        $categoryIds = $this->getCategoryAndChildren($categoryId);

        return $query->whereIn('category_id', $categoryIds);
    }

    /**
     * Get category and all its children recursively
     */
    protected function getCategoryAndChildren(int $categoryId): array
    {
        $category = \Modules\Category\Models\Category::find($categoryId);

        if (!$category) {
            return [$categoryId];
        }

        $categoryIds = [$categoryId];

        foreach ($category->children as $child) {
            $categoryIds = array_merge($categoryIds, $this->getCategoryAndChildren($child->id));
        }

        return $categoryIds;
    }

    /**
     * Filter by vendor
     */
    protected function applyVendorFilter(Builder $query, ?int $vendorId): Builder
    {
        if ($vendorId === null) {
            return $query;
        }

        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Filter by minimum rating
     */
    protected function applyRatingFilter(Builder $query, ?float $minRating): Builder
    {
        if ($minRating === null) {
            return $query;
        }

        return $query->where('avg_rating', '>=', $minRating);
    }

    /**
     * Filter by stock availability
     */
    protected function applyStockFilter(Builder $query, bool $inStockOnly): Builder
    {
        if (!$inStockOnly) {
            return $query;
        }

        return $query->where('stock', '>', 0);
    }

    /**
     * Filter by generic attributes (size, color)
     *
     * These are stored in JSON attributes field
     */
    protected function applyAttributeFilters(Builder $query, ?string $size, ?string $color): Builder
    {
        if ($size !== null) {
            $query = $query->where('attributes->size', 'LIKE', "%{$size}%");
        }

        if ($color !== null) {
            $query = $query->where('attributes->color', 'LIKE', "%{$color}%");
        }

        return $query;
    }

    /**
     * Apply Islamic clothing-specific filters
     *
     * These filters are only relevant for clothing products
     * and should be gracefully ignored for non-clothing items
     */
    protected function applyClothingFilters(Builder $query, array $clothing): Builder
    {
        if (empty(array_filter($clothing))) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($clothing) {
            foreach ($clothing as $key => $value) {
                if ($value === null) {
                    continue;
                }

                // Check if product has this clothing attribute set
                $q->orWhere(function (Builder $subQ) use ($key, $value) {
                    $subQ->whereJsonContains("clothing_attributes->{$key}", $value);
                });
            }
        });
    }
}
