<?php

namespace App\Support\Traits;

use Modules\Notification\Enums\DeviceTokenType;
use Modules\Notification\Models\DeviceToken;

trait HasDeviceToken
{
    public function deviceTokens($type = null): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(DeviceToken::class, 'reference')
            ->where('type', $type ?? DeviceTokenType::FIREBASE->value);
    }
}
