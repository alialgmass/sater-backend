<?php

namespace App\Support\Traits;

use Modules\Team\Models\Tenant;

trait HasTeam
{
    public function team()
    {
        return $this->belongsTo(Tenant::class);
    }
}
