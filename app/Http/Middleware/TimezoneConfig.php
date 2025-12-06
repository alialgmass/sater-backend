<?php

namespace App\Http\Middleware;

use Closure;

class TimezoneConfig
{
    public function handle($request, Closure $next)
    {
        $timezone = $request->headers->get('Accept-Timezone');

        if (in_array($timezone, \DateTimeZone::listIdentifiers())) {
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
        }

        return $next($request);
    }
}
