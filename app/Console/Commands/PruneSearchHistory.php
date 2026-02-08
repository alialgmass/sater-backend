<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Product\Services\Search\SearchHistoryService;

class PruneSearchHistory extends Command
{
    protected $signature = 'search:prune-history';

    protected $description = 'Prune old search history entries (older than 90 days)';

    public function handle(SearchHistoryService $service)
    {
        $this->info('Pruning search history...');

        $deleted = $service->pruneOld();

        $this->info("Deleted {$deleted} old search history entries.");

        return self::SUCCESS;
    }
}
