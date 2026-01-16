<?php

namespace Modules\Auth\Enums;

enum UserRoleEnum: string
{
    case ADMIN = 'admin';
    case VENDOR = 'vendor';
    case CUSTOMER = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::VENDOR => 'Vendor',
            self::CUSTOMER => 'Customer',
        };
    }
}
