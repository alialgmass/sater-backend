<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException\ExceptionResponse;
use Closure;
use Ejarnutowski\LaravelApiKey\Http\Middleware\AuthorizeApiKey;
use Ejarnutowski\LaravelApiKey\Models\ApiKey;
use Illuminate\Http\Request;

class AuthApiKey extends AuthorizeApiKey
{
    /**
     * Handle the incoming request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|mixed|\Symfony\Component\HttpFoundation\Response
     *
     * @throws ExceptionResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $header = $request->header(self::AUTH_HEADER);
        $apiKey = ApiKey::getByKey($header);

        if ($apiKey instanceof ApiKey) {
            $this->logAccessEvent($request, $apiKey);

            return $next($request);
        }

        throw ExceptionResponse::instance(__('validation.invalid api key'), 401);
    }
}
