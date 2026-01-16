<?php

namespace App\Support\Search;

/**
 * Search configuration and utilities
 *
 * This class provides configuration constants for the search system
 * and helper methods for search-related operations
 */
class SearchConfig
{
    /**
     * Maximum results per page for search
     */
    public const MAX_PER_PAGE = 100;

    /**
     * Default results per page
     */
    public const DEFAULT_PER_PAGE = 20;

    /**
     * Maximum history records per user
     */
    public const HISTORY_LIMIT = 50;

    /**
     * Search history retention (days)
     */
    public const HISTORY_RETENTION_DAYS = 90;

    /**
     * Cache duration for autocomplete (minutes)
     */
    public const AUTOCOMPLETE_CACHE_MINUTES = 60;

    /**
     * Cache duration for popular searches (minutes)
     */
    public const POPULAR_SEARCHES_CACHE_MINUTES = 1440; // 24 hours

    /**
     * Minimum query length for autocomplete
     */
    public const MIN_AUTOCOMPLETE_LENGTH = 2;

    /**
     * Maximum autocomplete suggestions
     */
    public const MAX_AUTOCOMPLETE_SUGGESTIONS = 50;

    /**
     * Get all configuration as array
     */
    public static function all(): array
    {
        return [
            'max_per_page' => self::MAX_PER_PAGE,
            'default_per_page' => self::DEFAULT_PER_PAGE,
            'history_limit' => self::HISTORY_LIMIT,
            'history_retention_days' => self::HISTORY_RETENTION_DAYS,
            'autocomplete_cache_minutes' => self::AUTOCOMPLETE_CACHE_MINUTES,
            'popular_searches_cache_minutes' => self::POPULAR_SEARCHES_CACHE_MINUTES,
        ];
    }
}
