<?php

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Auth\Exceptions\CustomerPhoneNotVerified;

class CustomerMustVerified
{
    /**
     * Handle an incoming request.
     * @throws CustomerPhoneNotVerified
     */
    public function handle(Request $request, Closure $next)
    {
        $customer = $request->user('api_customers');
        if(! $customer?->isVerified() ) {
            throw   CustomerPhoneNotVerified::instance("phone number is need to be verified");
        }
        return $next($request);
    }
}
