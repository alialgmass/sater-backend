<?php

namespace App\Support\Traits\Models;

use Illuminate\Database\Eloquent\Builder;
use Modules\Tenant\Models\Tenant;

trait BelongToTeam
{
    public function team()
    {
        return $this->belongsTo(Tenant::class);
    }

    protected static function booted(): void
    {
        static::addGlobalScope('teams', function (Builder $query) {
            if (auth()->check()) {

                $query->whereBelongsTo(auth()->user()->team);
            }
        });
    }
}
