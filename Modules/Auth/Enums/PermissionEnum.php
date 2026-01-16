<?php

namespace Modules\Auth\Enums;

enum PermissionEnum: string
{
    // User Management
    case MANAGE_USERS = 'manage_users';
    
    // Vendor Management
    case MANAGE_VENDORS = 'manage_vendors';
    
    // Order Management
    case MANAGE_ORDERS = 'manage_orders';
    
    // Finance
    case MANAGE_FINANCE = 'manage_finance';
    
    // Content
    case MANAGE_CONTENT = 'manage_content';

    public function label(): string
    {
        return str($this->value)->replace('_', ' ')->title();
    }
}
