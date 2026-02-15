<?php

namespace Modules\Product\Http\Controllers\Api;

use App\DTOs\AutocompleteDTO;
use App\DTOs\ProductSearchDTO;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Modules\Product\Http\Resources\ProductSearchResource;
use Modules\Product\Http\Resources\SearchSuggestionResource;
use Modules\Product\Services\Search\SearchHistoryService;
use Modules\Product\Services\Search\SearchService;

/**
 * Search API Controller
 *
 * Handles:
 * - Product search
 * - Autocomplete/suggestions
 * - Search history (authenticated)
 *
 * All endpoints are API-first and follow RESTful principles
 */
class SearchController extends ApiController
{
    public function __construct(
        protected SearchService $searchService,
        protected SearchHistoryService $historyService,
    ) {}

    /**
     * Search products
     *
     * GET /api/v1/search/products
     *
     * Query Parameters:
     * - query: string (optional)
     * - category_id: int (optional)
     * - price_min: float (optional)
     * - price_max: float (optional)
     * - size: string (optional)
     * - color: string (optional)
     * - vendor_id: int (optional)
     * - min_rating: float (optional)
     * - in_stock_only: boolean (optional)
     * - fabric_type: string (optional) - clothing attribute
     * - sleeve_length: string (optional) - clothing attribute
     * - opacity_level: string (optional) - clothing attribute
     * - hijab_style: string (optional) - clothing attribute
     * - sort: string (optional) - relevance|price_asc|price_desc|newest|popularity|rating
     * - page: int (default: 1)
     * - per_page: int (default: 20, max: 100)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        // Validate request
        $validated = $request->validate(ProductSearchDTO::rules());

        // Create DTO
        $dto = ProductSearchDTO::from($validated);

        // Execute search
        $results = $this->searchService->search($dto);

        // Record in history if authenticated
        if ($request->user() && $dto->hasQuery()) {
            $this->historyService->record(
                $request->user(),
                $dto->query,
                $dto->getGeneralFilters(),
                $results->total()
            );
        }

        // Check if results are empty
        if ($results->isEmpty()) {
            $suggestions = $this->searchService->getSuggestions($dto);

            return $this->apiBody([
                'products' => [],
                'pagination' => [
                    'total' => 0,
                    'per_page' => $dto->per_page,
                    'current_page' => $dto->page,
                    'last_page' => 0,
                ],
                'suggestions' => $suggestions,
            ])
            ->apiMessage('No products found. Check suggestions.')
            ->apiCode(202)
            ->apiResponse(); // Accepted but no content in body
        }

        return $this->apiBody([
            'products' => ProductSearchResource::paginate($results),
        ])->apiResponse();
    }

    /**
     * Get search suggestions with cursor pagination
     *
     * Useful for infinite scroll implementations
     *
     * GET /api/v1/search/cursor
     */
    public function searchCursor(Request $request)
    {
        $validated = $request->validate(ProductSearchDTO::rules());

        $dto = ProductSearchDTO::from($validated);

        // Get cursor from request
        $cursor = $request->get('cursor');

        // Execute search with cursor pagination
        $results = $this->searchService->searchWithCursor($dto, $cursor);

        // Record in history
        if ($request->user() && $dto->hasQuery()) {
            $this->historyService->record(
                $request->user(),
                $dto->query,
                $dto->getGeneralFilters(),
                count($results->items())
            );
        }

        return $this->apiBody([
            'products' => ProductSearchResource::collection($results->items()),
            'next_cursor' => $results->nextCursor()?->encode(),
            'prev_cursor' => $results->previousCursor()?->encode(),
        ])->apiResponse();
    }

    /**
     * Get autocomplete suggestions
     *
     * GET /api/v1/search/autocomplete
     *
     * Query Parameters:
     * - query: string (required)
     * - vendor_id: int (optional)
     * - limit: int (optional, default: 10, max: 50)
     *
     * Returns suggested product names and popular search terms
     */
    public function autocomplete(Request $request)
    {
        $validated = $request->validate(AutocompleteDTO::rules());

        $dto = AutocompleteDTO::from($validated);

        try {
            $suggestions = $this->searchService->getAutocomplete(
                $dto->query,
                $dto->vendor_id,
                $dto->limit
            );
        } catch (\Exception $e) {
            // Fallback to LIKE search if full-text search fails
            $suggestions = $this->searchService->getAutocompleteFallback(
                $dto->query,
                $dto->vendor_id,
                $dto->limit
            );
        }

        return $this->apiBody([
            'suggestions' => SearchSuggestionResource::collection($suggestions),
        ])->apiResponse();
    }

    /**
     * Get user's search history
     *
     * GET /api/v1/search/history
     *
     * Requires authentication
     *
     * Query Parameters:
     * - limit: int (optional, default: 20)
     */
    public function history(Request $request)
    {
        $request->user() ?? $this->unauthorized('Authentication required');

        $limit = min((int) $request->get('limit', 20), 100);

        $history = $this->historyService->getHistory($request->user(), $limit);

        return $this->apiBody([
            'history' => $history,
        ])->apiResponse();
    }

    /**
     * Clear user's search history
     *
     * DELETE /api/v1/search/history
     *
     * Requires authentication
     */
    public function clearHistory(Request $request)
    {
        $request->user() ?? $this->unauthorized('Authentication required');

        $this->historyService->clearHistory($request->user());

        return $this->apiBody([
            'message' => 'Search history cleared',
        ])->apiResponse();
    }

    /**
     * Delete single history entry
     *
     * DELETE /api/v1/search/history/{id}
     */
    public function deleteHistory(Request $request, int $id)
    {
        $request->user() ?? $this->unauthorized('Authentication required');

        $deleted = $this->historyService->deleteEntry($request->user(), $id);

        if (!$deleted) {
            return $this->notFound('History entry not found');
        }

        return $this->apiBody([
            'message' => 'History entry deleted',
        ])->apiResponse();
    }
}
