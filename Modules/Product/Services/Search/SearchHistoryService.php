<?php

namespace Modules\Product\Services\Search;

use App\Models\SearchHistory;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Search history service for authenticated users
 *
 * Handles:
 * - Recording search queries
 * - Retrieving user's search history
 * - Pruning old entries
 * - Privacy considerations
 */
class SearchHistoryService
{
    private const HISTORY_LIMIT = 50; // Max history per user
    private const RETENTION_DAYS = 90; // Keep history for 90 days

    /**
     * Record a search query
     *
     * Only records for authenticated users
     */
    public function record(?Authenticatable $user, string $query, array $filters = [], int $resultsCount = 0): ?SearchHistory
    {
        if (!$user) {
            return null;
        }

        // Don't record empty queries
        if (empty(trim($query))) {
            return null;
        }

        $history = SearchHistory::create([
            'user_id' => $user->id,
            'query' => trim($query),
            'filters' => !empty($filters) ? $filters : null,
            'results_count' => $resultsCount,
        ]);

        // Prune old entries if limit exceeded
        $this->pruneIfNeeded($user->id);

        return $history;
    }

    /**
     * Get user's search history
     */
    public function getHistory(?Authenticatable $user, int $limit = 20)
    {
        if (!$user) {
            return collect();
        }

        return SearchHistory::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get popular searches from history
     *
     * Useful for trending searches across all users
     */
    public function getPopularSearches(int $limit = 10): array
    {
        return SearchHistory::query()
            ->groupBy('query')
            ->selectRaw('query, COUNT(*) as search_count')
            ->orderByRaw('COUNT(*) DESC')
            ->where('created_at', '>=', now()->subDays(7)) // Last 7 days
            ->limit($limit)
            ->get()
            ->map(fn($s) => [
                'query' => $s->query,
                'count' => $s->search_count,
            ])
            ->toArray();
    }

    /**
     * Clear user's search history
     */
    public function clearHistory(?Authenticatable $user): bool
    {
        if (!$user) {
            return false;
        }

        SearchHistory::where('user_id', $user->id)->delete();
        return true;
    }

    /**
     * Delete single history entry
     */
    public function deleteEntry(?Authenticatable $user, int $historyId): bool
    {
        if (!$user) {
            return false;
        }

        return SearchHistory::where('id', $historyId)
            ->where('user_id', $user->id)
            ->delete() > 0;
    }

    /**
     * Prune history if limit exceeded
     */
    protected function pruneIfNeeded(int $userId): void
    {
        $count = SearchHistory::where('user_id', $userId)->count();

        if ($count > self::HISTORY_LIMIT) {
            SearchHistory::query()
                ->where('user_id', $userId)
                ->orderBy('created_at', 'asc')
                ->limit($count - self::HISTORY_LIMIT)
                ->delete();
        }
    }

    /**
     * Delete old entries (called by scheduled task)
     */
    public function pruneOld(): int
    {
        return SearchHistory::where('created_at', '<', now()->subDays(self::RETENTION_DAYS))->delete();
    }
}
