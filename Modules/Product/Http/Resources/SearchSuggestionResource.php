<?php

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for search suggestions/autocomplete
 */
class SearchSuggestionResource extends JsonResource
{
    public function toArray($request)
    {
        // If resource is just a string (product name)
        if (is_string($this->resource)) {
            return [
                'text' => $this->resource,
                'type' => 'keyword',
            ];
        }

        // If it's an array with structured data
        return $this->resource;
    }
}
