<?php

namespace App\Exceptions;

use App\Exceptions\ApiException\ApiException;
use App\Exceptions\ApiException\ExceptionAction;
use App\Exceptions\ApiException\ValidationExceptionResponse;
use BadMethodCallException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler
{
    /**
     * @throws ApiException
     * @throws Throwable
     */
    public function render(Throwable $e, $request)
    {
        return $this->handledExceptions($request, $e);
    }

    /**
     * @throws Throwable
     * @throws ApiException
     */
    public function handledExceptions(Throwable $e)
    {

        return match (true) {
            $e instanceof ModelNotFoundException => ExceptionAction::modelNotFound($e),
            $e instanceof AuthorizationException => ExceptionAction::unauthorized(),
            $e instanceof ValidationException => ValidationExceptionResponse::instance($e->errors()),
            $e instanceof FileNotFoundException,
            $e instanceof NotFoundHttpException,
            $e instanceof MethodNotAllowedHttpException,
            $e instanceof BadMethodCallException => ExceptionAction::make($e),
            default => $e,
        };
    }

    public function invalidJson(ValidationException $exception): JsonResponse
    {
        return response()->json([
            'custom_code' => 4000,
            'status' => false,
            'message' => collect($exception->errors())->first()[0],
            'body' => collect($exception->errors()),
            'info' => 'from invalidJson response in Handler',
        ], 400);

    }

    public function unauthenticated(
        AuthenticationException $exception,
        $request
    ): JsonResponse|\Symfony\Component\HttpFoundation\Response|\Illuminate\Http\RedirectResponse {
        return $request->expectsJson()
            ? response()->json([
                'custom_code' => 4001,
                'status' => false,
                'message' => __('app.messages.please-log-in-first'),
                'body' => [],
                'info' => 'from unauthenticated response in Handler',
            ], 401)
            :
            redirect()->guest(route('filament.admin.auth.login'));
    }
}
