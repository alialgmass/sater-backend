<?php

namespace App\Enums\Shipping;

enum ShipmentStatus: string
{
    case PENDING = 'pending';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case FAILED = 'failed';
}