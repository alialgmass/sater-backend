<?php

namespace Modules\Core\DTOs;

/**
 * Data Transfer Object for autocomplete requests
 */
class AutocompleteDTO
{
    public function __construct(
        public ?string $query = null,
        public ?int $vendor_id = null,
        public int $limit = 10,
    ) {}

    public static function from(array $data): self
    {
        return new self(
            query: data_get($data, 'query'),
            vendor_id: data_get($data, 'vendor_id'),
            limit: min((int) data_get($data, 'limit', 10), 50), // Max 50
        );
    }

    public static function rules(): array
    {
        return [
            'query' => ['required', 'string', 'min:2', 'max:255'],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
