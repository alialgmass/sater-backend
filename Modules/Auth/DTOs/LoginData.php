<?php

namespace Modules\Auth\DTOs;

readonly class LoginData
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember = false,
        public string $deviceName = 'auth_token',
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            email: $request->validated('email'),
            password: $request->validated('password'),
            remember: $request->boolean('remember'),
            deviceName: $request->input('device_name', 'auth_token'),
        );
    }
}
