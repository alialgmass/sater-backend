<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException\ExceptionResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class CustomThrottleRequests extends ThrottleRequests
{
    /**
     * Create a 'too many attempts' exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $key
     * @param  int  $maxAttempts
     * @param  callable|null  $responseCallback
     */
    protected function buildException($request, $key, $maxAttempts, $responseCallback = null): HttpResponseException
    {
        $retryAfter = $this->getTimeUntilNextRetry($key);

        $headers = $this->getHeaders(
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts, $retryAfter),
            $retryAfter
        );

        $manDays = $this->secondsToTime(RateLimiter::availableIn($key));

        return is_callable($responseCallback)
            ? new HttpResponseException($responseCallback($request, $headers))
            : throw ValidationException::withMessages([
                __('validation.attempted too many times.', [
                    'seconds' => $manDays->s,
                    'minutes' => $manDays->i,
                    'hours' => $manDays->h,
                    'days' => $manDays->d,
                ]),
            ]);
    }

    /**
     * Resolve request signature.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @throws \RuntimeException
     * @throws ExceptionResponse
     */
    protected function resolveRequestSignature($request): string
    {
        if ($user = $request->user()) {
            return sha1($user->getAuthIdentifier());
        } elseif ($route = $request->route()) {
            return sha1("{$request->input('phone')}|{$route->getDomain()}|{$request->ip()}");
        }

        throw ExceptionResponse::instance('Unable to generate the request signature. Route unavailable.');
    }

    private function secondsToTime($seconds): \DateInterval|bool
    {
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");

        return $dtF->diff($dtT);
    }
}
