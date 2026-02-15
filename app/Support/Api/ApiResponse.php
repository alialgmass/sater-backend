<?php

namespace App\Support\Api;

/**
 * @version 1.0
 */
trait ApiResponse
{
    protected int $code = 200;

    protected int $customCode = 2000;

    protected array $body = [];

    protected array $routes = [];

    protected ?string $message = null;

    protected string $info = 'from response action';

    protected function apiResponse(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'custom_code' => $this->customCode,
            'status' => $this->code === 200,
            'message' => $this->message ?? __('app.messages.data_retrieved_successfully'),
            'body' => (object) $this->body,
            'info' => $this->info,
        ], $this->code);
    }

    protected function apiBody(array|object $body = []): static
    {
        foreach ($body as $key => $value) {
            $this->body[$key] = $value;
        }

        return $this;
    }

    protected function apiMessage(string $message = ''): static
    {
        $this->message = $message;

        return $this;
    }

    protected function apiInfo(string $info = '', $addToCurrent = false): static
    {
        $addToCurrent ? $this->info .= $info : $this->info = $info;

        return $this;
    }

    protected function apiCode(int $code): static
    {
        $this->code = $code;

        return $this;
    }

    protected function apiCustomCode(int $customCode): static
    {
        $this->customCode = $customCode;

        return $this;
    }

    protected function unauthorized(string $message = 'Unauthorized'): \Illuminate\Http\JsonResponse
    {
        return $this->apiCode(401)->apiMessage($message)->apiResponse();
    }

    protected function notFound(string $message = 'Resource not found'): \Illuminate\Http\JsonResponse
    {
        return $this->apiCode(404)->apiMessage($message)->apiResponse();
    }

    /**
     * @deprecated
     */
    protected function strings(): array
    {
        return [

        ];
    }
}
