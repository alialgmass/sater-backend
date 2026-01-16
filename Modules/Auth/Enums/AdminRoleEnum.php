<?php

namespace Modules\Auth\Enums;

enum AdminRoleEnum: string
{
    case SUPER_ADMIN = 'super_admin';
    case SUPPORT = 'support';
    case FINANCE = 'finance';
    case CONTENT_MANAGER = 'content_manager';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::SUPPORT => 'Support',
            self::FINANCE => 'Finance',
            self::CONTENT_MANAGER => 'Content Manager',
        };
    }
}
