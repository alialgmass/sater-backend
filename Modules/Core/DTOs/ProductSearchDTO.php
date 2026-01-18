<?php

namespace Modules\Core\DTOs;

use Modules\Core\Enums\SortOptionEnum;
use Illuminate\Validation\Rule;

/**
 * Data Transfer Object for product search requests
 *
 * This DTO encapsulates all search parameters and provides validation
 * at the application layer, keeping controllers clean.
 */
class ProductSearchDTO
{
    public function __construct(
        public ?string $query = null,
        public ?int $category_id = null,
        public ?float $price_min = null,
        public ?float $price_max = null,
        public ?string $size = null,
        public ?string $color = null,
        public ?int $vendor_id = null,
        public ?float $min_rating = null,
        public bool $in_stock_only = false,
        public ?string $fabric_type = null,
        public ?string $sleeve_length = null,
        public ?string $opacity_level = null,
        public ?string $hijab_style = null,
        public string $sort = 'relevance',
        public int $page = 1,
        public int $per_page = 20,
    ) {}

    /**
     * Create DTO from array (typically from request)
     */
    public static function from(array $data): self
    {
        return new self(
            query: data_get($data, 'query'),
            category_id: data_get($data, 'category_id'),
            price_min: data_get($data, 'price_min'),
            price_max: data_get($data, 'price_max'),
            size: data_get($data, 'size'),
            color: data_get($data, 'color'),
            vendor_id: data_get($data, 'vendor_id'),
            min_rating: data_get($data, 'min_rating'),
            in_stock_only: (bool) data_get($data, 'in_stock_only', false),
            fabric_type: data_get($data, 'fabric_type'),
            sleeve_length: data_get($data, 'sleeve_length'),
            opacity_level: data_get($data, 'opacity_level'),
            hijab_style: data_get($data, 'hijab_style'),
            sort: data_get($data, 'sort', 'relevance'),
            page: (int) data_get($data, 'page', 1),
            per_page: min((int) data_get($data, 'per_page', 20), 100), // Max 100 per page
        );
    }

    /**
     * Get validation rules for search parameters
     */
    public static function rules(): array
    {
        return [
            'query' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
            'size' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:50'],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'min_rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'in_stock_only' => ['nullable', 'boolean'],
            'fabric_type' => ['nullable', 'string', 'in:' . implode(',', \Modules\Core\Enums\FabricTypeEnum::values())],
            'sleeve_length' => ['nullable', 'string', 'in:' . implode(',', \Modules\Core\Enums\SleeveLengthEnum::values())],
            'opacity_level' => ['nullable', 'string', 'in:' . implode(',', \Modules\Core\Enums\OpacityLevelEnum::values())],
            'hijab_style' => ['nullable', 'string', 'in:' . implode(',', \Modules\Core\Enums\HijabStyleEnum::values())],
            'sort' => ['nullable', 'string', 'in:' . implode(',', \Modules\Core\Enums\SortOptionEnum::values())],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * Check if query is present (for search history)
     */
    public function hasQuery(): bool
    {
        return !empty($this->query);
    }

    /**
     * Check if any filters are applied
     */
    public function hasFilters(): bool
    {
        return !empty($this->category_id)
            || !empty($this->price_min)
            || !empty($this->price_max)
            || !empty($this->size)
            || !empty($this->color)
            || !empty($this->vendor_id)
            || !empty($this->min_rating)
            || $this->in_stock_only
            || !empty($this->fabric_type)
            || !empty($this->sleeve_length)
            || !empty($this->opacity_level)
            || !empty($this->hijab_style);
    }

    /**
     * Get clothing-specific filters (Islamic attributes)
     */
    public function getClothingFilters(): array
    {
        return [
            'fabric_type' => $this->fabric_type,
            'sleeve_length' => $this->sleeve_length,
            'opacity_level' => $this->opacity_level,
            'hijab_style' => $this->hijab_style,
        ];
    }

    /**
     * Get non-clothing filters
     */
    public function getGeneralFilters(): array
    {
        return [
            'category_id' => $this->category_id,
            'price_min' => $this->price_min,
            'price_max' => $this->price_max,
            'size' => $this->size,
            'color' => $this->color,
            'vendor_id' => $this->vendor_id,
            'min_rating' => $this->min_rating,
            'in_stock_only' => $this->in_stock_only,
        ];
    }

    /**
     * Convert to array for caching
     */
    public function toArray(): array
    {
        return [
            'query' => $this->query,
            'category_id' => $this->category_id,
            'price_min' => $this->price_min,
            'price_max' => $this->price_max,
            'size' => $this->size,
            'color' => $this->color,
            'vendor_id' => $this->vendor_id,
            'min_rating' => $this->min_rating,
            'in_stock_only' => $this->in_stock_only,
            'fabric_type' => $this->fabric_type,
            'sleeve_length' => $this->sleeve_length,
            'opacity_level' => $this->opacity_level,
            'hijab_style' => $this->hijab_style,
            'sort' => $this->sort,
        ];
    }
}
