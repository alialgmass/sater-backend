<?php

namespace App\Support\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\StateLog\Models\StateLog;

trait HasStates
{
    use \Spatie\ModelStates\HasStates;

    public function stateLogs(): MorphMany
    {
        return $this->morphMany(StateLog::class, 'resource');
    }

    public function lastLog()
    {
        return $this->morphOne(StateLog::class, 'resource')->latest();
    }

    public function getStateHistory(): array
    {
        $logs = $this->stateLogs;

        $history = [];
        foreach ($logs as $index => $log) {
            if ($log->oldState) {
                $history[] = $log->oldState->toArray();
            }

            if ($index == (count($logs) - 1) && $log->newState) {
                $history[] = $log->newState->toArray();
            }
        }

        return $history;
    }
}
