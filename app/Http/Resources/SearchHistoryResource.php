<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for search history
 */
class SearchHistoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'query' => $this->query,
            'filters' => $this->filters,
            'results_count' => $this->results_count,
            'searched_at' => $this->created_at->toIso8601String(),
        ];
    }
}
