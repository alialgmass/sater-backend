<?php

namespace App\Enums;

enum TokenAbilityEnum: string
{
    case ChangePassword = 'change_password';

    public function middleware(): string
    {
        return 'ability:'.$this->value;
    }

    public function scope(): array
    {
        return [$this->value];
    }

    public function expiration(): \Illuminate\Support\Carbon
    {
        return match ($this) {
            default => now()->addSeconds(520),
        };
    }

    public function exceptionMessage(): string
    {
        return match ($this) {
            default => now()->addSeconds(520),
        };
    }
}
